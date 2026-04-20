<?php

namespace App\Http\Controllers;

use App\Imports\AssetsImport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetDocument;
use App\Models\AssetImage;
use App\Models\AssetLog;
use App\Models\AssetMaintenance;
use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use App\Models\Venue;
use DB;
use Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Log;

class AssetManagementController extends Controller{

    public function index(Request $request){
        $assetsQuery = Asset::with(['category', 'venue', 'currentAssignment.assignable']);
        
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        $users = User::orderBy('lastname')->get();
        
        if ($request->filled('status')) {
            $assetsQuery->where('status', $request->status);
        }
        
        if ($request->filled('category_id')) {
            $assetsQuery->where('category_id', $request->category_id);
        }
        
        if ($request->filled('venue_id')) {
            $assetsQuery->where('venue_id', $request->venue_id);
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $assetsQuery->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('asset_code', 'like', $search)
                    ->orWhere('model', 'like', $search)
                    ->orWhere('make', 'like', $search)
                    ->orWhere('notes', 'like', $search);
            });
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSortFields = ['name', 'asset_code', 'status', 'purchase_date', 'current_value'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $assetsQuery->orderBy($sortField, $sortDirection);
        } else {
            $assetsQuery->orderBy('name', 'asc');
        }
        $assets = $assetsQuery->get();
        return view('assets.index', compact('assets', 'categories', 'venues', 'users'));
    }

    public function createAsset(){
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = $this->assetContacts();
        $venues = Venue::orderBy('name')->get();
        return view('assets.create-asset', compact('categories', 'vendors', 'venues'));

    }

    public function store(Request $request){
        $request->merge([
            'contact_id' => $this->normalizeSelectedContactId($request),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_code' => 'required|string|max:255|unique:assets',
            'category_id' => 'required|exists:asset_categories,id',
            'contact_id' => $this->assetContactValidationRule(),
            'venue_id' => 'nullable|exists:venues,id',
            'status' => 'required|string|in:Available,Assigned,In Maintenance,Disposed',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'specifications' => 'nullable|string',
            'notes' => 'nullable|string',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'expected_lifespan' => 'nullable|integer|min:1',
            'current_value' => 'nullable|numeric|min:0',
            'condition' => 'required|string|in:New,Good,Fair,Poor',
            'invoice_number' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'document' => 'nullable|file|max:5120',
            'document_type' => 'required_with:document|nullable|string|max:255',
            'document_title' => 'required_with:document|nullable|string|max:255',
        ]);
        
        $asset = Asset::create($validated);
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'asset_' . $asset->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('assets/images', $filename, 'public');
            
            $asset->update(['image_path' => $path]);
            
            AssetImage::create([
                'asset_id' => $asset->id,
                'image_path' => $path,
                'title' => $asset->name,
                'is_primary' => true
            ]);
        }
        
        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $filename = 'doc_' . $asset->id . '_' . time() . '.' . $document->getClientOriginalExtension();
            $path = $document->storeAs('assets/documents', $filename, 'public');
            
            AssetDocument::create([
                'asset_id' => $asset->id,
                'document_path' => $path,
                'document_type' => $request->document_type,
                'title' => $request->document_title ?: $asset->name . ' ' . $request->document_type,
                'description' => $request->document_description ?? null,
            ]);
        }
        
        AssetLog::createLog(
            $asset->id,
            'create',
            'Asset was created',
            null,
            auth()->id()
        );
        return redirect()->route('assets.index')->with('message', 'Asset created successfully');
    }

    public function show(Asset $asset){
        $asset->load([
            'category', 
            'vendor.primaryPerson',
            'vendor.tags',
            'venue', 
            'images', 
            'documents',
            'assignments',
            'currentAssignment.assignable',
            'maintenances' => function ($query) {
                $query->orderBy('maintenance_date', 'desc');
            }
        ]);

        $showDisposalButton = !$asset->isDisposed() && !$asset->isAssigned();
        
        $logs = AssetLog::where('asset_id', $asset->id)->with('performedByUser')->orderBy('created_at', 'desc')->get();
        $users = User::where('active', true)->orderBy('lastname')->get();
        return view('assets.show-asset', compact('asset', 'logs', 'users', 'showDisposalButton'));
    }

    public function assignAsset(Request $request){
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:assigned_date',
            'condition_on_assignment' => 'required|string|in:New,Good,Fair,Poor',
            'assignment_notes' => 'nullable|string',
        ]);

        $assignmentBlocked = false;
        $user = User::findOrFail($validated['user_id']);

        DB::transaction(function () use ($validated, $user, &$assignmentBlocked) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);

            if (!$asset->isAvailable()) {
                $assignmentBlocked = true;
                return;
            }

            $hasOpenAssignment = AssetAssignment::query()
                ->where('asset_id', $asset->id)
                ->whereNull('actual_return_date')
                ->lockForUpdate()
                ->exists();

            if ($hasOpenAssignment) {
                $assignmentBlocked = true;
                return;
            }

            $assignment = new AssetAssignment();
            $assignment->asset_id = $validated['asset_id'];
            $assignment->assignable_type = 'App\\Models\\User';
            $assignment->assignable_id = $user->id;
            $assignment->assigned_date = $validated['assigned_date'];
            $assignment->expected_return_date = $validated['expected_return_date'];
            $assignment->condition_on_assignment = $validated['condition_on_assignment'];
            $assignment->assignment_notes = $validated['assignment_notes'];
            $assignment->status = 'Assigned';
            $assignment->assigned_by = auth()->id();
            $assignment->save();

            $asset->status = 'Assigned';
            $asset->save();

            AssetLog::createLog(
                $asset->id,
                'assign',
                "Asset assigned to user: {$user->full_name}",
                [
                    'assigned_to' => [
                        'id' => $user->id,
                        'name' => $user->full_name
                    ],
                    'assigned_date' => $validated['assigned_date'],
                    'expected_return_date' => $validated['expected_return_date'],
                    'condition' => $validated['condition_on_assignment']
                ],
                auth()->id()
            );
        }, 3);

        if ($assignmentBlocked) {
            return redirect()->back()->with('error', 'This asset is not available for assignment.');
        }
        
        return redirect()->back()->with('message', 'Asset has been successfully assigned to ' . $user->full_name . '.');
    }

    public function returnForm($id){
        $asset = Asset::with('currentAssignment.assignable')->findOrFail($id);
        if (!$asset->isAssigned() || !$asset->currentAssignment) {
            return redirect()->back()->with('error', 'This asset is not currently assigned.');
        }
        return view('assets.assignments.return-assignment', compact('asset'));
    }

    public function processReturn(Request $request){
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'assignment_id' => 'required|exists:asset_assignments,id',
            'actual_return_date' => 'required|date',
            'condition_on_return' => 'required|string|in:New,Good,Fair,Poor',
            'return_notes' => 'nullable|string',
        ]);

        $returnBlocked = false;
        $assigneeName = '';

        DB::transaction(function () use ($validated, &$returnBlocked, &$assigneeName) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);
            $assignment = AssetAssignment::query()->lockForUpdate()->findOrFail($validated['assignment_id']);

            if ($assignment->asset_id != $asset->id || $assignment->status !== 'Assigned' || $assignment->actual_return_date) {
                $returnBlocked = true;
                return;
            }

            $assignment->actual_return_date = $validated['actual_return_date'];
            $assignment->condition_on_return = $validated['condition_on_return'];
            $assignment->return_notes = $validated['return_notes'];
            $assignment->status = 'Returned';
            $assignment->received_by = auth()->id();
            $assignment->save();

            $asset->status = 'Available';
            $asset->condition = $validated['condition_on_return'];
            $asset->save();

            if ($assignment->assignable_type === 'App\\Models\\User') {
                $assigneeName = User::find($assignment->assignable_id)->name ?? 'User';
            } else {
                $assigneeName = Department::find($assignment->assignable_id)->name ?? 'Department';
            }

            AssetLog::createLog(
                $asset->id,
                'return',
                "Asset returned from " . (str_contains($assignment->assignable_type, 'User') ? 'user' : 'department') . ": $assigneeName",
                [
                    'return_date' => $validated['actual_return_date'],
                    'condition' => $validated['condition_on_return']
                ],
                auth()->id()
            );
        }, 3);

        if ($returnBlocked) {
            return redirect()->back()->with('error', 'Invalid assignment for this asset.');
        }
        return redirect()->route('assets.show', $validated['asset_id'])->with('message', 'Asset has been successfully returned.');
    }

    public function edit(Asset $asset){
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = $this->assetContacts();
        $venues = Venue::orderBy('name')->get();

        if ($asset->isDisposed()) {
            return redirect()->back()->with('error', 'Disposed assets cannot be edited.');
        }

        return view('assets.update-asset', compact('asset', 'categories', 'vendors', 'venues'));
    }

    public function update(Request $request, Asset $asset){
        $request->merge([
            'contact_id' => $this->normalizeSelectedContactId($request),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('assets')->ignore($asset->id),
            ],
            'category_id' => 'required|exists:asset_categories,id',
            'contact_id' => $this->assetContactValidationRule(),
            'venue_id' => 'nullable|exists:venues,id',
            'status' => 'required|string|in:Available,Assigned,In Maintenance,Disposed',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'specifications' => 'nullable|string',
            'notes' => 'nullable|string',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'expected_lifespan' => 'nullable|integer|min:1',
            'current_value' => 'nullable|numeric|min:0',
            'condition' => 'required|string|in:New,Good,Fair,Poor',
            'invoice_number' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'document' => 'nullable|file|max:5120',
            'document_type' => 'required_with:document|nullable|string|max:255',
            'document_title' => 'required_with:document|nullable|string|max:255',
        ]);

        if ($asset->isDisposed()) {
            return redirect()->back()->with('error', 'Disposed assets cannot be updated.');
        }
        
        $changes = [];
        foreach ($validated as $key => $value) {
            if (in_array($key, ['image', 'document', 'document_type', 'document_title'])) {
                continue;
            }
            
            if ($asset->{$key} != $value) {
                $changes[$key] = [
                    'old' => $asset->{$key},
                    'new' => $value
                ];
            }
        }
        
        $asset->update($validated);
        if ($request->hasFile('image')) {
            if ($asset->image_path && Storage::disk('public')->exists($asset->image_path)) {
                Storage::disk('public')->delete($asset->image_path);
            }
            
            $image = $request->file('image');
            $filename = 'asset_' . $asset->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('assets/images', $filename, 'public');
            
            $asset->update(['image_path' => $path]);
            $assetImage = AssetImage::where('asset_id', $asset->id)
                ->where('is_primary', true)
                ->first();
                
            if ($assetImage) {
                $assetImage->update([
                    'image_path' => $path,
                    'title' => $asset->name
                ]);
            } else {
                AssetImage::create([
                    'asset_id' => $asset->id,
                    'image_path' => $path,
                    'title' => $asset->name,
                    'is_primary' => true
                ]);
            }
            
            $changes['image'] = [
                'old' => 'Previous image',
                'new' => 'Updated image'
            ];
        }
        
        if ($request->hasFile('document')) {
            $existingDocument = AssetDocument::where('asset_id', $asset->id)->first();
            if ($existingDocument) {
                if (Storage::disk('public')->exists($existingDocument->document_path)) {
                    Storage::disk('public')->delete($existingDocument->document_path);
                }
                
                $existingDocument->forceDelete();
                
                $changes['document_removal'] = [
                    'old' => 'Previous document: ' . $existingDocument->title,
                    'new' => 'Removed for replacement'
                ];
            }
            
            $document = $request->file('document');
            $filename = 'doc_' . $asset->id . '_' . time() . '.' . $document->getClientOriginalExtension();
            $path = $document->storeAs('assets/documents', $filename, 'public');
            
            AssetDocument::create([
                'asset_id' => $asset->id,
                'document_path' => $path,
                'document_type' => $request->document_type,
                'title' => $request->document_title,
                'description' => $request->document_description ?? null,
            ]);
            
            $changes['document'] = [
                'old' => $existingDocument ? 'Previous document' : 'No document',
                'new' => 'New document: ' . $request->document_title
            ];
        }
        
        if (!empty($changes)) {
            AssetLog::createLog(
                $asset->id,
                'update',
                'Asset information was updated',
                $changes,
                auth()->id()
            );
        }
        
        return redirect()->back()->with('message', 'Asset updated successfully');
    }

    public function destroy(Asset $asset){
        if ($asset->isAssigned()) {
            return redirect()->route('assets.show', $asset)->with('error', 'This asset is currently assigned to staff. Please return it first.');
        }
        
        $activeMaintenance = $asset->maintenances()->whereIn('status', ['Scheduled', 'In Progress'])->count();
        if ($activeMaintenance > 0) {
            return redirect()->route('assets.show', $asset)->with('error', 'This asset has active maintenance records. Please complete or cancel them first.');
        }
        
        $assetName = $asset->name;
        $assetCode = $asset->asset_code;
        
        try {
            DB::transaction(function() use ($asset) {
                
                if ($asset->image_path && Storage::disk('public')->exists($asset->image_path)) {
                    Storage::disk('public')->delete($asset->image_path);
                }
                
                foreach ($asset->documents as $document) {
                    if (Storage::disk('public')->exists($document->document_path)) {
                        Storage::disk('public')->delete($document->document_path);
                    }
                }
                
                $asset->auditItems()->forceDelete();
                $asset->assignments()->forceDelete();
                $asset->maintenances()->forceDelete();

                if ($asset->disposal) {
                    $asset->disposal->forceDelete();
                }

                $asset->images()->forceDelete();
                $asset->documents()->forceDelete();
                $asset->logs()->forceDelete();
                
                $asset->forceDelete();
            });
            
            return redirect()->back()->with('message', "Asset '$assetName' ($assetCode) has been permanently deleted along with all its records.");
            
        } catch (\Exception $e) {
            Log::error('Asset deletion failed', [
                'asset_id' => $asset->id,
                'asset_name' => $assetName,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete asset. Please contact system administrator.');
        }
    }

    public function deleteImage(Asset $asset){
        if (!$asset->image_path) {
            return redirect()->back()->with('error', 'Asset does not have an image to delete.');
        }
        
        $imagePath = $asset->image_path;
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
        
        $assetImage = AssetImage::where('asset_id', $asset->id)->where('image_path', $imagePath)->first();
        if ($assetImage) {
            $assetImage->forceDelete();
        }
        
        $asset->update(['image_path' => null]);
        AssetLog::createLog(
            $asset->id,
            'update',
            'Asset image was deleted',
            [
                'image' => [
                    'old' => $imagePath,
                    'new' => null
                ]
            ],
            auth()->id()
        );

        return redirect()->back()->with('message', 'Asset image has been deleted successfully.');
    }

    public function deleteDocument(AssetDocument $document){
        $assetId = $document->asset_id;
        if (!$document) {
            return redirect()->back()->with('error', 'Document does not exist.');
        }
        
        $documentInfo = [
            'title' => $document->title,
            'type' => $document->document_type,
            'path' => $document->document_path
        ];
        
        if (Storage::disk('public')->exists($document->document_path)) {
            Storage::disk('public')->delete($document->document_path);
        }

        $document->forceDelete();
        AssetLog::createLog(
            $assetId,
            'update',
            'Asset document was deleted: ' . $documentInfo['title'],
            [
                'document' => [
                    'old' => $documentInfo,
                    'new' => null
                ]
            ],
            auth()->id()
        );
        
        return redirect()->back()->with('message', 'Document has been deleted successfully.');
    }

    public function assetSettings(){
        $categories = AssetCategory::withCount('assets')->orderBy('name')->get();
        return view('assets.assets-settings', compact('categories'));
    }

    public function storeCategory(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:asset_categories,code',
        ]);
        
        AssetCategory::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);
        return redirect()->back()->with('message', 'Category added successfully');
    }

    public function updateCategory(Request $request, AssetCategory $assetCategory){
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('asset_categories', 'code')->ignore($assetCategory->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        
        $assetCategory->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        
        return response()->json(['success' => true]);
    }

    public function destroyCategory(AssetCategory $assetCategory){
        if ($assetCategory->assets()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with associated assets');
        }
        
        $assetCategory->forceDelete();
        return redirect()->back()->with('message', 'Category deleted successfully');
    }

    //Assets Analysis reports
    public function assetCategoryReport(Request $request){
        $categories = AssetCategory::withCount([
            'assets',
            'assets as available_assets_count' => function ($query) {
                $query->where('status', 'Available');
            },
            'assets as assigned_assets_count' => function ($query) {
                $query->where('status', 'Assigned');
            },
            'assets as maintenance_assets_count' => function ($query) {
                $query->where('status', 'In Maintenance');
            },
            'assets as disposed_assets_count' => function ($query) {
                $query->where('status', 'Disposed');
            }
        ])
        ->with(['assets' => function ($query) {
            $query->select('id', 'category_id', 'purchase_price', 'current_value', 'purchase_date', 'condition', 'status');
        }])->where('is_active', true)->orderBy('name')->get();
        $categoryStats = $categories->map(function ($category) {
            $assets = $category->assets;
            
            return [
                'category' => $category,
                'total_assets' => $category->assets_count,
                'available_count' => $category->available_assets_count,
                'assigned_count' => $category->assigned_assets_count,
                'maintenance_count' => $category->maintenance_assets_count,
                'disposed_count' => $category->disposed_assets_count,
                'total_purchase_value' => $assets->sum('purchase_price') ?: 0,
                'total_current_value' => $assets->sum('current_value') ?: 0,
                'average_asset_age' => $assets->where('purchase_date')->avg(function ($asset) {
                    return $asset->purchase_date ? now()->diffInMonths($asset->purchase_date) : 0;
                }) ?: 0,
                'condition_breakdown' => [
                    'new' => $assets->where('condition', 'New')->count(),
                    'good' => $assets->where('condition', 'Good')->count(),
                    'fair' => $assets->where('condition', 'Fair')->count(),
                    'poor' => $assets->where('condition', 'Poor')->count(),
                ],
                'utilization_rate' => $category->assets_count > 0 
                    ? round(($category->assigned_assets_count / $category->assets_count) * 100, 1) 
                    : 0,
            ];
        });

        $overallStats = [
            'total_categories' => $categories->count(),
            'total_assets' => $categories->sum('assets_count'),
            'total_available' => $categories->sum('available_assets_count'),
            'total_assigned' => $categories->sum('assigned_assets_count'),
            'total_maintenance' => $categories->sum('maintenance_assets_count'),
            'total_disposed' => $categories->sum('disposed_assets_count'),
            'total_purchase_value' => $categoryStats->sum('total_purchase_value'),
            'total_current_value' => $categoryStats->sum('total_current_value'),
            'overall_utilization_rate' => $categories->sum('assets_count') > 0 
                ? round(($categories->sum('assigned_assets_count') / $categories->sum('assets_count')) * 100, 1) 
                : 0,
        ];
        return view('assets.reports.category-report', compact('categoryStats', 'overallStats'));
    }

    public function assetValueReport(Request $request){
        $assets = Asset::with(['category', 'venue', 'vendor'])->whereNotNull('purchase_price')->where('purchase_price', '>', 0)->get();
        $overallStats = [
            'total_assets_with_value' => $assets->count(),
            'total_purchase_value' => $assets->sum('purchase_price'),
            'total_current_value' => $assets->sum('current_value') ?: $assets->sum('purchase_price'),
            'total_depreciation' => $assets->sum('purchase_price') - ($assets->sum('current_value') ?: $assets->sum('purchase_price')),
            'average_asset_value' => $assets->count() > 0 ? $assets->avg('purchase_price') : 0,
            'average_current_value' => $assets->count() > 0 ? ($assets->avg('current_value') ?: $assets->avg('purchase_price')) : 0,
        ];

        $overallStats['depreciation_percentage'] = $overallStats['total_purchase_value'] > 0 
            ? round(($overallStats['total_depreciation'] / $overallStats['total_purchase_value']) * 100, 1) 
            : 0;

        $categoryValues = AssetCategory::withCount('assets')->with(['assets' => function($query) {
                $query->whereNotNull('purchase_price')->where('purchase_price', '>', 0);
            }])->where('is_active', true)->get()->map(function($category) use ($overallStats) {
                $categoryAssets = $category->assets;
                $purchaseValue = $categoryAssets->sum('purchase_price');
                $currentValue = $categoryAssets->sum('current_value') ?: $purchaseValue;
                
                return [
                    'category' => $category,
                    'asset_count' => $categoryAssets->count(),
                    'total_purchase_value' => $purchaseValue,
                    'total_current_value' => $currentValue,
                    'depreciation' => $purchaseValue - $currentValue,
                    'depreciation_percentage' => $purchaseValue > 0 ? round((($purchaseValue - $currentValue) / $purchaseValue) * 100, 1) : 0,
                    'average_value' => $categoryAssets->count() > 0 ? $purchaseValue / $categoryAssets->count() : 0,
                    'percentage_of_total' => $overallStats['total_purchase_value'] > 0 ? round(($purchaseValue / $overallStats['total_purchase_value']) * 100, 1) : 0,
                ];
            })->sortByDesc('total_purchase_value');

        $venueValues = Venue::withCount(['assets' => function($query) {
                $query->whereNotNull('purchase_price')->where('purchase_price', '>', 0);
            }])->with(['assets' => function($query) {
                $query->whereNotNull('purchase_price')->where('purchase_price', '>', 0);
            }])->get()->map(function($venue) use ($overallStats) {
                $venueAssets = $venue->assets;
                $purchaseValue = $venueAssets->sum('purchase_price');
                $currentValue = $venueAssets->sum('current_value') ?: $purchaseValue;
                
                return [
                    'venue' => $venue,
                    'asset_count' => $venueAssets->count(),
                    'total_purchase_value' => $purchaseValue,
                    'total_current_value' => $currentValue,
                    'depreciation' => $purchaseValue - $currentValue,
                    'percentage_of_total' => $overallStats['total_purchase_value'] > 0 ? round(($purchaseValue / $overallStats['total_purchase_value']) * 100, 1) : 0,
                ];
            })->filter(function($venue) {
                return $venue['asset_count'] > 0;
            })->sortByDesc('total_purchase_value');

        $highValueAssets = $assets->sortByDesc('purchase_price')->take(10);
        $conditionValues = [
            'new' => $assets->where('condition', 'New'),
            'good' => $assets->where('condition', 'Good'),
            'fair' => $assets->where('condition', 'Fair'),
            'poor' => $assets->where('condition', 'Poor'),
        ];

        $conditionStats = collect($conditionValues)->map(function($conditionAssets, $condition) use ($overallStats) {
            $purchaseValue = $conditionAssets->sum('purchase_price');
            $currentValue = $conditionAssets->sum('current_value') ?: $purchaseValue;
            
            return [
                'condition' => ucfirst($condition),
                'count' => $conditionAssets->count(),
                'total_purchase_value' => $purchaseValue,
                'total_current_value' => $currentValue,
                'average_value' => $conditionAssets->count() > 0 ? $purchaseValue / $conditionAssets->count() : 0,
                'percentage_of_total' => $overallStats['total_purchase_value'] > 0 ? round(($purchaseValue / $overallStats['total_purchase_value']) * 100, 1) : 0,
            ];
        });

        $statusValues = [
            'available' => $assets->where('status', 'Available'),
            'assigned' => $assets->where('status', 'Assigned'),
            'maintenance' => $assets->where('status', 'In Maintenance'),
            'disposed' => $assets->where('status', 'Disposed'),
        ];

        $statusStats = collect($statusValues)->map(function($statusAssets, $status) use ($overallStats) {
            $purchaseValue = $statusAssets->sum('purchase_price');
            $currentValue = $statusAssets->sum('current_value') ?: $purchaseValue;
            
            return [
                'status' => ucfirst($status),
                'count' => $statusAssets->count(),
                'total_purchase_value' => $purchaseValue,
                'total_current_value' => $currentValue,
                'percentage_of_total' => $overallStats['total_purchase_value'] > 0 ? round(($purchaseValue / $overallStats['total_purchase_value']) * 100, 1) : 0,
            ];
        });

        $ageGroups = [
            'new' => $assets->filter(function($asset) {
                return !$asset->purchase_date || $asset->purchase_date->diffInMonths(now()) <= 12;
            }),
            'recent' => $assets->filter(function($asset) {
                return $asset->purchase_date && $asset->purchase_date->diffInMonths(now()) > 12 && $asset->purchase_date->diffInMonths(now()) <= 36;
            }),
            'mature' => $assets->filter(function($asset) {
                return $asset->purchase_date && $asset->purchase_date->diffInMonths(now()) > 36 && $asset->purchase_date->diffInMonths(now()) <= 60;
            }),
            'old' => $assets->filter(function($asset) {
                return $asset->purchase_date && $asset->purchase_date->diffInMonths(now()) > 60;
            }),
        ];

        $ageStats = collect($ageGroups)->map(function($ageAssets, $ageGroup) use ($overallStats) {
            $purchaseValue = $ageAssets->sum('purchase_price');
            $currentValue = $ageAssets->sum('current_value') ?: $purchaseValue;
            $depreciation = $purchaseValue - $currentValue;
            
            return [
                'age_group' => $ageGroup,
                'count' => $ageAssets->count(),
                'total_purchase_value' => $purchaseValue,
                'total_current_value' => $currentValue,
                'depreciation' => $depreciation,
                'depreciation_percentage' => $purchaseValue > 0 ? round(($depreciation / $purchaseValue) * 100, 1) : 0,
                'percentage_of_total' => $overallStats['total_purchase_value'] > 0 ? round(($purchaseValue / $overallStats['total_purchase_value']) * 100, 1) : 0,
            ];
        });


        return view('assets.reports.asset-value-report', compact(
            'overallStats',
            'categoryValues',
            'venueValues',
            'highValueAssets',
            'conditionStats',
            'statusStats',
            'ageStats'
        ));
    }

    public function assetLocationReport(Request $request){
        $locations = Venue::withCount([
            'assets',
            'assets as available_assets_count' => function ($query) {
                $query->where('status', 'Available');
            },
            'assets as assigned_assets_count' => function ($query) {
                $query->where('status', 'Assigned');
            },
            'assets as maintenance_assets_count' => function ($query) {
                $query->where('status', 'In Maintenance');
            },
            'assets as disposed_assets_count' => function ($query) {
                $query->where('status', 'Disposed');
            }
        ])
        ->with(['assets' => function ($query) {
            $query->with(['category', 'currentAssignment.assignable'])
                ->select('id', 'venue_id', 'category_id', 'status', 'condition', 'purchase_price', 'current_value', 'purchase_date', 'name', 'asset_code');
        }])->orderBy('name')->get();

        $unassignedAssets = Asset::whereNull('venue_id')
            ->with(['category', 'currentAssignment.assignable'])
            ->get();

        $locationStats = $locations->map(function ($location) {
            $assets = $location->assets;
            
            $totalPurchaseValue = $assets->sum('purchase_price') ?: 0;
            $totalCurrentValue = $assets->sum('current_value') ?: $totalPurchaseValue;
            
            $categoryDistribution = $assets->groupBy('category.name')->map(function ($categoryAssets, $categoryName) {
                return [
                    'name' => $categoryName ?: 'Uncategorized',
                    'count' => $categoryAssets->count(),
                    'percentage' => 0
                ];
            });

            $totalAssets = $assets->count();
            $categoryDistribution = $categoryDistribution->map(function ($category) use ($totalAssets) {
                $category['percentage'] = $totalAssets > 0 ? round(($category['count'] / $totalAssets) * 100, 1) : 0;
                return $category;
            });

            $conditionBreakdown = [
                'new' => $assets->where('condition', 'New')->count(),
                'good' => $assets->where('condition', 'Good')->count(),
                'fair' => $assets->where('condition', 'Fair')->count(),
                'poor' => $assets->where('condition', 'Poor')->count(),
            ];

            $averageAge = $assets->where('purchase_date')->avg(function ($asset) {
                return $asset->purchase_date ? now()->diffInMonths($asset->purchase_date) : 0;
            }) ?: 0;

            $assignedAssets = $assets->where('status', 'Assigned');
            $assignedToUsers = $assignedAssets->filter(function ($asset) {
                return $asset->currentAssignment && $asset->currentAssignment->assignable_type === 'App\\Models\\User';
            });

            $utilizationRate = $totalAssets > 0 ? round(($location->assigned_assets_count / $totalAssets) * 100, 1) : 0;
            $assetDensity = $totalAssets;

            return [
                'location' => $location,
                'total_assets' => $totalAssets,
                'available_count' => $location->available_assets_count,
                'assigned_count' => $location->assigned_assets_count,
                'maintenance_count' => $location->maintenance_assets_count,
                'disposed_count' => $location->disposed_assets_count,
                'total_purchase_value' => $totalPurchaseValue,
                'total_current_value' => $totalCurrentValue,
                'average_asset_value' => $totalAssets > 0 ? $totalPurchaseValue / $totalAssets : 0,
                'depreciation' => $totalPurchaseValue - $totalCurrentValue,
                'depreciation_percentage' => $totalPurchaseValue > 0 ? round((($totalPurchaseValue - $totalCurrentValue) / $totalPurchaseValue) * 100, 1) : 0,
                'utilization_rate' => $utilizationRate,
                'asset_density' => $assetDensity,
                'average_age_months' => round($averageAge),
                'category_distribution' => $categoryDistribution,
                'condition_breakdown' => $conditionBreakdown,
                'assigned_to_users_count' => $assignedToUsers->count(),
                'most_common_category' => $categoryDistribution->sortByDesc('count')->first()['name'] ?? 'None',
                'condition_score' => $this->calculateConditionScore($conditionBreakdown, $totalAssets),
            ];
        })->sortByDesc('total_assets');

        $unassignedStats = null;
        if ($unassignedAssets->count() > 0) {
            $totalPurchaseValue = $unassignedAssets->sum('purchase_price') ?: 0;
            $totalCurrentValue = $unassignedAssets->sum('current_value') ?: $totalPurchaseValue;
            
            $unassignedStats = [
                'total_assets' => $unassignedAssets->count(),
                'available_count' => $unassignedAssets->where('status', 'Available')->count(),
                'assigned_count' => $unassignedAssets->where('status', 'Assigned')->count(),
                'maintenance_count' => $unassignedAssets->where('status', 'In Maintenance')->count(),
                'disposed_count' => $unassignedAssets->where('status', 'Disposed')->count(),
                'total_purchase_value' => $totalPurchaseValue,
                'total_current_value' => $totalCurrentValue,
                'condition_breakdown' => [
                    'new' => $unassignedAssets->where('condition', 'New')->count(),
                    'good' => $unassignedAssets->where('condition', 'Good')->count(),
                    'fair' => $unassignedAssets->where('condition', 'Fair')->count(),
                    'poor' => $unassignedAssets->where('condition', 'Poor')->count(),
                ],
            ];
        }

        $overallStats = [
            'total_locations' => $locations->count(),
            'locations_with_assets' => $locationStats->where('total_assets', '>', 0)->count(),
            'total_assets' => $locationStats->sum('total_assets') + ($unassignedStats['total_assets'] ?? 0),
            'total_available' => $locationStats->sum('available_count') + ($unassignedStats['available_count'] ?? 0),
            'total_assigned' => $locationStats->sum('assigned_count') + ($unassignedStats['assigned_count'] ?? 0),
            'total_maintenance' => $locationStats->sum('maintenance_count') + ($unassignedStats['maintenance_count'] ?? 0),
            'total_disposed' => $locationStats->sum('disposed_count') + ($unassignedStats['disposed_count'] ?? 0),
            'total_purchase_value' => $locationStats->sum('total_purchase_value') + ($unassignedStats['total_purchase_value'] ?? 0),
            'total_current_value' => $locationStats->sum('total_current_value') + ($unassignedStats['total_current_value'] ?? 0),
            'average_assets_per_location' => $locationStats->where('total_assets', '>', 0)->count() > 0 
                ? round($locationStats->sum('total_assets') / $locationStats->where('total_assets', '>', 0)->count(), 1) 
                : 0,
            'highest_asset_count' => $locationStats->max('total_assets') ?? 0,
            'lowest_asset_count' => $locationStats->where('total_assets', '>', 0)->min('total_assets') ?? 0,
            'locations_without_assets' => $locations->count() - $locationStats->where('total_assets', '>', 0)->count(),
            'overall_utilization' => $locationStats->sum('total_assets') > 0 
                ? round(($locationStats->sum('assigned_count') / $locationStats->sum('total_assets')) * 100, 1) 
                : 0,
        ];

        $topLocations = [
            'most_assets' => $locationStats->sortByDesc('total_assets')->take(5),
            'highest_value' => $locationStats->sortByDesc('total_purchase_value')->take(5),
            'best_utilization' => $locationStats->where('total_assets', '>', 0)->sortByDesc('utilization_rate')->take(5),
            'newest_assets' => $locationStats->where('total_assets', '>', 0)->sortBy('average_age_months')->take(5),
            'best_condition' => $locationStats->where('total_assets', '>', 0)->sortByDesc('condition_score')->take(5),
        ];

        $distributionAnalysis = [
            'concentration_index' => $this->calculateConcentrationIndex($locationStats),
            'diversity_index' => $this->calculateDiversityIndex($locationStats),
            'utilization_variance' => $this->calculateUtilizationVariance($locationStats),
        ];

        $categoryPresence = $this->analyzeCategoryPresence($locationStats);
        return view('assets.reports.location-report', compact(
            'overallStats',
            'locationStats',
            'unassignedStats',
            'topLocations',
            'distributionAnalysis',
            'categoryPresence'
        ));
    }

    private function calculateConditionScore($conditionBreakdown, $totalAssets){
        if ($totalAssets === 0) return 0;
        
        $weights = ['new' => 4, 'good' => 3, 'fair' => 2, 'poor' => 1];
        $score = 0;
        
        foreach ($conditionBreakdown as $condition => $count) {
            $score += $count * $weights[$condition];
        }
        
        return round(($score / ($totalAssets * 4)) * 100, 1);
    }

    private function calculateConcentrationIndex($locationStats){
        $totalAssets = $locationStats->sum('total_assets');
        if ($totalAssets === 0) return 0;
        
        $herfindahlIndex = $locationStats->reduce(function ($carry, $location) use ($totalAssets) {
            $share = $location['total_assets'] / $totalAssets;
            return $carry + ($share * $share);
        }, 0);
        
        return round($herfindahlIndex * 100, 2);
    }

    private function calculateDiversityIndex($locationStats){
        $locationsWithAssets = $locationStats->where('total_assets', '>', 0);
        if ($locationsWithAssets->count() === 0) return 0;
        
        $averageCategoryCount = $locationsWithAssets->avg(function ($location) {
            return $location['category_distribution']->count();
        });
        
        return round($averageCategoryCount, 1);
    }

    private function calculateUtilizationVariance($locationStats){
        $locationsWithAssets = $locationStats->where('total_assets', '>', 0);
        if ($locationsWithAssets->count() === 0) return 0;
        
        $utilizationRates = $locationsWithAssets->pluck('utilization_rate');
        $mean = $utilizationRates->avg();
        
        $variance = $utilizationRates->reduce(function ($carry, $rate) use ($mean) {
            return $carry + pow($rate - $mean, 2);
        }, 0) / $utilizationRates->count();
        
        return round(sqrt($variance), 1);
    }

    private function analyzeCategoryPresence($locationStats){
        $categoryPresence = [];
        
        foreach ($locationStats as $locationStat) {
            foreach ($locationStat['category_distribution'] as $category) {
                $categoryName = $category['name'];
                
                if (!isset($categoryPresence[$categoryName])) {
                    $categoryPresence[$categoryName] = [
                        'name' => $categoryName,
                        'total_locations' => 0,
                        'total_assets' => 0,
                        'locations' => [],
                    ];
                }
                
                $categoryPresence[$categoryName]['total_locations']++;
                $categoryPresence[$categoryName]['total_assets'] += $category['count'];
                $categoryPresence[$categoryName]['locations'][] = [
                    'location' => $locationStat['location']->name,
                    'count' => $category['count']
                ];
            }
        }
        return collect($categoryPresence)->sortByDesc('total_assets');
    }

    public function assetStatusReport(Request $request){
        $allAssets = Asset::with([
            'category', 
            'venue', 
            'vendor',
            'currentAssignment.assignable',
            'currentAssignment.assignedByUser',
            'maintenances' => function($query) {
                $query->latest()->limit(1);
            },
            'disposal.authorizedByUser'
        ])->get();

        $statusGroups = [
            'Available' => $allAssets->where('status', 'Available'),
            'Assigned' => $allAssets->where('status', 'Assigned'),
            'In Maintenance' => $allAssets->where('status', 'In Maintenance'),
            'Disposed' => $allAssets->where('status', 'Disposed'),
        ];

        $availableAssets = $statusGroups['Available']->map(function($asset) {
            return [
                'asset' => $asset,
                'days_available' => $this->calculateDaysInCurrentStatus($asset, 'Available'),
                'last_assignment' => $this->getLastAssignmentInfo($asset),
                'condition_score' => $this->getConditionScore($asset->condition),
                'value_category' => $this->getValueCategory($asset->purchase_price),
            ];
        })->sortByDesc('asset.purchase_price');

        $assignedAssets = $statusGroups['Assigned']->map(function($asset) {
            $assignment = $asset->currentAssignment;
            $isOverdue = $assignment && $assignment->expected_return_date && $assignment->expected_return_date->isPast();
            
            return [
                'asset' => $asset,
                'assignment' => $assignment,
                'days_assigned' => $assignment ? $assignment->assigned_date->diffInDays(now()) : 0,
                'is_overdue' => $isOverdue,
                'overdue_days' => $isOverdue ? now()->diffInDays($assignment->expected_return_date) : 0,
                'assigned_to_type' => $assignment ? class_basename($assignment->assignable_type) : 'Unknown',
                'assigned_to_name' => $assignment && $assignment->assignable ? 
                    ($assignment->assignable->name ?? $assignment->assignable->full_name ?? 'Unknown') : 'Unknown',
                'condition_change_risk' => $this->assessConditionChangeRisk($asset, $assignment),
            ];
        })->sortByDesc('days_assigned');

        $maintenanceAssets = $statusGroups['In Maintenance']->map(function($asset) {
            $latestMaintenance = $asset->maintenances->first();
            
            return [
                'asset' => $asset,
                'maintenance' => $latestMaintenance,
                'days_in_maintenance' => $this->calculateDaysInCurrentStatus($asset, 'In Maintenance'),
                'maintenance_type' => $latestMaintenance->maintenance_type ?? 'Unknown',
                'maintenance_cost' => $latestMaintenance->cost ?? 0,
                'maintenance_vendor' => $latestMaintenance->vendor->name ?? 'Internal',
                'estimated_completion' => $latestMaintenance->next_maintenance_date ?? null,
                'maintenance_priority' => $this->getMaintenancePriority($asset, $latestMaintenance),
            ];
        })->sortByDesc('days_in_maintenance');

        $disposedAssets = $statusGroups['Disposed']->map(function($asset) {
            $disposal = $asset->disposal;
            
            return [
                'asset' => $asset,
                'disposal' => $disposal,
                'disposal_date' => $disposal->disposal_date ?? null,
                'disposal_method' => $disposal->disposal_method ?? 'Unknown',
                'disposal_amount' => $disposal->disposal_amount ?? 0,
                'disposal_reason' => $disposal->reason ?? 'Not specified',
                'age_at_disposal' => $disposal && $asset->purchase_date ? 
                    $asset->purchase_date->diffInMonths($disposal->disposal_date) : 0,
                'value_recovery_percentage' => $this->calculateValueRecovery($asset, $disposal),
            ];
        })->sortByDesc('disposal.disposal_date');

        $statusTransitions = $this->analyzeStatusTransitions($allAssets);
        $ageAnalysis = $this->analyzeAssetAgeByStatus($statusGroups);
        $valueAnalysis = $this->analyzeAssetValueByStatus($statusGroups);
        $categoryDistribution = $this->analyzeCategoryDistributionByStatus($statusGroups);
        $locationDistribution = $this->analyzeLocationDistributionByStatus($statusGroups);
        $performanceMetrics = [
            'average_assignment_duration' => $this->calculateAverageAssignmentDuration($allAssets),
            'average_maintenance_duration' => $this->calculateAverageMaintenanceDuration($allAssets),
            'asset_turnover_rate' => $this->calculateAssetTurnoverRate($allAssets),
            'utilization_efficiency' => $this->calculateUtilizationEfficiency($statusGroups),
            'maintenance_frequency' => $this->calculateMaintenanceFrequency($allAssets),
            'disposal_rate' => $this->calculateDisposalRate($allAssets),
        ];

        $healthIndicators = [
            'overdue_assignments' => $assignedAssets->where('is_overdue', true)->count(),
            'long_term_maintenance' => $maintenanceAssets->where('days_in_maintenance', '>', 30)->count(),
            'idle_available_assets' => $availableAssets->where('days_available', '>', 90)->count(),
            'high_value_disposed' => $disposedAssets->where('asset.purchase_price', '>', 10000)->count(),
            'poor_condition_available' => $availableAssets->where('asset.condition', 'Poor')->count(),
        ];

        return view('assets.reports.status-report', compact(
            'availableAssets',
            'assignedAssets',
            'maintenanceAssets',
            'disposedAssets',
            'statusTransitions',
            'ageAnalysis',
            'valueAnalysis',
            'categoryDistribution',
            'locationDistribution',
            'performanceMetrics',
            'healthIndicators'
        ));
    }

    private function calculateDaysInCurrentStatus($asset, $status){
        $latestLog = AssetLog::where('asset_id', $asset->id)
            ->where('action', 'update')
            ->where('description', 'like', '%status%')
            ->latest()
            ->first();
            
        if ($latestLog) {
            return $latestLog->created_at->diffInDays(now());
        }
        
        return $asset->updated_at->diffInDays(now());
    }

    private function getLastAssignmentInfo($asset){
        $lastAssignment = AssetAssignment::where('asset_id', $asset->id)
            ->where('status', 'Returned')
            ->latest('actual_return_date')
            ->first();
            
        if ($lastAssignment) {
            return [
                'date' => $lastAssignment->actual_return_date,
                'duration' => $lastAssignment->assigned_date->diffInDays($lastAssignment->actual_return_date),
                'assigned_to' => $lastAssignment->assignable->name ?? 'Unknown'
            ];
        }
        
        return null;
    }

    private function getConditionScore($condition){
        $scores = ['New' => 100, 'Good' => 75, 'Fair' => 50, 'Poor' => 25];
        return $scores[$condition] ?? 0;
    }

    private function getValueCategory($purchasePrice){
        if (!$purchasePrice) return 'Unknown';
        if ($purchasePrice >= 50000) return 'Very High';
        if ($purchasePrice >= 20000) return 'High';
        if ($purchasePrice >= 5000) return 'Medium';
        if ($purchasePrice >= 1000) return 'Low';
        return 'Very Low';
    }

    private function assessConditionChangeRisk($asset, $assignment){
        if (!$assignment) return 'Low';
        
        $daysAssigned = $assignment->assigned_date->diffInDays(now());
        $conditionScore = $this->getConditionScore($asset->condition);
        
        if ($daysAssigned > 365 && $conditionScore <= 50) return 'High';
        if ($daysAssigned > 180 && $conditionScore <= 75) return 'Medium';
        return 'Low';
    }

    private function getMaintenancePriority($asset, $maintenance){
        if (!$maintenance) return 'Low';
        
        $daysInMaintenance = $this->calculateDaysInCurrentStatus($asset, 'In Maintenance');
        $assetValue = $asset->purchase_price ?? 0;
        
        if ($daysInMaintenance > 30 && $assetValue > 20000) return 'High';
        if ($daysInMaintenance > 14 || $assetValue > 10000) return 'Medium';
        return 'Low';
    }

    private function calculateValueRecovery($asset, $disposal){
        if (!$disposal || !$asset->purchase_price || !$disposal->disposal_amount) return 0;
        
        return round(($disposal->disposal_amount / $asset->purchase_price) * 100, 1);
    }

    private function analyzeStatusTransitions($assets){
        return [
            'available_to_assigned' => 0,
            'assigned_to_maintenance' => 0,
            'maintenance_to_available' => 0,
            'any_to_disposed' => 0,
        ];
    }

    private function analyzeAssetAgeByStatus($statusGroups){
        $analysis = [];
        foreach ($statusGroups as $status => $assets) {
            $ages = $assets->filter(function($asset) {
                return $asset->purchase_date;
            })->map(function($asset) {
                return $asset->purchase_date->diffInMonths(now());
            });
            
            $analysis[$status] = [
                'average_age' => $ages->avg() ?? 0,
                'oldest_asset' => $ages->max() ?? 0,
                'newest_asset' => $ages->min() ?? 0,
            ];
        }
        return $analysis;
    }

    private function analyzeAssetValueByStatus($statusGroups){
        $analysis = [];
        foreach ($statusGroups as $status => $assets) {
            $values = $assets->pluck('purchase_price')->filter();
            
            $analysis[$status] = [
                'total_value' => $values->sum(),
                'average_value' => $values->avg() ?? 0,
                'highest_value' => $values->max() ?? 0,
                'count' => $assets->count(),
            ];
        }
        return $analysis;
    }

    private function analyzeCategoryDistributionByStatus($statusGroups){
        $distribution = [];
        foreach ($statusGroups as $status => $assets) {
            $distribution[$status] = $assets->groupBy('category.name')->map(function($categoryAssets, $categoryName) {
                return [
                    'name' => $categoryName ?: 'Uncategorized',
                    'count' => $categoryAssets->count(),
                ];
            })->sortByDesc('count');
        }
        return $distribution;
    }

    private function analyzeLocationDistributionByStatus($statusGroups){
        $distribution = [];
        foreach ($statusGroups as $status => $assets) {
            $distribution[$status] = $assets->groupBy('venue.name')->map(function($venueAssets, $venueName) {
                return [
                    'name' => $venueName ?: 'No Location',
                    'count' => $venueAssets->count(),
                ];
            })->sortByDesc('count');
        }
        return $distribution;
    }

    private function calculateAverageAssignmentDuration($assets) { return 120; }
    private function calculateAverageMaintenanceDuration($assets) { return 7; }
    private function calculateAssetTurnoverRate($assets) { return 0.25; }
    private function calculateUtilizationEfficiency($statusGroups) { return 75; }
    private function calculateMaintenanceFrequency($assets) { return 2.5; }
    private function calculateDisposalRate($assets) { return 0.05; }

    public function assetAssignmentReport(Request $request){
        $allAssignments = AssetAssignment::with([
            'asset.category',
            'asset.venue', 
            'assignable',
            'assignedByUser',
            'receivedByUser'
        ])
        ->orderBy('assigned_date', 'desc')
        ->get();

        $activeAssignments = $allAssignments->where('status', 'Assigned')->map(function($assignment) {
            $daysAssigned = $assignment->assigned_date->diffInDays(now());
            $isOverdue = $assignment->expected_return_date && $assignment->expected_return_date->isPast();
            $overdueDays = $isOverdue ? now()->diffInDays($assignment->expected_return_date) : 0;
            
            return [
                'assignment' => $assignment,
                'days_assigned' => $daysAssigned,
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
                'assignee_name' => $this->getAssigneeName($assignment),
                'assignee_type' => class_basename($assignment->assignable_type),
                'urgency_level' => $this->calculateUrgencyLevel($assignment, $isOverdue, $overdueDays),
                'condition_risk' => $this->assessAssignmentConditionRisk($assignment, $daysAssigned),
            ];
        })->sortByDesc('days_assigned');
        $assignmentHistory = $allAssignments->where('status', 'Returned')->map(function($assignment) {
            $assignmentDuration = $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            $wasOnTime = !$assignment->expected_return_date || $assignment->actual_return_date <= $assignment->expected_return_date;
            $conditionChange = $this->calculateConditionChange($assignment);
            
            return [
                'assignment' => $assignment,
                'duration_days' => $assignmentDuration,
                'was_on_time' => $wasOnTime,
                'days_late' => $wasOnTime ? 0 : $assignment->expected_return_date->diffInDays($assignment->actual_return_date),
                'assignee_name' => $this->getAssigneeName($assignment),
                'assignee_type' => class_basename($assignment->assignable_type),
                'condition_change' => $conditionChange,
                'assignment_success_score' => $this->calculateAssignmentSuccessScore($assignment, $wasOnTime, $conditionChange),
            ];
        })->sortByDesc('assignment.actual_return_date');

        $overdueAssignments = $activeAssignments->where('is_overdue', true)->sortByDesc('overdue_days');
        $userAssignmentPatterns = $this->analyzeUserAssignmentPatterns($allAssignments);
        $assetAssignmentFrequency = $this->analyzeAssetAssignmentFrequency($allAssignments);
        $durationAnalysis = $this->analyzeDurationPatterns($allAssignments);
        $monthlyTrends = $this->analyzeMonthlyAssignmentTrends($allAssignments);
        $categoryAssignmentPatterns = $this->analyzeCategoryAssignmentPatterns($allAssignments);
        $locationAssignmentPatterns = $this->analyzeLocationAssignmentPatterns($allAssignments);
        $conditionImpactAnalysis = $this->analyzeConditionImpact($allAssignments);

        $performanceInsights = [
            'most_reliable_assignees' => $userAssignmentPatterns->sortByDesc('reliability_score')->take(10),
            'most_active_assignees' => $userAssignmentPatterns->sortByDesc('total_assignments')->take(10),
            'problematic_assignees' => $userAssignmentPatterns->where('overdue_rate', '>', 20)->sortByDesc('overdue_rate')->take(10),
            'most_assigned_assets' => $assetAssignmentFrequency->sortByDesc('total_assignments')->take(10),
            'least_assigned_assets' => $assetAssignmentFrequency->where('total_assignments', '>', 0)->sortBy('total_assignments')->take(10),
            'high_maintenance_assets' => $assetAssignmentFrequency->where('condition_degradation_rate', '>', 0.5)->sortByDesc('condition_degradation_rate')->take(10),
        ];

        $healthMetrics = [
            'total_assignments' => $allAssignments->count(),
            'active_assignments' => $activeAssignments->count(),
            'overdue_assignments' => $overdueAssignments->count(),
            'overdue_percentage' => $activeAssignments->count() > 0 ? round(($overdueAssignments->count() / $activeAssignments->count()) * 100, 1) : 0,
            'average_assignment_duration' => round($assignmentHistory->avg('duration_days') ?? 0),
            'on_time_return_rate' => $assignmentHistory->count() > 0 ? round(($assignmentHistory->where('was_on_time', true)->count() / $assignmentHistory->count()) * 100, 1) : 0,
            'condition_preservation_rate' => $assignmentHistory->count() > 0 ? round(($assignmentHistory->where('condition_change.degraded', false)->count() / $assignmentHistory->count()) * 100, 1) : 0,
            'average_overdue_days' => round($overdueAssignments->avg('overdue_days') ?? 0),
            'unique_assignees' => $allAssignments->pluck('assignable_id')->unique()->count(),
            'repeat_assignment_rate' => $this->calculateRepeatAssignmentRate($allAssignments),
        ];

        $riskIndicators = [
            'high_risk_assignments' => $activeAssignments->where('urgency_level', 'High')->count(),
            'long_term_assignments' => $activeAssignments->where('days_assigned', '>', 365)->count(),
            'no_return_date_assignments' => $activeAssignments->filter(function($item) {
                return !$item['assignment']->expected_return_date;
            })->count(),
            'condition_risk_assignments' => $activeAssignments->where('condition_risk', 'High')->count(),
            'frequent_late_returners' => $userAssignmentPatterns->where('overdue_rate', '>', 30)->count(),
        ];

        return view('assets.reports.assignments-report', compact(
            'activeAssignments',
            'assignmentHistory', 
            'overdueAssignments',
            'userAssignmentPatterns',
            'assetAssignmentFrequency',
            'durationAnalysis',
            'monthlyTrends',
            'categoryAssignmentPatterns',
            'locationAssignmentPatterns',
            'conditionImpactAnalysis',
            'performanceInsights',
            'healthMetrics',
            'riskIndicators'
        ));
    }

    private function getAssigneeName($assignment){
        if (!$assignment->assignable) return 'Unknown';
        
        return $assignment->assignable->name ?? 
            $assignment->assignable->full_name ?? 
            ($assignment->assignable->firstname . ' ' . $assignment->assignable->lastname) ?? 
            'Unknown';
    }

    private function calculateUrgencyLevel($assignment, $isOverdue, $overdueDays){
        if ($isOverdue && $overdueDays > 30) return 'Critical';
        if ($isOverdue && $overdueDays > 7) return 'High';
        if ($isOverdue) return 'Medium';
        
        if ($assignment->expected_return_date) {
            $daysUntilDue = now()->diffInDays($assignment->expected_return_date, false);
            if ($daysUntilDue < 0) return 'High';
            if ($daysUntilDue <= 7) return 'Medium';
        }
        return 'Low';
    }

    private function assessAssignmentConditionRisk($assignment, $daysAssigned){
        $assetCondition = $assignment->asset->condition;
        $conditionScore = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1][$assetCondition] ?? 1;
        
        if ($daysAssigned > 365 && $conditionScore <= 2) return 'High';
        if ($daysAssigned > 180 && $conditionScore <= 3) return 'Medium';
        if ($daysAssigned > 90 && $conditionScore <= 2) return 'Medium';
        
        return 'Low';
    }

    private function calculateConditionChange($assignment){
        if (!$assignment->condition_on_assignment || !$assignment->condition_on_return) {
            return ['changed' => false, 'degraded' => false, 'improved' => false, 'change_level' => 0];
        }
        
        $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
        $startCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
        $endCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
        $changeLevel = $endCondition - $startCondition;
        
        return [
            'changed' => $changeLevel !== 0,
            'degraded' => $changeLevel < 0,
            'improved' => $changeLevel > 0,
            'change_level' => $changeLevel,
            'start_condition' => $assignment->condition_on_assignment,
            'end_condition' => $assignment->condition_on_return,
        ];
    }

    private function calculateAssignmentSuccessScore($assignment, $wasOnTime, $conditionChange){
        $score = 50;
        
        if ($wasOnTime) {
            $score += 25;
        } else {
            $lateDays = $assignment->expected_return_date ? $assignment->expected_return_date->diffInDays($assignment->actual_return_date) : 0;
            $score -= min($lateDays * 2, 25);
        }
        
        if (!$conditionChange['changed']) {
            $score += 25;
        } elseif ($conditionChange['improved']) {
            $score += 15;
        } else {
            $score -= abs($conditionChange['change_level']) * 10;
        }
        return max(0, min(100, $score));
    }

    private function analyzeUserAssignmentPatterns($assignments){
        return $assignments->groupBy('assignable_id')->map(function($userAssignments, $userId) {
            $assignee = $userAssignments->first()->assignable;
            $totalAssignments = $userAssignments->count();
            $completedAssignments = $userAssignments->where('status', 'Returned');
            $overdueAssignments = $userAssignments->where('status', 'Assigned')->filter(function($assignment) {
                return $assignment->expected_return_date && $assignment->expected_return_date->isPast();
            });
            
            $onTimeReturns = $completedAssignments->filter(function($assignment) {
                return !$assignment->expected_return_date || $assignment->actual_return_date <= $assignment->expected_return_date;
            });
            
            $averageDuration = $completedAssignments->avg(function($assignment) {
                return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            }) ?? 0;
            
            return [
                'assignee' => $assignee,
                'assignee_name' => $this->getAssigneeName($userAssignments->first()),
                'total_assignments' => $totalAssignments,
                'active_assignments' => $userAssignments->where('status', 'Assigned')->count(),
                'completed_assignments' => $completedAssignments->count(),
                'overdue_count' => $overdueAssignments->count(),
                'overdue_rate' => $totalAssignments > 0 ? round(($overdueAssignments->count() / $totalAssignments) * 100, 1) : 0,
                'on_time_rate' => $completedAssignments->count() > 0 ? round(($onTimeReturns->count() / $completedAssignments->count()) * 100, 1) : 0,
                'average_duration' => round($averageDuration),
                'reliability_score' => $this->calculateReliabilityScore($onTimeReturns->count(), $completedAssignments->count(), $overdueAssignments->count()),
                'last_assignment_date' => $userAssignments->max('assigned_date'),
            ];
        });
    }

    private function analyzeAssetAssignmentFrequency($assignments){
        return $assignments->groupBy('asset_id')->map(function($assetAssignments, $assetId) {
            $asset = $assetAssignments->first()->asset;
            $totalAssignments = $assetAssignments->count();
            $completedAssignments = $assetAssignments->where('status', 'Returned');
            
            $conditionChanges = $completedAssignments->map(function($assignment) {
                return $this->calculateConditionChange($assignment);
            });
            
            $degradationCount = $conditionChanges->where('degraded', true)->count();
            $conditionDegradationRate = $completedAssignments->count() > 0 ? $degradationCount / $completedAssignments->count() : 0;
            
            $averageDuration = $completedAssignments->avg(function($assignment) {
                return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            }) ?? 0;
            
            return [
                'asset' => $asset,
                'total_assignments' => $totalAssignments,
                'active_assignments' => $assetAssignments->where('status', 'Assigned')->count(),
                'completed_assignments' => $completedAssignments->count(),
                'unique_assignees' => $assetAssignments->pluck('assignable_id')->unique()->count(),
                'average_assignment_duration' => round($averageDuration),
                'condition_degradation_rate' => round($conditionDegradationRate, 2),
                'last_assignment_date' => $assetAssignments->max('assigned_date'),
                'assignment_frequency_score' => $this->calculateAssignmentFrequencyScore($totalAssignments, $asset->created_at),
            ];
        });
    }

    private function analyzeDurationPatterns($assignments){
        $completedAssignments = $assignments->where('status', 'Returned');
        $durations = $completedAssignments->map(function($assignment) {
            return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
        });
        
        return [
            'average_duration' => round($durations->avg() ?? 0),
            'median_duration' => $durations->count() > 0 ? $durations->sort()->values()->get(intval($durations->count() / 2)) : 0,
            'shortest_duration' => $durations->min() ?? 0,
            'longest_duration' => $durations->max() ?? 0,
            'duration_ranges' => [
                '1_7_days' => $durations->filter(function($d) { return $d >= 1 && $d <= 7; })->count(),
                '8_30_days' => $durations->filter(function($d) { return $d >= 8 && $d <= 30; })->count(),
                '31_90_days' => $durations->filter(function($d) { return $d >= 31 && $d <= 90; })->count(),
                '91_365_days' => $durations->filter(function($d) { return $d >= 91 && $d <= 365; })->count(),
                'over_365_days' => $durations->filter(function($d) { return $d > 365; })->count(),
            ]
        ];
    }

    private function analyzeMonthlyAssignmentTrends($assignments){
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthAssignments = $assignments->filter(function($assignment) use ($month) {
                return $assignment->assigned_date->format('Y-m') === $month->format('Y-m');
            });
            
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'month_key' => $month->format('Y-m'),
                'new_assignments' => $monthAssignments->count(),
                'returns' => $assignments->filter(function($assignment) use ($month) {
                    return $assignment->actual_return_date && $assignment->actual_return_date->format('Y-m') === $month->format('Y-m');
                })->count(),
            ];
        }
        return collect($monthlyData);
    }

    private function analyzeCategoryAssignmentPatterns($assignments) { 
        return collect();
    }

    private function analyzeLocationAssignmentPatterns($assignments) { 
        return collect();
    }

    private function analyzeConditionImpact($assignments) { 
        return [];
    }

    private function calculateReliabilityScore($onTimeCount, $totalCompleted, $overdueCount){
        if ($totalCompleted === 0) return 0;
        
        $onTimeRate = $onTimeCount / $totalCompleted;
        $overdueRate = $overdueCount / ($totalCompleted + $overdueCount);
        
        return round((($onTimeRate * 0.7) + ((1 - $overdueRate) * 0.3)) * 100, 1);
    }

    private function calculateAssignmentFrequencyScore($totalAssignments, $assetCreatedAt){
        $monthsSinceCreation = $assetCreatedAt->diffInMonths(now());
        if ($monthsSinceCreation === 0) return 0;
        
        return round($totalAssignments / $monthsSinceCreation, 2);
    }

    private function calculateRepeatAssignmentRate($assignments){
        $assetAssignmentCounts = $assignments->groupBy('asset_id')->map->count();
        $multipleAssignments = $assetAssignmentCounts->filter(function($count) { return $count > 1; });
        
        return $assetAssignmentCounts->count() > 0 ? 
            round(($multipleAssignments->count() / $assetAssignmentCounts->count()) * 100, 1) : 0;
    }

    public function assetMaintenanceReport(Request $request){
        $allMaintenance = AssetMaintenance::with([
            'asset.category',
            'asset.venue',
            'vendor',
            'performedByUser'
        ])->orderBy('maintenance_date', 'desc')->get();

        $activeMaintenance = $allMaintenance->where('status', 'In Progress')->map(function($maintenance) {
            $daysInMaintenance = $maintenance->maintenance_date->diffInDays(now());
            
            return [
                'maintenance' => $maintenance,
                'days_in_maintenance' => $daysInMaintenance,
                'priority_level' => $this->calculateMaintenancePriority($maintenance, $daysInMaintenance),
            ];
        })->sortByDesc('days_in_maintenance');

        $maintenanceHistory = $allMaintenance->where('status', 'Completed')->map(function($maintenance) {
            $duration = $maintenance->maintenance_date->diffInDays($maintenance->updated_at ?? $maintenance->maintenance_date);
            
            return [
                'maintenance' => $maintenance,
                'duration_days' => max($duration, 1), // Ensure at least 1 day
                'cost_per_day' => $maintenance->cost && $duration > 0 ? round($maintenance->cost / max($duration, 1), 2) : 0,
            ];
        })->sortByDesc('maintenance.maintenance_date');

        $scheduledMaintenance = $allMaintenance->where('status', 'Scheduled')->map(function($maintenance) {
            $daysUntilDue = $maintenance->maintenance_date->diffInDays(now(), false);
            $isOverdue = $daysUntilDue < 0;
            
            return [
                'maintenance' => $maintenance,
                'days_until_due' => abs($daysUntilDue),
                'is_overdue' => $isOverdue,
                'urgency_level' => $this->calculateScheduleUrgency($daysUntilDue),
            ];
        })->sortBy('maintenance.maintenance_date');

        $assetMaintenanceAnalysis = $allMaintenance->groupBy('asset_id')->map(function($assetMaintenance, $assetId) {
            $asset = $assetMaintenance->first()->asset;
            $completedMaintenance = $assetMaintenance->where('status', 'Completed');
            $totalCost = $assetMaintenance->sum('cost');
            
            return [
                'asset' => $asset,
                'total_maintenance_count' => $assetMaintenance->count(),
                'completed_count' => $completedMaintenance->count(),
                'active_count' => $assetMaintenance->whereIn('status', ['In Progress', 'Scheduled'])->count(),
                'total_cost' => $totalCost,
                'average_cost' => $completedMaintenance->count() > 0 ? round($totalCost / $completedMaintenance->count(), 2) : 0,
                'last_maintenance_date' => $assetMaintenance->max('maintenance_date'),
            ];
        })->sortByDesc('total_cost');

        $vendorPerformanceAnalysis = $allMaintenance->whereNotNull('contact_id')->groupBy('contact_id')->map(function($vendorMaintenance, $vendorId) {
            $vendor = $vendorMaintenance->first()->vendor;
            $completedMaintenance = $vendorMaintenance->where('status', 'Completed');
            $totalCost = $vendorMaintenance->sum('cost');
            
            return [
                'vendor' => $vendor,
                'total_jobs' => $vendorMaintenance->count(),
                'completed_jobs' => $completedMaintenance->count(),
                'in_progress_jobs' => $vendorMaintenance->where('status', 'In Progress')->count(),
                'total_cost' => $totalCost,
                'average_cost' => $completedMaintenance->count() > 0 ? round($totalCost / $completedMaintenance->count(), 2) : 0,
                'last_job_date' => $vendorMaintenance->max('maintenance_date'),
            ];
        })->sortByDesc('total_cost');

        $costAnalysis = [
            'total_spend' => $allMaintenance->sum('cost'),
            'average_cost' => round($allMaintenance->where('cost', '>', 0)->avg('cost') ?? 0, 2),
            'highest_cost' => $allMaintenance->max('cost') ?? 0,
            'lowest_cost' => $allMaintenance->where('cost', '>', 0)->min('cost') ?? 0,
            'cost_ranges' => [
                'under_500' => $allMaintenance->where('cost', '<', 500)->count(),
                '500_2000' => $allMaintenance->whereBetween('cost', [500, 2000])->count(),
                '2000_5000' => $allMaintenance->whereBetween('cost', [2000, 5000])->count(),
                '5000_10000' => $allMaintenance->whereBetween('cost', [5000, 10000])->count(),
                'over_10000' => $allMaintenance->where('cost', '>', 10000)->count(),
            ],
        ];

        $typeAnalysis = [
            'preventive' => [
                'count' => $allMaintenance->where('maintenance_type', 'Preventive')->count(),
                'cost' => $allMaintenance->where('maintenance_type', 'Preventive')->sum('cost'),
                'percentage' => $allMaintenance->count() > 0 ? round(($allMaintenance->where('maintenance_type', 'Preventive')->count() / $allMaintenance->count()) * 100, 1) : 0,
            ],
            'corrective' => [
                'count' => $allMaintenance->where('maintenance_type', 'Corrective')->count(),
                'cost' => $allMaintenance->where('maintenance_type', 'Corrective')->sum('cost'),
                'percentage' => $allMaintenance->count() > 0 ? round(($allMaintenance->where('maintenance_type', 'Corrective')->count() / $allMaintenance->count()) * 100, 1) : 0,
            ],
            'upgrade' => [
                'count' => $allMaintenance->where('maintenance_type', 'Upgrade')->count(),
                'cost' => $allMaintenance->where('maintenance_type', 'Upgrade')->sum('cost'),
                'percentage' => $allMaintenance->count() > 0 ? round(($allMaintenance->where('maintenance_type', 'Upgrade')->count() / $allMaintenance->count()) * 100, 1) : 0,
            ],
        ];

        $monthlyTrends = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthMaintenance = $allMaintenance->filter(function($maintenance) use ($month) {
                return $maintenance->maintenance_date->format('Y-m') === $month->format('Y-m');
            });
            
            $monthlyTrends->push([
                'month' => $month->format('M Y'),
                'total_maintenance' => $monthMaintenance->count(),
                'preventive_count' => $monthMaintenance->where('maintenance_type', 'Preventive')->count(),
                'corrective_count' => $monthMaintenance->where('maintenance_type', 'Corrective')->count(),
                'total_cost' => $monthMaintenance->sum('cost'),
            ]);
        }

        $performanceMetrics = [
            'total_maintenance_records' => $allMaintenance->count(),
            'active_maintenance' => $activeMaintenance->count(),
            'completed_maintenance' => $maintenanceHistory->count(),
            'scheduled_maintenance' => $scheduledMaintenance->count(),
            'overdue_scheduled' => $scheduledMaintenance->where('is_overdue', true)->count(),
            'total_maintenance_spend' => $allMaintenance->sum('cost'),
            'preventive_maintenance_rate' => $allMaintenance->count() > 0 ? 
                round(($allMaintenance->where('maintenance_type', 'Preventive')->count() / $allMaintenance->count()) * 100, 1) : 0,
        ];

        $healthIndicators = [
            'high_priority_active' => $activeMaintenance->where('priority_level', 'High')->count(),
            'long_duration_maintenance' => $activeMaintenance->where('days_in_maintenance', '>', 30)->count(),
            'overdue_scheduled_critical' => $scheduledMaintenance->where('is_overdue', true)->where('urgency_level', 'Critical')->count(),
            'high_cost_maintenance' => $allMaintenance->where('cost', '>', 10000)->count(),
        ];

        return view('assets.reports.maintenance-report', compact(
            'activeMaintenance',
            'maintenanceHistory',
            'scheduledMaintenance',
            'assetMaintenanceAnalysis',
            'vendorPerformanceAnalysis',
            'costAnalysis',
            'typeAnalysis',
            'monthlyTrends',
            'performanceMetrics',
            'healthIndicators'
        ));
    }

    private function calculateMaintenancePriority($maintenance, $daysInMaintenance){
        $assetValue = $maintenance->asset->purchase_price ?? 0;
        $maintenanceCost = $maintenance->cost ?? 0;
        
        if ($daysInMaintenance > 30 && $assetValue > 50000) return 'Critical';
        if ($daysInMaintenance > 14 && ($assetValue > 20000 || $maintenanceCost > 5000)) return 'High';
        if ($daysInMaintenance > 7 || $maintenanceCost > 2000) return 'Medium';
        
        return 'Low';
    }

    private function calculateScheduleUrgency($daysUntilDue){
        if ($daysUntilDue < 0) return 'Overdue';
        if ($daysUntilDue <= 7) return 'Critical';
        if ($daysUntilDue <= 14) return 'High';
        if ($daysUntilDue <= 30) return 'Medium';
        
        return 'Low';
    }

    public function assetUtilizationReport(Request $request){
        $allAssets = Asset::with([
            'category',
            'venue',
            'assignments' => function($query) {
                $query->orderBy('assigned_date', 'desc');
            }
        ])->get();

        $individualUtilization = $allAssets->map(function($asset) {
            $totalDaysSinceCreation = $asset->created_at->diffInDays(now());
            $totalDaysSinceCreation = max($totalDaysSinceCreation, 1);
            
            $totalDaysAssigned = 0;
            foreach($asset->assignments as $assignment) {
                if ($assignment->status === 'Assigned') {
                    $totalDaysAssigned += $assignment->assigned_date->diffInDays(now());
                } elseif ($assignment->status === 'Returned' && $assignment->actual_return_date) {
                    $totalDaysAssigned += $assignment->assigned_date->diffInDays($assignment->actual_return_date);
                }
            }
            
            $utilizationRate = round(($totalDaysAssigned / $totalDaysSinceCreation) * 100, 1);
            $totalDaysIdle = $totalDaysSinceCreation - $totalDaysAssigned;
            $idleRate = round(($totalDaysIdle / $totalDaysSinceCreation) * 100, 1);
            
            $currentAssignment = $asset->assignments->where('status', 'Assigned')->first();
            $isCurrentlyAssigned = $currentAssignment ? true : false;
            $currentIdleDays = 0;
            
            if (!$isCurrentlyAssigned) {
                $lastAssignment = $asset->assignments->where('status', 'Returned')->first();
                if ($lastAssignment && $lastAssignment->actual_return_date) {
                    $currentIdleDays = $lastAssignment->actual_return_date->diffInDays(now());
                } else {
                    $currentIdleDays = $totalDaysSinceCreation;
                }
            }
            
            return [
                'asset' => $asset,
                'total_days_since_creation' => $totalDaysSinceCreation,
                'total_days_assigned' => $totalDaysAssigned,
                'total_days_idle' => $totalDaysIdle,
                'utilization_rate' => $utilizationRate,
                'idle_rate' => $idleRate,
                'is_currently_assigned' => $isCurrentlyAssigned,
                'current_idle_days' => $currentIdleDays,
                'total_assignments' => $asset->assignments->count(),
                'assignment_frequency' => $asset->assignments->count() > 0 ? 
                    round($totalDaysSinceCreation / $asset->assignments->count(), 1) : 0,
                'utilization_category' => $this->getUtilizationCategory($utilizationRate),
            ];
        })->sortByDesc('utilization_rate');

        $categoryUtilization = $allAssets->groupBy('category.name')->map(function($categoryAssets, $categoryName) {
            $categoryName = $categoryName ?: 'Uncategorized';
            $totalAssets = $categoryAssets->count();
            
            $totalDaysSinceCreation = $categoryAssets->sum(function($asset) {
                return $asset->created_at->diffInDays(now());
            });
            
            $totalDaysAssigned = 0;
            $currentlyAssignedCount = 0;
            $totalAssignments = 0;
            
            foreach($categoryAssets as $asset) {
                foreach($asset->assignments as $assignment) {
                    $totalAssignments++;
                    if ($assignment->status === 'Assigned') {
                        $totalDaysAssigned += $assignment->assigned_date->diffInDays(now());
                        $currentlyAssignedCount++;
                    } elseif ($assignment->status === 'Returned' && $assignment->actual_return_date) {
                        $totalDaysAssigned += $assignment->assigned_date->diffInDays($assignment->actual_return_date);
                    }
                }
            }
            
            $categoryUtilizationRate = $totalDaysSinceCreation > 0 ? 
                round(($totalDaysAssigned / $totalDaysSinceCreation) * 100, 1) : 0;
            
            $availableCount = $totalAssets - $currentlyAssignedCount;
            $assignedPercentage = $totalAssets > 0 ? round(($currentlyAssignedCount / $totalAssets) * 100, 1) : 0;
            
            return [
                'category_name' => $categoryName,
                'total_assets' => $totalAssets,
                'currently_assigned' => $currentlyAssignedCount,
                'currently_available' => $availableCount,
                'assigned_percentage' => $assignedPercentage,
                'category_utilization_rate' => $categoryUtilizationRate,
                'total_assignments' => $totalAssignments,
                'average_assignments_per_asset' => $totalAssets > 0 ? round($totalAssignments / $totalAssets, 1) : 0,
                'utilization_category' => $this->getUtilizationCategory($categoryUtilizationRate),
            ];
        })->sortByDesc('category_utilization_rate');

        $overallUtilization = [
            'total_assets' => $allAssets->count(),
            'currently_assigned' => $individualUtilization->where('is_currently_assigned', true)->count(),
            'currently_available' => $individualUtilization->where('is_currently_assigned', false)->count(),
            'overall_assignment_percentage' => $allAssets->count() > 0 ? 
                round(($individualUtilization->where('is_currently_assigned', true)->count() / $allAssets->count()) * 100, 1) : 0,
            'average_utilization_rate' => round($individualUtilization->avg('utilization_rate'), 1),
            'high_utilization_assets' => $individualUtilization->where('utilization_category', 'High')->count(),
            'medium_utilization_assets' => $individualUtilization->where('utilization_category', 'Medium')->count(),
            'low_utilization_assets' => $individualUtilization->where('utilization_category', 'Low')->count(),
            'idle_assets' => $individualUtilization->where('utilization_category', 'Idle')->count(),
        ];

        $utilizationRanges = [
            'excellent' => $individualUtilization->where('utilization_rate', '>=', 80)->count(),
            'good' => $individualUtilization->whereBetween('utilization_rate', [60, 79])->count(),
            'moderate' => $individualUtilization->whereBetween('utilization_rate', [40, 59])->count(),
            'low' => $individualUtilization->whereBetween('utilization_rate', [20, 39])->count(),
            'poor' => $individualUtilization->whereBetween('utilization_rate', [1, 19])->count(),
            'unused' => $individualUtilization->where('utilization_rate', 0)->count(),
        ];

        $longIdleAssets = $individualUtilization->filter(function($asset) {
            return !$asset['is_currently_assigned'] && $asset['current_idle_days'] > 90;
        })->sortByDesc('current_idle_days');

        $highUtilizationAssets = $individualUtilization->where('utilization_rate', '>=', 80)->sortByDesc('utilization_rate');

        return view('assets.reports.utilization-report', compact(
            'individualUtilization',
            'categoryUtilization',
            'overallUtilization',
            'utilizationRanges',
            'longIdleAssets',
            'highUtilizationAssets'
        ));
    }

    private function getUtilizationCategory($utilizationRate){
        if ($utilizationRate >= 80) return 'High';
        if ($utilizationRate >= 50) return 'Medium';
        if ($utilizationRate >= 20) return 'Low';
        if ($utilizationRate > 0) return 'Minimal';
        return 'Idle';
    }

    public function importAssets(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        set_time_limit(300);
        if ($request->has('clear_data_first')) {
            $this->clearAssetData();
            if (Asset::count() > 0 || Asset::withTrashed()->count() > 0) {
                return redirect()->back()->with('error', 'Assets table was not cleared!');
            }
        }

        $options = [
            'create_missing_categories' => $request->has('create_missing_categories'),
            'create_missing_contacts' => $request->has('create_missing_contacts') || $request->has('create_missing_vendors'),
        ];

        $import = new AssetsImport($options);
        try {
            $fileType = $this->getFileType($request->file('file')->getClientOriginalName());
            Excel::import($import, $request->file('file'), null, $fileType);

            $rowCount = $import->rowsCount;
            if ($import->failures()->isNotEmpty()) {
                $failureMessages = [];
                foreach ($import->failures() as $failure) {
                    $errorMessages = implode(', ', $failure->errors());
                    $failureMessages[] = "Row {$failure->row()}: {$errorMessages}";
                }
                return redirect()->back()->withErrors($failureMessages);
            }

            $message = "Assets imported successfully. Total assets processed: {$rowCount}";
            if (!empty($import->createdCategories)) {
                $message .= ". Categories created: " . implode(', ', $import->createdCategories);
            }
            if (!empty($import->createdContacts)) {
                $message .= ". Business contacts created: " . implode(', ', $import->createdContacts);
            }

            return redirect()->back()->with('message', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing Assets: ' . $e->getMessage());
        }
    }

    public function downloadTemplate(){
        $headers = [
            'Asset Name*',
            'Asset Code*', 
            'Category*',
            'Business Contact',
            'Location',
            'Status',
            'Manufacturer',
            'Model',
            'Purchase Date',
            'Purchase Price',
            'Current Value',
            'Warranty Expiry',
            'Expected Lifespan',
            'Invoice Number',
            'Condition',
            'Specifications',
            'Notes'
        ];

        $sampleData = [
            ['MacBook Pro 16"', 'LAPTOP-2024-001', 'IT Equipment', 'Apple Inc', 'Main Office', 'Available', 'Apple', 'MacBook Pro M3', '2024-01-15', '40000.00', '3500.00', '2027-01-15', '36', 'INV-2024-0015', 'New', '16" Liquid Retina XDR display, M3 Pro chip, 32GB RAM, 1TB SSD', 'For development team'],
            ['Dell Laptop', 'LAPTOP-2024-002', 'IT Equipment', 'Dell Technologies', 'IT Department', 'Availale', 'Dell', 'Latitude 7420', '2024-02-10', '7000.00', '4500.00', '2027-02-10', '36', 'INV-2024-0025', 'Good', '14" FHD, Intel i7, 16GB RAM, 512GB SSD', 'Assigned to John Doe'],
            ['HP Desktop Computer', 'DESKTOP-2023-001', 'IT Equipment', 'HP Inc', 'Computer Lab', 'Available', 'HP', 'EliteDesk 800 G9', '2023-09-15', '4500.00', '3200.00', '2026-09-15', '36', 'INV-2023-0125', 'Good', 'Intel i5, 8GB RAM, 256GB SSD, Windows 11', 'Lab computer #1'],
            ['Lenovo ThinkPad', 'LAPTOP-2023-003', 'IT Equipment', 'Lenovo', 'Remote Work', 'Available', 'Lenovo', 'ThinkPad X1 Carbon', '2023-11-20', '19000.00', '13000.00', '2026-11-20', '36', 'INV-2023-0178', 'Good', '14" WUXGA, Intel i7, 16GB RAM, 1TB SSD', 'Work from home device'],
            ['Dell Monitor 27"', 'MONITOR-2024-001', 'IT Equipment', 'Dell Technologies', 'Development Area', 'Available', 'Dell', 'UltraSharp U2723QE', '2024-03-05', '7200.00', '5500.00', '2027-03-05', '36', 'INV-2024-0078', 'New', '27" 4K USB-C Hub Monitor with 90W Power Delivery', 'Assigned to senior developer'],
            ['Samsung Monitor 24"', 'MONITOR-2023-002', 'IT Equipment', 'Samsung', 'Reception', 'Available', 'Samsung', 'F24T450FQN', '2023-08-12', '2000.00', '1600.00', '2026-08-12', '36', 'INV-2023-0098', 'Good', '24" Full HD IPS Monitor', 'Reception desk monitor'],
            ['iPad Pro 12.9"', 'TABLET-2024-001', 'IT Equipment', 'Apple Inc', 'Executive Office', 'Available', 'Apple', 'iPad Pro M2', '2024-01-20', '12000.00', '9500.00', '2027-01-20', '36', 'INV-2024-0021', 'New', '12.9" Liquid Retina XDR display, M2 chip, 256GB', 'For presentations'],
            ['Surface Pro 9', 'TABLET-2023-001', 'IT Equipment', 'Microsoft', 'Marketing', 'Available', 'Microsoft', 'Surface Pro 9', '2023-10-05', '14999.00', '11999.00', '2026-10-05', '36', 'INV-2023-0145', 'Good', '13" PixelSense touchscreen, Intel i7, 16GB RAM', 'Marketing team device'],
            
            ['Executive Office Chair', 'CHAIR-2024-001', 'Office Furniture', 'Herman Miller', 'Executive Office', 'Available', 'Herman Miller', 'Aeron Chair', '2024-02-20', '1200.00', '1100.00', '2029-02-20', '120', 'INV-2024-0032', 'Good', 'Ergonomic office chair with lumbar support, breathable mesh', 'For executive office'],
            ['Standing Desk', 'DESK-2024-001', 'Office Furniture', 'IKEA Business', 'Open Office', 'Available', 'IKEA', 'BEKANT Sit/Stand', '2024-03-10', '450.00', '400.00', '2029-03-10', '120', 'INV-2024-0065', 'New', 'Height adjustable desk, 160x80cm, white', 'Hot desk area'],
            ['Conference Table', 'TABLE-2023-001', 'Office Furniture', 'Steelcase', 'Conference Room A', 'Available', 'Steelcase', 'Series 7 Conference', '2023-06-15', '2200.00', '1900.00', '2028-06-15', '120', 'INV-2023-0078', 'Good', '12-person conference table, oak finish', 'Main conference room'],
            ['Office Bookshelf', 'SHELF-2023-001', 'Office Furniture', 'IKEA Business', 'Library', 'Available', 'IKEA', 'BILLY Bookcase', '2023-04-08', '85.00', '70.00', '2028-04-08', '120', 'INV-2023-0045', 'Fair', '5-shelf bookcase, white finish', 'Storage for manuals'],
            ['Reception Sofa', 'SOFA-2023-001', 'Office Furniture', 'West Elm Business', 'Reception', 'Available', 'West Elm', 'Andes Sectional', '2023-07-22', '1599.00', '1300.00', '2028-07-22', '120', 'INV-2023-0089', 'Good', '3-seat sectional sofa, navy blue fabric', 'Reception waiting area'],
            ['Drafting Table', 'TABLE-2024-002', 'Office Furniture', 'Wayfair Business', 'Design Studio', 'Available', 'Studio Designs', 'Vision Craft', '2024-01-30', '320.00', '280.00', '2029-01-30', '120', 'INV-2024-0028', 'New', 'Adjustable height drafting table with storage', 'For design work'],
            
            ['Canon Multifunction Printer', 'PRINTER-2024-001', 'Office Equipment', 'Canon Inc', 'IT Room', 'Available', 'Canon', 'imageCLASS MF445dw', '2023-11-10', '450.00', '400.00', '2025-11-10', '24', 'INV-2023-0156', 'Fair', 'Monochrome laser multifunction printer with WiFi', 'Requires toner replacement'],
            ['HP Color LaserJet', 'PRINTER-2024-002', 'Office Equipment', 'HP Inc', 'Marketing', 'Available', 'HP', 'Color LaserJet Pro M454dw', '2024-02-14', '320.00', '290.00', '2027-02-14', '36', 'INV-2024-0035', 'Good', 'Color laser printer with wireless connectivity', 'For marketing materials'],
            ['Brother Label Printer', 'PRINTER-2023-003', 'Office Equipment', 'Brother International', 'Warehouse', 'Available', 'Brother', 'P-touch PT-D600', '2023-12-05', '180.00', '150.00', '2026-12-05', '36', 'INV-2023-0189', 'Good', 'Desktop label printer with PC connectivity', 'For inventory labels'],
            ['Shredder CrossCut', 'SHREDDER-2023-001', 'Office Equipment', 'Fellowes', 'HR Office', 'Available', 'Fellowes', 'Powershred 99Ci', '2023-09-18', '450.00', '380.00', '2026-09-18', '36', 'INV-2023-0127', 'Good', '18-sheet cross-cut shredder with jam protection', 'HR document disposal'],
            ['Laminator Machine', 'LAMINATOR-2024-001', 'Office Equipment', 'GBC', 'Print Room', 'Available', 'GBC', 'Thermal Laminator', '2024-01-12', '120.00', '100.00', '2027-01-12', '36', 'INV-2024-0018', 'New', '9" thermal laminator for documents', 'For ID cards and signage'],
            
            ['Company Van', 'VEHICLE-2022-001', 'Vehicles', 'Ford Dealership', 'Main Parking', 'Available', 'Ford', 'Transit Connect', '2022-08-15', '28000.00', '22000.00', '2025-08-15', '36', 'INV-2022-0087', 'Good', '2.0L EcoBoost, 7-passenger, white', 'For staff transportation'],
            ['Delivery Truck', 'VEHICLE-2021-001', 'Vehicles', 'Isuzu Commercial', 'Loading Bay', 'Available', 'Isuzu', 'NPR-HD', '2021-03-20', '45000.00', '35000.00', '2024-03-20', '36', 'INV-2021-0034', 'Fair', 'Box truck, diesel engine, 14ft box', 'Scheduled maintenance'],
            ['Golf Cart', 'VEHICLE-2023-001', 'Vehicles', 'Club Car', 'Facilities', 'Available', 'Club Car', 'Villager 4', '2023-05-10', '8500.00', '7500.00', '2026-05-10', '36', 'INV-2023-0067', 'Good', 'Electric 4-passenger utility vehicle', 'Campus transportation'],
            
            ['Industrial Vacuum', 'VACUUM-2023-001', 'Facilities & Maintenance', 'Bissell Commercial', 'Custodial', 'Available', 'Bissell', 'BigGreen Commercial', '2023-07-30', '250.00', '200.00', '2026-07-30', '36', 'INV-2023-0092', 'Good', 'Upright commercial vacuum with HEPA filter', 'Daily cleaning'],
            ['Floor Buffer', 'BUFFER-2022-001', 'Facilities & Maintenance', 'Clarke Commercial', 'Custodial', 'Available', 'Clarke', 'MA10 12E', '2022-11-15', '380.00', '300.00', '2025-11-15', '36', 'INV-2022-0123', 'Fair', '12" electric floor machine', 'Floor maintenance'],
            ['Pressure Washer', 'WASHER-2023-001', 'Facilities & Maintenance', 'Karcher USA', 'Maintenance Shed', 'Available', 'Karcher', 'K1700 Electric', '2023-04-25', '150.00', '120.00', '2026-04-25', '36', 'INV-2023-0056', 'Good', '1700 PSI electric pressure washer', 'Exterior cleaning'],
            ['Generator Backup', 'GENERATOR-2022-001', 'Facilities & Maintenance', 'Generac Power', 'Utility Room', 'Available', 'Generac', 'GP3500iO', '2022-09-12', '899.00', '750.00', '2025-09-12', '36', 'INV-2022-0098', 'Good', '3500W inverter generator, portable', 'Emergency backup power'],
            
            ['Cordless Drill Set', 'DRILL-2024-001', 'Tools & Equipment', 'DeWalt', 'Tool Room', 'Available', 'DeWalt', 'DCD771C2', '2024-01-08', '129.00', '110.00', '2027-01-08', '36', 'INV-2024-0012', 'New', '20V MAX cordless drill with 2 batteries', 'General maintenance'],
            ['Socket Wrench Set', 'WRENCH-2023-001', 'Tools & Equipment', 'Craftsman', 'Tool Room', 'Available', 'Craftsman', 'Professional 450-pc', '2023-10-22', '199.00', '170.00', '2026-10-22', '36', 'INV-2023-0148', 'Good', '450-piece mechanic\'s tool set', 'Vehicle maintenance'],
            ['Ladder Extension', 'LADDER-2023-001', 'Tools & Equipment', 'Werner', 'Storage Room', 'Available', 'Werner', 'D6228-2', '2023-06-18', '180.00', '150.00', '2026-06-18', '36', 'INV-2023-0081', 'Good', '28ft fiberglass extension ladder', 'Building maintenance'],
            
            ['Commercial Refrigerator', 'FRIDGE-2023-001', 'Kitchen & Cafeteria', 'True Manufacturing', 'Kitchen', 'Available', 'True', 'T-72', '2023-08-05', '2800.00', '2400.00', '2026-08-05', '36', 'INV-2023-0101', 'Good', '72" 3-door reach-in refrigerator', 'Staff kitchen'],
            ['Microwave Oven', 'MICROWAVE-2024-001', 'Kitchen & Cafeteria', 'Panasonic', 'Break Room', 'Available', 'Panasonic', 'NN-SN686S', '2024-02-28', '150.00', '130.00', '2027-02-28', '24', 'INV-2024-0045', 'New', '1.2 cu ft countertop microwave', 'Break room appliance'],
            ['Coffee Machine', 'COFFEE-2023-001', 'Kitchen & Cafeteria', 'Keurig Commercial', 'Break Room', 'Available', 'Keurig', 'K-3500', '2023-09-10', '450.00', '380.00', '2026-09-10', '36', 'INV-2023-0119', 'Good', 'Commercial coffee brewing system', 'Staff break room'],
            
            ['Security Camera System', 'CAMERA-2023-001', 'Security & Safety', 'Hikvision', 'Building Perimeter', 'Available', 'Hikvision', 'DS-2CD2147G2-L', '2023-05-15', '1200.00', '1000.00', '2026-05-15', '36', 'INV-2023-0069', 'Good', '4MP ColorVu fixed turret network camera', 'Building security'],
            ['Fire Extinguisher', 'EXTINGUISHER-2024-001', 'Security & Safety', 'Amerex', 'Hallway A', 'Available', 'Amerex', 'B500', '2024-01-15', '45.00', '40.00', '2029-01-15', '60', 'INV-2024-0019', 'New', '5lb ABC dry chemical fire extinguisher', 'Safety equipment'],
            ['First Aid Kit', 'FIRSTAID-2024-001', 'Security & Safety', 'Johnson & Johnson', 'Reception', 'Available', 'Johnson & Johnson', 'All Purpose Kit', '2024-03-01', '25.00', '23.00', '2029-03-01', '60', 'INV-2024-0055', 'New', '140-piece first aid kit', 'Emergency medical supplies']
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asset Import Template');

        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($columnLetter . '1', $header);
            
            $sheet->getStyle($columnLetter . '1')->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E3F2FD'));
            
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnIndex++;
        }

        $rowIndex = 2;
        foreach ($sampleData as $row) {
            $columnIndex = 1;
            foreach ($row as $value) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($columnLetter . $rowIndex, $value);
                $columnIndex++;
            }
            $rowIndex++;
        }

        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = $rowIndex - 1;
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $filename = 'Asset_Import_Template.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function assetContacts(){
        return Contact::query()
            ->eligibleForAssets()
            ->with(['primaryPerson', 'tags'])
            ->orderBy('name')
            ->get();
    }

    private function assetContactValidationRule(): array
    {
        return [
            'nullable',
            Rule::exists('contacts', 'id')->where(function ($query) {
                $query
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->whereExists(function ($tagQuery) {
                        $tagQuery->select(DB::raw(1))
                            ->from('contact_contact_tag')
                            ->join('contact_tags', 'contact_tags.id', '=', 'contact_contact_tag.contact_tag_id')
                            ->whereColumn('contact_contact_tag.contact_id', 'contacts.id')
                            ->where('contact_tags.is_active', true)
                            ->where('contact_tags.usable_in_assets', true);
                    });
            }),
        ];
    }

    private function normalizeSelectedContactId(Request $request): ?int
    {
        $value = $request->input('contact_id', $request->input('vendor_id'));

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function clearAssetData(){
        Asset::query()->forceDelete();
    }

    private function getFileType($filename){
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'csv':
                return \Maatwebsite\Excel\Excel::CSV;
            case 'xls':
                return \Maatwebsite\Excel\Excel::XLS;
            case 'xlsx':
            default:
                return \Maatwebsite\Excel\Excel::XLSX;
        }
    }
    
}
