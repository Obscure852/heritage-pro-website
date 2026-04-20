<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetLog;
use App\Models\Contact;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MaintenanceController extends Controller{
    public function index(Request $request){
        $maintenancesQuery = AssetMaintenance::with(['asset', 'vendor', 'performedByUser']);
        $selectedContactId = $this->normalizeSelectedContactId($request);
        
        if ($request->filled('status')) {
            $maintenancesQuery->where('status', $request->status);
        }
        
        if ($request->filled('asset_id')) {
            $maintenancesQuery->where('asset_id', $request->asset_id);
        }
        
        if ($selectedContactId) {
            $maintenancesQuery->where('contact_id', $selectedContactId);
        }
        
        if ($request->filled('maintenance_type')) {
            $maintenancesQuery->where('maintenance_type', $request->maintenance_type);
        }
        
        if ($request->filled('date_from')) {
            $maintenancesQuery->where('maintenance_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $maintenancesQuery->where('maintenance_date', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $maintenancesQuery->where('description', 'like', $search);
        }
        
        $maintenances = $maintenancesQuery->orderBy('maintenance_date', 'desc')->paginate(15)->withQueryString();
        $assets = Asset::orderBy('name')->get();
        $vendors = $this->maintenanceContacts();
        
        return view('assets.maintenance.index', compact('maintenances', 'assets', 'vendors'));
    }

    public function createAssetMaintenance($id){
        $asset = Asset::findOrFail($id);
        $vendors = $this->maintenanceContacts();
        return view('assets.maintenance.create-maintenance', compact('asset', 'vendors'));
    }

    public function createWithSelect(){
        $assets = Asset::whereNotIn('status', ['Disposed'])
            ->whereDoesntHave('maintenances', function($query) {
                $query->whereIn('status', ['Scheduled', 'In Progress']);
            })->with(['category'])->orderBy('name')->get();
            
        $vendors = $this->maintenanceContacts();
        return view('assets.maintenance.schedule-maintenance-select', compact('assets', 'vendors'));
    }

    public function createMaintenance(){
        $assets = Asset::whereNotIn('status', ['Disposed'])->orderBy('name')->get();
        $vendors = $this->maintenanceContacts();
        return view('assets.maintenance.create-maintenance', compact('assets', 'vendors'));
    }

    public function store(Request $request){
        $request->merge([
            'contact_id' => $this->normalizeSelectedContactId($request),
        ]);

        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type' => 'required|string|in:Preventive,Corrective,Upgrade',
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'contact_id' => $this->maintenanceContactValidationRule(),
            'cost' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'status' => 'required|string|in:Scheduled,In Progress,Completed,Cancelled',
        ]);

        DB::transaction(function () use ($validated) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);
            AssetMaintenance::query()->create($validated);

            $this->syncAssetStatusForMaintenanceCreate($asset, $validated['status'], $validated['maintenance_date']);

            $logDescription = 'Maintenance ' . strtolower($validated['status']) . ': ' . $validated['maintenance_type'];
            if ($validated['status'] === 'Scheduled') {
                $logDescription .= ' for ' . \Carbon\Carbon::parse($validated['maintenance_date'])->format('M d, Y');
            }

            AssetLog::createLog(
                $asset->id,
                'maintenance',
                $logDescription,
                [
                    'maintenance_type' => $validated['maintenance_type'],
                    'status' => $validated['status'],
                    'maintenance_date' => $validated['maintenance_date'],
                    'asset_status_changed' => $asset->wasChanged('status'),
                    'new_asset_status' => $asset->status,
                ],
                auth()->id()
            );
        }, 3);

        return redirect()->route('assets.maintenance.index')->with('message', 'Maintenance record created successfully');
    }

    public function edit($id){
        $maintenance = AssetMaintenance::findOrFail($id);
        $vendors = $this->maintenanceContacts();
        return view('assets.maintenance.edit-maintenance', compact('maintenance', 'vendors'));
    }

    public function update(Request $request, $id){
        $request->merge([
            'contact_id' => $this->normalizeSelectedContactId($request),
        ]);

        $validated = $request->validate([
            'maintenance_type' => 'required|string|in:Preventive,Corrective,Upgrade',
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'contact_id' => $this->maintenanceContactValidationRule(),
            'cost' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'status' => 'required|string|in:Scheduled,In Progress,Completed,Cancelled',
            'results' => 'nullable|string',
        ]);

        [$asset, $changes, $oldStatus, $newStatus, $assignedUser, $notificationDetails] = DB::transaction(function () use ($id, $validated) {
            $maintenance = AssetMaintenance::query()->lockForUpdate()->findOrFail($id);
            $asset = Asset::query()->lockForUpdate()->findOrFail($maintenance->asset_id);

            $oldStatus = $maintenance->status;
            $newStatus = $validated['status'];

            $changes = [];
            foreach ($validated as $key => $value) {
                if ($maintenance->{$key} != $value) {
                    $changes[$key] = [
                        'old' => $maintenance->{$key},
                        'new' => $value,
                    ];
                }
            }

            $maintenance->update($validated);
            $this->syncAssetStatusForMaintenanceUpdate($asset, $maintenance, $oldStatus, $newStatus);

            if (!empty($changes)) {
                AssetLog::createLog(
                    $asset->id,
                    'maintenance_update',
                    'Maintenance record updated',
                    $changes,
                    auth()->id()
                );
            }

            $assignedUser = null;
            $notificationDetails = null;
            if ($oldStatus !== $newStatus && $asset->currentAssignment && $asset->currentAssignment->assignable_type === 'App\\Models\\User') {
                $assignedUser = User::find($asset->currentAssignment->assignable_id);

                if ($assignedUser && $assignedUser->hasValidEmail()) {
                    $notificationDetails = [
                        'asset_name' => $asset->name,
                        'asset_code' => $asset->asset_code,
                        'maintenance_type' => $maintenance->maintenance_type,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'maintenance_date' => $maintenance->maintenance_date->format('M d, Y'),
                        'description' => $maintenance->description,
                        'results' => $maintenance->results,
                    ];
                }
            }

            return [$asset->fresh(), $changes, $oldStatus, $newStatus, $assignedUser, $notificationDetails];
        }, 3);

        if ($assignedUser && $notificationDetails) {
            if ($newStatus === 'In Progress') {
                $this->sendMaintenanceStartedEmail($assignedUser, $notificationDetails);
            } elseif ($newStatus === 'Completed') {
                $this->sendMaintenanceCompletedEmail($assignedUser, $notificationDetails);
            } elseif ($newStatus === 'Cancelled') {
                $this->sendMaintenanceCancelledEmail($assignedUser, $notificationDetails);
            }
        }

        return redirect()->back()->with('message', 'Maintenance record updated successfully');
    }

    public function completeForm($id){
        $maintenance = AssetMaintenance::with('asset')->findOrFail($id);
        
        if ($maintenance->status === 'Completed' || $maintenance->status === 'Cancelled') {
            return redirect()->back()->with('error', 'This maintenance record is already completed or cancelled.');
        }
        return view('assets.maintenance.complete-maintenance', compact('maintenance'));
    }


    public function complete(Request $request, $id){
        $validated = $request->validate([
            'results' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'next_maintenance_date' => 'nullable|date|after:today',
        ]);

        $blocked = false;
        DB::transaction(function () use ($id, $validated, &$blocked) {
            $maintenance = AssetMaintenance::query()->lockForUpdate()->findOrFail($id);
            $asset = Asset::query()->lockForUpdate()->findOrFail($maintenance->asset_id);

            if ($maintenance->status === 'Completed' || $maintenance->status === 'Cancelled') {
                $blocked = true;
                return;
            }

            $oldStatus = $maintenance->status;

            $maintenance->update([
                'status' => 'Completed',
                'results' => $validated['results'],
                'cost' => $validated['cost'] ?? $maintenance->cost,
                'next_maintenance_date' => $validated['next_maintenance_date'],
                'performed_by' => auth()->id(),
            ]);

            $this->setAssetAvailableWhenMaintenanceClears($asset, $maintenance->id);

            AssetLog::createLog(
                $asset->id,
                'maintenance_completed',
                'Maintenance marked as completed',
                [
                    'status' => [
                        'old' => $oldStatus,
                        'new' => 'Completed',
                    ],
                    'results' => $validated['results'],
                    'cost' => $validated['cost'] ?? $maintenance->cost,
                ],
                auth()->id()
            );
        }, 3);

        if ($blocked) {
            return redirect()->back()->with('error', 'This maintenance record is already completed or cancelled.');
        }
        
        return redirect()->route('assets.maintenance.index')->with('message', 'Maintenance has been marked as completed successfully');
    }

    public function cancel($id){
        $blocked = false;
        DB::transaction(function () use ($id, &$blocked) {
            $maintenance = AssetMaintenance::query()->lockForUpdate()->findOrFail($id);
            $asset = Asset::query()->lockForUpdate()->findOrFail($maintenance->asset_id);

            if ($maintenance->status === 'Completed' || $maintenance->status === 'Cancelled') {
                $blocked = true;
                return;
            }

            $oldStatus = $maintenance->status;
            $maintenance->update([
                'status' => 'Cancelled',
            ]);

            if ($oldStatus === 'In Progress') {
                $this->setAssetAvailableWhenMaintenanceClears($asset, $maintenance->id);
            }

            AssetLog::createLog(
                $asset->id,
                'maintenance_cancelled',
                'Maintenance cancelled',
                [
                    'status' => [
                        'old' => $oldStatus,
                        'new' => 'Cancelled',
                    ],
                ],
                auth()->id()
            );
        }, 3);

        if ($blocked) {
            return redirect()->back()->with('error', 'This maintenance record is already completed or cancelled.');
        }
        
        return redirect()->back()->with('message', 'Maintenance has been cancelled successfully');
    }

    public function destroy($id){
        DB::transaction(function () use ($id) {
            $maintenance = AssetMaintenance::query()->lockForUpdate()->findOrFail($id);
            $asset = Asset::query()->lockForUpdate()->findOrFail($maintenance->asset_id);

            if ($maintenance->status === 'In Progress') {
                $this->setAssetAvailableWhenMaintenanceClears($asset, $maintenance->id);
            }

            AssetLog::createLog(
                $asset->id,
                'maintenance_deleted',
                'Maintenance record deleted',
                [
                    'maintenance_type' => $maintenance->maintenance_type,
                    'date' => $maintenance->maintenance_date->format('Y-m-d'),
                    'status' => $maintenance->status,
                ],
                auth()->id()
            );

            $maintenance->forceDelete();
        }, 3);

        return redirect()->back()->with('message', 'Maintenance record has been deleted successfully');
    }
    
    public function assetHistory($assetId){
        $asset = Asset::findOrFail($assetId);
        $maintenances = AssetMaintenance::where('asset_id', $assetId)
            ->with(['vendor', 'performedByUser'])
            ->orderBy('maintenance_date', 'desc')
            ->get();
        
        return view('assets.maintenance.asset-maintenance-history', compact('asset', 'maintenances'));
    }
    
    public function scheduleReport(Request $request){
        $query = AssetMaintenance::with(['asset', 'vendor'])
            ->where('status', 'Scheduled')
            ->orderBy('maintenance_date');
        $selectedContactId = $this->normalizeSelectedContactId($request);
        
        if ($request->filled('date_from')) {
            $query->where('maintenance_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('maintenance_date', '<=', $request->date_to);
        }
        
        if ($selectedContactId) {
            $query->where('contact_id', $selectedContactId);
        }
        
        $scheduledMaintenances = $query->get();
        $vendors = $this->maintenanceContacts();
        
        return view('assets.maintenance.schedule-report', compact('scheduledMaintenances', 'vendors'));
    }
    

    public function costReport(Request $request){
        $query = AssetMaintenance::with(['asset', 'vendor'])->where('status', 'Completed')->whereNotNull('cost')->orderBy('maintenance_date', 'desc');
        $selectedContactId = $this->normalizeSelectedContactId($request);
        if ($request->filled('date_from')) {
            $query->where('maintenance_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('maintenance_date', '<=', $request->date_to);
        }
        
        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }
        
        if ($selectedContactId) {
            $query->where('contact_id', $selectedContactId);
        }
        
        $completedMaintenances = $query->get();
        $totalCost = $completedMaintenances->sum('cost');
        $assets = Asset::orderBy('name')->get();
        $vendors = $this->maintenanceContacts();
        
        return view('assets.maintenance.cost-report', compact('completedMaintenances', 'totalCost', 'assets', 'vendors'));
    }


    private function sendMaintenanceStartedEmail(User $user, array $details){
        $subject = "Maintenance Started: {$details['asset_name']}";
        
        $message = "Dear {$user->getFullNameAttribute()},\n\n";
        $message .= "We would like to inform you that the asset assigned to you is now under maintenance.\n\n";
        $message .= "Asset Details:\n";
        $message .= "- Name: {$details['asset_name']}\n";
        $message .= "- Asset Code: {$details['asset_code']}\n";
        $message .= "- Maintenance Type: {$details['maintenance_type']}\n";
        $message .= "- Maintenance Date: {$details['maintenance_date']}\n";
        $message .= "- Description: {$details['description']}\n\n";
        $message .= "During this maintenance period, the asset will be unavailable for use. ";
        $message .= "We will notify you once the maintenance is completed.\n\n";
        $message .= "If you have any questions or concerns, please contact the IT department.\n\n";
        $message .= "Regards,\nAsset Management Team";
        
        Mail::raw($message, function($mail) use ($user, $subject) {
            $mail->to($user->email)->subject($subject);
        });
    }

    private function sendMaintenanceCompletedEmail(User $user, array $details){
        $subject = "Maintenance Completed: {$details['asset_name']}";
        
        $message = "Dear {$user->getFullNameAttribute()},\n\n";
        $message .= "We are pleased to inform you that the maintenance for your assigned asset has been completed.\n\n";
        $message .= "Asset Details:\n";
        $message .= "- Name: {$details['asset_name']}\n";
        $message .= "- Asset Code: {$details['asset_code']}\n";
        $message .= "- Maintenance Type: {$details['maintenance_type']}\n";
        $message .= "- Maintenance Date: {$details['maintenance_date']}\n";
        
        if (isset($details['results']) && !empty($details['results'])) {
            $message .= "- Maintenance Results: {$details['results']}\n";
        }
        
        $message .= "\nThe asset is now available for use again. ";
        $message .= "If you notice any issues with the asset after maintenance, please report them to the IT department immediately.\n\n";
        $message .= "Regards,\nAsset Management Team";
        
        Mail::raw($message, function($mail) use ($user, $subject) {
            $mail->to($user->email)->subject($subject);
        });
    }

    private function sendMaintenanceCancelledEmail(User $user, array $details){
        $subject = "Maintenance Cancelled: {$details['asset_name']}";
        
        $message = "Dear {$user->getFullNameAttribute()},\n\n";
        $message .= "We would like to inform you that the scheduled maintenance for your assigned asset has been cancelled.\n\n";
        $message .= "Asset Details:\n";
        $message .= "- Name: {$details['asset_name']}\n";
        $message .= "- Asset Code: {$details['asset_code']}\n";
        $message .= "- Maintenance Type: {$details['maintenance_type']}\n";
        $message .= "- Originally Scheduled Date: {$details['maintenance_date']}\n\n";
        $message .= "If the asset was previously unavailable due to this maintenance, it should now be available for use again. ";
        $message .= "If you have any questions, please contact the IT department.\n\n";
        $message .= "Regards,\nAsset Management Team";
        
        Mail::raw($message, function($mail) use ($user, $subject) {
            $mail->to($user->email)->subject($subject);
        });
    }

    public function scheduledMaintenanceReport(Request $request){
        $query = AssetMaintenance::with([
            'asset.category',
            'asset.venue',
            'vendor',
            'performedByUser'
        ])->whereIn('status', ['Scheduled', 'In Progress', 'Completed', 'Cancelled']);
        $selectedContactId = $this->normalizeSelectedContactId($request);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('maintenance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('maintenance_date', '<=', $request->date_to);
        }

        if ($request->filled('maintenance_type')) {
            $query->where('maintenance_type', $request->maintenance_type);
        }

        if ($selectedContactId) {
            $query->where('contact_id', $selectedContactId);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('venue_id')) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('venue_id', $request->venue_id);
            });
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhereHas('asset', function($assetQuery) use ($search) {
                      $assetQuery->where('name', 'like', $search)
                                 ->orWhere('asset_code', 'like', $search);
                  });
            });
        }

        $maintenances = $query->orderBy('maintenance_date', 'asc')->orderBy('status', 'desc')->get();
        $maintenanceTypes = ['Corrective', 'Preventative', 'Upgrade'];

        $vendors = $this->maintenanceContacts();
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();

        $totalScheduled = $maintenances->where('status', 'Scheduled')->count();
        $totalInProgress = $maintenances->where('status', 'In Progress')->count();
        $totalCompleted = $maintenances->where('status', 'Completed')->count();
        $totalCancelled = $maintenances->where('status', 'Cancelled')->count();
        $overdueCount = $maintenances->filter(function($maintenance) {
            return $maintenance->maintenance_date < now() && $maintenance->status === 'Scheduled';
        })->count();
        $totalCost = $maintenances->sum('cost');

        $summary = [
            'total_scheduled' => $totalScheduled,
            'total_in_progress' => $totalInProgress,
            'total_completed' => $totalCompleted,
            'total_cancelled' => $totalCancelled,
            'overdue_count' => $overdueCount,
            'total_cost' => $totalCost,
            'total_records' => $maintenances->count()
        ];

        return view('assets.maintenance.reports.scheduled-maintenance', compact(
            'maintenances',
            'maintenanceTypes',
            'vendors',
            'categories',
            'venues',
            'summary'
        ));
    }

    public function maintenanceCostAnalysis(Request $request){
        $query = AssetMaintenance::with([
            'asset.category',
            'asset.venue',
            'vendor',
            'performedByUser'
        ]);
        $selectedContactId = $this->normalizeSelectedContactId($request);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('maintenance_type')) {
            $query->where('maintenance_type', $request->maintenance_type);
        }
        
        if ($request->filled('date_from')) {
            $query->where('maintenance_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('maintenance_date', '<=', $request->date_to);
        }
        
        if ($selectedContactId) {
            $query->where('contact_id', $selectedContactId);
        }
        
        if ($request->filled('category_id')) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        if ($request->filled('venue_id')) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('venue_id', $request->venue_id);
            });
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', $search)
                ->orWhereHas('asset', function($assetQuery) use ($search) {
                    $assetQuery->where('name', 'like', $search)
                                ->orWhere('asset_code', 'like', $search);
                });
            });
        }
        
        $maintenances = $query->orderBy('maintenance_date', 'asc')->get();
        $maintenanceTypes = AssetMaintenance::distinct()
                                        ->whereNotNull('maintenance_type')
                                        ->pluck('maintenance_type')
                                        ->toArray();
        
        $vendors = $this->maintenanceContacts();
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        
        $maintenancesWithCost = $maintenances->filter(function($m) {
            return !is_null($m->cost) && $m->cost > 0;
        });
        
        $totalCost = $maintenancesWithCost->sum('cost');
        $averageCost = $maintenancesWithCost->count() > 0 ? $maintenancesWithCost->avg('cost') : 0;

        $costByType = [];
        foreach($maintenanceTypes as $type) {
            $costByType[$type] = $maintenances->where('maintenance_type', $type)->sum('cost');
        }
        
        $totalRecords = $maintenances->count();
        
        $summary = [
            'total_cost' => $totalCost,
            'average_cost' => $averageCost,
            'cost_by_type' => $costByType,
            'total_records' => $totalRecords,
        ];
        
        return view('assets.maintenance.reports.maintenance-cost-analysis', compact(
            'maintenances',
            'maintenanceTypes',
            'vendors',
            'categories',
            'venues',
            'summary'
        ));
    }

    private function maintenanceContacts()
    {
        return Contact::query()
            ->eligibleForMaintenance()
            ->with(['primaryPerson', 'tags'])
            ->orderBy('name')
            ->get();
    }

    private function maintenanceContactValidationRule(): array
    {
        return [
            'nullable',
            \Illuminate\Validation\Rule::exists('contacts', 'id')->where(function ($query) {
                $query
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->whereExists(function ($tagQuery) {
                        $tagQuery->select(DB::raw(1))
                            ->from('contact_contact_tag')
                            ->join('contact_tags', 'contact_tags.id', '=', 'contact_contact_tag.contact_tag_id')
                            ->whereColumn('contact_contact_tag.contact_id', 'contacts.id')
                            ->where('contact_tags.is_active', true)
                            ->where('contact_tags.usable_in_maintenance', true);
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

    private function syncAssetStatusForMaintenanceCreate(Asset $asset, string $status, string $maintenanceDate): void
    {
        if ($status === 'In Progress') {
            $asset->update(['status' => 'In Maintenance']);
            return;
        }

        if ($status === 'Scheduled') {
            $maintenanceDate = \Carbon\Carbon::parse($maintenanceDate);
            $daysUntilMaintenance = $maintenanceDate->diffInDays(now(), false);

            if ($daysUntilMaintenance <= 1) {
                $asset->update(['status' => 'In Maintenance']);
            }
        }
    }

    private function syncAssetStatusForMaintenanceUpdate(Asset $asset, AssetMaintenance $maintenance, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            if ($newStatus === 'In Progress') {
                $asset->update(['status' => 'In Maintenance']);
            }

            if (in_array($newStatus, ['Completed', 'Cancelled'], true)) {
                $this->setAssetAvailableWhenMaintenanceClears($asset, $maintenance->id);
            }

            return;
        }

        if ($newStatus === 'In Progress') {
            $asset->update(['status' => 'In Maintenance']);
            return;
        }

        if ($newStatus === 'Scheduled') {
            $this->syncAssetStatusForMaintenanceCreate($asset, $newStatus, $maintenance->maintenance_date->format('Y-m-d'));
            return;
        }

        if (in_array($newStatus, ['Completed', 'Cancelled'], true)) {
            $this->setAssetAvailableWhenMaintenanceClears($asset, $maintenance->id);
        }
    }

    private function setAssetAvailableWhenMaintenanceClears(Asset $asset, int $maintenanceId): void
    {
        if ($asset->status !== 'In Maintenance') {
            return;
        }

        $ongoingMaintenanceCount = AssetMaintenance::query()
            ->where('asset_id', $asset->id)
            ->where('id', '!=', $maintenanceId)
            ->where('status', 'In Progress')
            ->lockForUpdate()
            ->count();

        if ($ongoingMaintenanceCount === 0) {
            $asset->update(['status' => 'Available']);
        }
    }

}
