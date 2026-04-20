<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Asset;
use App\Models\AssetAudit;
use App\Models\AssetAuditItem;
use App\Models\AssetCategory;
use App\Models\AssetLog;
use App\Models\AssetMaintenance;
use App\Models\SchoolSetup;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class AssetAuditController extends Controller{

    public function index(Request $request){
        $auditsQuery = AssetAudit::with(['conductedByUser'])
            ->withCount(['auditItems', 'auditItems as missing_count' => function($query) {
                $query->where('is_present', false);
            }, 'auditItems as maintenance_needed_count' => function($query) {
                $query->where('needs_maintenance', true);
            }]);
        
        if ($request->filled('status')) {
            $auditsQuery->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $auditsQuery->where('audit_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $auditsQuery->where('audit_date', '<=', $request->date_to);
        }
        
        if ($request->filled('conducted_by')) {
            $auditsQuery->where('conducted_by', $request->conducted_by);
        }
        
        $audits = $auditsQuery->orderBy('audit_date', 'desc')->paginate(15);
        $users = \App\Models\User::where('active', true)->orderBy('lastname')->get();
        return view('assets.audits.index', compact('audits', 'users'));
    }

    public function create(){
        $assets = Asset::whereNotIn('status', ['Disposed'])
            ->with(['category', 'venue'])
            ->orderBy('name')
            ->get();
            
        return view('assets.audits.create-audit', compact('assets'));
    }

    public function store(Request $request){
        $validated = $request->validate([
            'audit_date' => 'required|date',
            'next_audit_date' => 'nullable|date|after:audit_date',
            'notes' => 'nullable|string',
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id'
        ]);

        DB::beginTransaction();
        try {
            $auditCode = $this->generateUniqueAuditCode();
            $audit = AssetAudit::create([
                'audit_code' => $auditCode,
                'audit_date' => $validated['audit_date'],
                'next_audit_date' => $validated['next_audit_date'],
                'status' => 'Pending',
                'notes' => $validated['notes'],
                'conducted_by' => Auth::id()
            ]);

            foreach ($validated['asset_ids'] as $assetId) {
                AssetAuditItem::create([
                    'audit_id' => $audit->id,
                    'asset_id' => $assetId,
                    'is_present' => false,
                    'condition' => null,
                    'needs_maintenance' => false,
                    'notes' => null
                ]);
            }

            DB::commit();

            return redirect()->route('audits.show', $audit->id)
                ->with('message', 'Asset audit created successfully. You can now start the audit process.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create audit: ' . $e->getMessage());
        }
    }

    private function generateUniqueAuditCode(){
        $year = date('Y');
        $maxAttempts = 10;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $lastAudit = AssetAudit::where('audit_code', 'like', "AUD-{$year}-%")
                ->orderBy('audit_code', 'desc')
                ->first();
            
            if ($lastAudit) {
                $lastSequence = intval(substr($lastAudit->audit_code, -4));
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }
            
            $auditCode = 'AUD-' . $year . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            if (!AssetAudit::where('audit_code', $auditCode)->exists()) {
                return $auditCode;
            }
            
            usleep(100000);
        }
        return 'AUD-' . $year . '-' . time();
    }

    public function show($id){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'conductedByUser'
        ])->findOrFail($id);
        
        $totalAssets = $audit->auditItems->count();
        $presentAssets = $audit->auditItems->where('is_present', true)->count();
        $missingAssets = $audit->auditItems->where('is_present', false)->count();
        $maintenanceNeeded = $audit->auditItems->where('needs_maintenance', true)->count();
        $completionPercentage = $totalAssets > 0 ? round(($presentAssets + $missingAssets) / $totalAssets * 100, 1) : 0;
        
        return view('assets.audits.show-audit', compact(
            'audit', 
            'totalAssets', 
            'presentAssets', 
            'missingAssets', 
            'maintenanceNeeded',
            'completionPercentage'
        ));
    }

    public function start($id){
        $audit = AssetAudit::findOrFail($id);
        
        if ($audit->status !== 'Pending') {
            return redirect()->back()->with('error', 'This audit has already been started or completed.');
        }
        
        $audit->start();
        return redirect()->route('audits.conduct', $audit->id)->with('message', 'Audit started. You can now begin checking assets.');
    }

    public function conduct($id){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'conductedByUser'
        ])->findOrFail($id);
        
        if ($audit->status === 'Completed') {
            return redirect()->route('assets.audits.show', $audit->id)->with('error', 'This audit has already been completed.');
        }
        
        $currentItem = $audit->auditItems()->whereNull('condition')->with('asset.category')->first();
        $progress = $audit->auditItems->where('condition', '!=', null)->count();
        $total = $audit->auditItems->count();
        $progressPercentage = $total > 0 ? round($progress / $total * 100, 1) : 0;
        
        return view('assets.audits.conduct-audit', compact('audit', 'currentItem', 'progress', 'total', 'progressPercentage'));
    }

    public function verifyAsset(Request $request, $auditId, $itemId){
        $validated = $request->validate([
            'is_present' => 'required|boolean',
            'condition' => 'required_if:is_present,1|nullable|string|in:New,Good,Fair,Poor',
            'needs_maintenance' => 'nullable|boolean',
            'notes' => 'nullable|string'
        ]);
        
        $audit = AssetAudit::findOrFail($auditId);
        $auditItem = AssetAuditItem::where('audit_id', $auditId)->where('id', $itemId)->firstOrFail();
        
        if ($audit->status === 'Completed') {
            return redirect()->back()->with('error', 'This audit has already been completed.');
        }
        
        $auditItem->update([
            'is_present' => $validated['is_present'],
            'condition' => $validated['is_present'] ? $validated['condition'] : null,
            'needs_maintenance' => $validated['needs_maintenance'] ?? false,
            'notes' => $validated['notes']
        ]);
        
        if ($validated['is_present'] && $validated['condition']) {
            $asset = $auditItem->asset;
            if ($asset->condition !== $validated['condition']) {
                $oldCondition = $asset->condition;
                $asset->update(['condition' => $validated['condition']]);
                
                AssetLog::createLog(
                    $asset->id,
                    'audit_condition_update',
                    "Asset condition updated during audit from {$oldCondition} to {$validated['condition']}",
                    [
                        'audit_id' => $auditId,
                        'old_condition' => $oldCondition,
                        'new_condition' => $validated['condition']
                    ],
                    Auth::id()
                );
            }
        }
        
        if ($validated['needs_maintenance'] && $validated['is_present']) {
            $this->createMaintenanceRequest($auditItem, $validated['notes']);
        }
        
        $remainingItems = $audit->auditItems()->whereNull('condition')->count();
        
        if ($remainingItems === 0) {
            return redirect()->route('assets.audits.complete', $audit->id)
                ->with('message', 'All assets have been verified. Ready to complete the audit.');
        }
        
        return redirect()->route('assets.audits.conduct', $audit->id)
            ->with('message', 'Asset verified successfully.');
    }

    public function complete($id){
        try {
            $audit = AssetAudit::with(['auditItems.asset', 'conductedByUser'])->findOrFail($id);
            
            if ($audit->status === 'Completed') {
                return redirect()->back()->with('error', 'This audit has already been completed.');
            }
            
            if ($audit->status !== 'In Progress') {
                return redirect()->back()->with('error', 'Only audits in progress can be completed.');
            }
            
            $unprocessedItems = $audit->auditItems()->whereNull('condition')->count();
            if ($unprocessedItems > 0) {
                return redirect()->back()->with('error', "Cannot complete audit. There are {$unprocessedItems} unprocessed assets that need to be reviewed.");
            }
            
            $uncheckedItems = $audit->auditItems()->whereNull('is_present')->count();
            if ($uncheckedItems > 0) {
                return redirect()->back()->with('error', "Cannot complete audit. {$uncheckedItems} assets have not been marked as present or missing.");
            }
            
            foreach ($audit->auditItems as $auditItem) {
                if ($auditItem->asset) {
                    if ($auditItem->condition && $auditItem->asset->condition !== $auditItem->condition) {
                        $auditItem->asset->update(['condition' => $auditItem->condition]);
                    }
                    
                    if (!$auditItem->is_present) {
                        $auditItem->asset->update(['status' => 'Missing']);
                        AssetLog::createLog(
                            $auditItem->asset->id,
                            'audit_missing',
                            "Asset marked as missing during audit: {$audit->audit_code}",
                            [
                                'audit_id' => $audit->id,
                                'audit_code' => $audit->audit_code,
                                'notes' => $auditItem->notes
                            ],
                            auth()->id()
                        );
                    } elseif ($auditItem->needs_maintenance) {
                        $auditItem->asset->update(['status' => 'In Maintenance']);
                        AssetLog::createLog(
                            $auditItem->asset->id,
                            'audit_maintenance',
                            "Asset flagged for maintenance during audit: {$audit->audit_code}",
                            [
                                'audit_id' => $audit->id,
                                'audit_code' => $audit->audit_code,
                                'notes' => $auditItem->notes
                            ],
                            auth()->id()
                        );
                    } elseif ($auditItem->is_present && $auditItem->asset->status === 'Missing') {
                        $auditItem->asset->update(['status' => 'Available']);
                        AssetLog::createLog(
                            $auditItem->asset->id,
                            'audit_found',
                            "Previously missing asset found during audit: {$audit->audit_code}",
                            [
                                'audit_id' => $audit->id,
                                'audit_code' => $audit->audit_code,
                                'notes' => $auditItem->notes
                            ],
                            auth()->id()
                        );
                    }
                }
            }
            
            $audit->complete();
            $audit->update([
                'next_audit_date' => now()->addYear(),
                'notes' => ($audit->notes ? $audit->notes . "\n\n" : '') . "Completed on " . now()->format('Y-m-d H:i:s') . " by " . auth()->user()->name
            ]);
            
            $summary = $this->generateAuditSummary($audit);
            return redirect()->route('audits.show', $audit->id)
                ->with('message', 'Audit completed successfully! Asset statuses have been updated based on audit findings.')
                ->with('audit_summary', $summary);
                
        } catch (\Exception $e) {
            Log::error('Error completing audit: ' . $e->getMessage(), [
                'audit_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while completing the audit. Please try again or contact support.');
        }
    }

    public function cancelAudit($auditId){
        try {
            $audit = AssetAudit::findOrFail($auditId);
            
            if ($audit->status === 'Completed') {
                return redirect()->back()->with('error', 'Cannot cancel a completed audit');
            }

            $audit->update(['status' => 'Cancelled']);
            return redirect()->route('audits.index')->with('message', 'Audit cancelled successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error cancelling audit: ' . $e->getMessage());
        }
    }


    private function generateMissingAssetsSummary($audit){
        $missingAssets = $audit->auditItems;
        $totalMissingCount = $missingAssets->count();
        
        $totalMissingValue = $missingAssets->sum(function($item) {
            return $item->asset->current_value ?? $item->asset->purchase_price ?? 0;
        });
        
        $categoryBreakdown = [];
        $categoryGroups = $missingAssets->groupBy(function($item) {
            return $item->asset->category->name ?? 'Uncategorized';
        });
        
        foreach($categoryGroups as $category => $items) {
            $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $avgValue = $items->count() > 0 ? $categoryValue / $items->count() : 0;
            
            $categoryBreakdown[$category] = [
                'count' => $items->count(),
                'total_value' => $categoryValue,
                'average_value' => $avgValue,
                'impact_level' => $this->getImpactLevel($categoryValue),
                'impact_class' => $this->getImpactClass($categoryValue)
            ];
        }
        
        $locationBreakdown = [];
        $locationGroups = $missingAssets->groupBy(function($item) {
            return $item->asset->venue->name ?? 'Unknown Location';
        });
        
        foreach($locationGroups as $location => $items) {
            $locationValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            
            $locationBreakdown[$location] = [
                'count' => $items->count(),
                'total_value' => $locationValue,
                'security_level' => $this->getSecurityLevel($items->count()),
                'security_class' => $this->getSecurityClass($items->count()),
                'action_required' => $items->count() > 2 ? 'Security Review' : 'Investigation'
            ];
        }
        
        $priorityLevel = 'No Issues';
        $priorityClass = 'success';
        
        if ($totalMissingCount > 0) {
            if ($totalMissingCount <= 2) {
                $priorityLevel = 'Low Priority';
                $priorityClass = 'warning';
            } elseif ($totalMissingCount <= 5) {
                $priorityLevel = 'High Priority';
                $priorityClass = 'danger';
            } else {
                $priorityLevel = 'Critical';
                $priorityClass = 'dark';
            }
        }
        
        $totalAuditItems = $audit->auditItems()->count();
        $missingRate = $totalAuditItems > 0 ? round(($totalMissingCount / $totalAuditItems) * 100, 1) : 0;
        
        return [
            'total_missing_count' => $totalMissingCount,
            'total_missing_value' => $totalMissingValue,
            'categories_affected' => $categoryGroups->count(),
            'locations_affected' => $locationGroups->count(),
            'missing_rate' => $missingRate,
            'priority_level' => $priorityLevel,
            'priority_class' => $priorityClass,
            'insurance_claim_required' => $totalMissingValue > 1000,
            'investigation_required' => $totalMissingCount > 0,
            'security_review_urgency' => $totalMissingCount > 3 ? 'Urgent' : 'Standard',
            'category_breakdown' => $categoryBreakdown,
            'location_breakdown' => $locationBreakdown,
            'conducted_by' => $audit->conductedByUser->name ?? 'System',
            'audit_status' => $audit->status,
            'audit_status_class' => $audit->status === 'Completed' ? 'success' : 'warning'
        ];
    }

    public function missingAssetsReport($id){
        $audit = AssetAudit::with([
            'auditItems' => function($query) {
                $query->where('is_present', false)->with([
                    'asset.category',
                    'asset.venue'
                ]);
            },
            'conductedByUser'
        ])->findOrFail($id);
        $missingSummary = $this->generateMissingAssetsSummary($audit);
        $school_data = SchoolSetup::first();
        
        return view('assets.audits.missing-assets-report', compact('audit', 'missingSummary', 'school_data'));
    }

    public function maintenanceReport($id){ 
        $audit = AssetAudit::with([
            'auditItems' => function($query) {
                $query->where('needs_maintenance', true)->with([
                    'asset.category',
                    'asset.venue'
                ]);
            },
            'conductedByUser'
        ])->findOrFail($id);
        
        $maintenanceSummary = $this->generateMaintenanceSummary($audit);
        $school_data = SchoolSetup::first();
        return view('assets.audits.maintenance-report', compact('audit', 'maintenanceSummary', 'school_data'));
    }

    private function generateMaintenanceSummary($audit){
        $maintenanceAssets = $audit->auditItems;
        $totalMaintenanceCount = $maintenanceAssets->count();
        
        $totalMaintenanceValue = $maintenanceAssets->sum(function($item) {
            return $item->asset->current_value ?? $item->asset->purchase_price ?? 0;
        });
        
        $criticalCount = $maintenanceAssets->filter(function($item) {
            return ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Poor';
        })->count();
        
        $urgentCount = $maintenanceAssets->filter(function($item) {
            return ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Fair';
        })->count();
        
        $routineCount = $maintenanceAssets->filter(function($item) {
            $condition = $item->condition ?? $item->asset->condition ?? 'Unknown';
            return in_array($condition, ['Good', 'New']);
        })->count();
        
        $categoryBreakdown = [];
        $categoryGroups = $maintenanceAssets->groupBy(function($item) {
            return $item->asset->category->name ?? 'Uncategorized';
        });
        
        foreach($categoryGroups as $category => $items) {
            $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $categoryCritical = $items->filter(fn($item) => ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Poor')->count();
            $categoryUrgent = $items->filter(fn($item) => ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Fair')->count();
            $categoryRoutine = $items->filter(function($item) {
                $condition = $item->condition ?? $item->asset->condition ?? 'Unknown';
                return in_array($condition, ['Good', 'New']);
            })->count();
            
            $categoryBreakdown[$category] = [
                'count' => $items->count(),
                'total_value' => $categoryValue,
                'critical_count' => $categoryCritical,
                'urgent_count' => $categoryUrgent,
                'routine_count' => $categoryRoutine,
                'estimated_cost' => $categoryValue * 0.15
            ];
        }
        
        $locationBreakdown = [];
        $locationGroups = $maintenanceAssets->groupBy(function($item) {
            return $item->asset->venue->name ?? 'Unknown Location';
        });
        
        foreach($locationGroups as $location => $items) {
            $locationValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $hasCritical = $items->filter(fn($item) => ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Poor')->count() > 0;
            $hasUrgent = $items->filter(fn($item) => ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Fair')->count() > 0;
            
            $priorityLevel = $hasCritical ? 'Critical' : ($hasUrgent ? 'Urgent' : 'Routine');
            $priorityClass = $hasCritical ? 'danger' : ($hasUrgent ? 'warning' : 'info');
            
            $coordination = 'Standard scheduling';
            if ($items->count() > 3) {
                $coordination = 'Schedule coordination';
            } elseif ($hasCritical) {
                $coordination = 'Immediate access';
            }
            
            $locationBreakdown[$location] = [
                'count' => $items->count(),
                'total_value' => $locationValue,
                'priority_level' => $priorityLevel,
                'priority_class' => $priorityClass,
                'coordination_required' => $coordination
            ];
        }
        
        $budgetEstimations = [
            'routine_maintenance' => $totalMaintenanceValue * 0.05,
            'corrective_maintenance' => $totalMaintenanceValue * 0.15,
            'emergency_repairs' => $totalMaintenanceValue * 0.30,
            'recommended_budget' => $totalMaintenanceValue * 0.15
        ];
        
        $priorityBreakdown = [
            'critical' => [
                'count' => $criticalCount,
                'value' => $maintenanceAssets->filter(function($item) {
                    return ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Poor';
                })->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0),
                'timeframe' => 'Immediate (1-3 days)',
                'estimated_cost_rate' => 0.20
            ],
            'urgent' => [
                'count' => $urgentCount,
                'value' => $maintenanceAssets->filter(function($item) {
                    return ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Fair';
                })->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0),
                'timeframe' => '1-2 weeks',
                'estimated_cost_rate' => 0.15
            ],
            'routine' => [
                'count' => $routineCount,
                'value' => $maintenanceAssets->filter(function($item) {
                    $condition = $item->condition ?? $item->asset->condition ?? 'Unknown';
                    return in_array($condition, ['Good', 'New']);
                })->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0),
                'timeframe' => '1-3 months',
                'estimated_cost_rate' => 0.05
            ]
        ];
        
        foreach ($priorityBreakdown as $key => $priority) {
            $priorityBreakdown[$key]['estimated_cost'] = $priority['value'] * $priority['estimated_cost_rate'];
        }
        
        $totalAuditItems = $audit->auditItems()->count();
        $maintenanceRate = $totalAuditItems > 0 ? round(($totalMaintenanceCount / $totalAuditItems) * 100, 1) : 0;
        
        return [
            'total_maintenance_count' => $totalMaintenanceCount,
            'total_maintenance_value' => $totalMaintenanceValue,
            'categories_affected' => $categoryGroups->count(),
            'locations_affected' => $locationGroups->count(),
            'maintenance_rate' => $maintenanceRate,
            'critical_count' => $criticalCount,
            'urgent_count' => $urgentCount,
            'routine_count' => $routineCount,
            'priority_breakdown' => $priorityBreakdown,
            'category_breakdown' => $categoryBreakdown,
            'location_breakdown' => $locationBreakdown,
            'budget_estimations' => $budgetEstimations,
            'conducted_by' => $audit->conductedByUser->name ?? 'System',
            'audit_status' => $audit->status,
            'audit_status_class' => $audit->status === 'Completed' ? 'success' : 'warning'
        ];
    }

    private function getImpactLevel($value){
        if ($value > 5000) return 'Critical';
        if ($value > 2000) return 'High';
        if ($value > 500) return 'Medium';
        return 'Low';
    }
    private function getImpactClass($value){
        if ($value > 5000) return 'dark';
        if ($value > 2000) return 'danger';
        if ($value > 500) return 'warning';
        return 'info';
    }

    private function getSecurityLevel($count){
        if ($count > 3) return 'High Risk';
        if ($count > 1) return 'Medium Risk';
        return 'Low Risk';
    }

    private function getSecurityClass($count){
        if ($count > 3) return 'danger';
        if ($count > 1) return 'warning';
        return 'info';
    }

    public function edit($id){
        $audit = AssetAudit::with('auditItems.asset')->findOrFail($id);
        
        if ($audit->status === 'Completed') {
            return redirect()->back()->with('error', 'Cannot edit a completed audit.');
        }
        
        $assets = Asset::whereNotIn('status', ['Disposed'])
            ->with(['category', 'venue'])
            ->orderBy('name')
            ->get();
            
        return view('assets.audits.edit', compact('audit', 'assets'));
    }

    public function update(Request $request, $id){
        $audit = AssetAudit::findOrFail($id);
        
        if ($audit->status === 'Completed') {
            return redirect()->back()->with('error', 'Cannot edit a completed audit.');
        }
        
        $validated = $request->validate([
            'audit_date' => 'required|date',
            'next_audit_date' => 'nullable|date|after:audit_date',
            'notes' => 'nullable|string',
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            $audit->update([
                'audit_date' => $validated['audit_date'],
                'next_audit_date' => $validated['next_audit_date'],
                'notes' => $validated['notes']
            ]);
            
            $currentAssetIds = $audit->auditItems->pluck('asset_id')->toArray();
            $newAssetIds = $validated['asset_ids'];
            
            $assetsToRemove = array_diff($currentAssetIds, $newAssetIds);
            if (!empty($assetsToRemove)) {
                AssetAuditItem::where('audit_id', $audit->id)
                    ->whereIn('asset_id', $assetsToRemove)
                    ->whereNull('condition')
                    ->delete();
            }
            
            $assetsToAdd = array_diff($newAssetIds, $currentAssetIds);
            foreach ($assetsToAdd as $assetId) {
                AssetAuditItem::create([
                    'audit_id' => $audit->id,
                    'asset_id' => $assetId,
                    'is_present' => false,
                    'condition' => null,
                    'needs_maintenance' => false,
                    'notes' => null
                ]);
            }
            
            DB::commit();
            return redirect()->route('assets.audits.show', $audit->id)->with('message', 'Audit updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update audit: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $audit = AssetAudit::findOrFail($id);
        
        if ($audit->status === 'In Progress') {
            return redirect()->back()->with('error', 'Cannot delete an audit that is in progress.');
        }
        
        $auditCode = $audit->audit_code;
        $audit->delete();
        
        return redirect()->route('assets.audits.index')
            ->with('message', "Audit {$auditCode} has been deleted successfully.");
    }

    private function createMaintenanceRequest(AssetAuditItem $auditItem, $notes = null){
        $asset = $auditItem->asset;
        $existingMaintenance = AssetMaintenance::where('asset_id', $asset->id)
            ->whereIn('status', ['Scheduled', 'In Progress'])
            ->first();
            
        if (!$existingMaintenance) {
            AssetMaintenance::create([
                'asset_id' => $asset->id,
                'maintenance_type' => 'Corrective',
                'maintenance_date' => now()->addDays(7),
                'vendor_id' => null,
                'cost' => null,
                'description' => 'Maintenance required - identified during asset audit',
                'status' => 'Scheduled',
                'results' => null,
                'performed_by' => null
            ]);
            
            AssetLog::createLog(
                $asset->id,
                'maintenance_scheduled',
                'Maintenance scheduled based on audit findings',
                [
                    'audit_id' => $auditItem->audit_id,
                    'audit_notes' => $notes
                ],
                Auth::id()
            );
        }
    }

    private function generateAuditSummary($audit){
        $auditItems = $audit->auditItems;
        
        $totalAssets = $auditItems->count();
        $presentAssets = $auditItems->where('is_present', true)->count();
        $missingAssets = $auditItems->where('is_present', false)->count();
        $maintenanceNeeded = $auditItems->where('needs_maintenance', true)->count();

        $totalValue = $auditItems->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        $missingValue = $auditItems->where('is_present', false)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        $maintenanceValue = $auditItems->where('needs_maintenance', true)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        $assetsAtRiskValue = $missingValue + $maintenanceValue;
        
        $conditions = ['New', 'Good', 'Fair', 'Poor'];
        $conditionBreakdown = [];
        foreach($conditions as $condition) {
            $items = $auditItems->where('condition', $condition);
            $count = $items->count();
            $conditionBreakdown[strtolower($condition)] = [
                'count' => $count,
                'percentage' => $totalAssets > 0 ? round(($count / $totalAssets) * 100, 1) : 0,
                'value' => $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0)
            ];
        }
        
        $categoryBreakdown = [];
        $categoryGroups = $auditItems->groupBy(fn($item) => $item->asset->category->name ?? 'Uncategorized');
        foreach($categoryGroups as $category => $items) {
            $present = $items->where('is_present', true)->count();
            $missing = $items->where('is_present', false)->count();
            $maintenance = $items->where('needs_maintenance', true)->count();
            $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            
            $categoryBreakdown[$category] = [
                'total' => $items->count(),
                'present' => $present,
                'missing' => $missing,
                'maintenance' => $maintenance,
                'condition_rate' => $items->count() > 0 ? round(($present / $items->count()) * 100, 1) : 0,
                'value' => $categoryValue
            ];
        }
        
        $locationBreakdown = [];
        $locationGroups = $auditItems->groupBy(fn($item) => $item->asset->venue->name ?? 'Unknown Location');
        foreach($locationGroups as $location => $items) {
            $present = $items->where('is_present', true)->count();
            $missing = $items->where('is_present', false)->count();
            $maintenance = $items->where('needs_maintenance', true)->count();
            $locationValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            
            $locationBreakdown[$location] = [
                'total' => $items->count(),
                'present' => $present,
                'missing' => $missing,
                'maintenance' => $maintenance,
                'condition_rate' => $items->count() > 0 ? round(($present / $items->count()) * 100, 1) : 0,
                'value' => $locationValue
            ];
        }
        
        $assetRecoveryRate = $totalAssets > 0 ? round(($presentAssets / $totalAssets) * 100, 1) : 0;
        $assetsInGoodCondition = $totalAssets > 0 ? round(($auditItems->whereIn('condition', ['New', 'Good'])->count() / $totalAssets) * 100, 1) : 0;
        $maintenanceRequiredRate = $totalAssets > 0 ? round(($maintenanceNeeded / $totalAssets) * 100, 1) : 0;
        $valueRetentionRate = $totalValue > 0 ? round((($totalValue - $missingValue) / $totalValue) * 100, 1) : 0;
        
        $auditStatus = 'Excellent - No issues found';
        $auditStatusClass = 'success';
        
        if ($missingAssets > 0 || $maintenanceNeeded > 0) {
            if ($missingAssets == 0) {
                $auditStatus = 'Good - Minor maintenance needed';
                $auditStatusClass = 'info';
            } elseif ($missingAssets <= 2) {
                $auditStatus = 'Fair - Some issues require attention';
                $auditStatusClass = 'warning';
            } else {
                $auditStatus = 'Poor - Immediate action required';
                $auditStatusClass = 'danger';
            }
        }
        
        return [
            'total_assets' => $totalAssets,
            'present_assets' => $presentAssets,
            'missing_assets' => $missingAssets,
            'maintenance_needed' => $maintenanceNeeded,
            
            'present_percentage' => $totalAssets > 0 ? round(($presentAssets / $totalAssets) * 100, 1) : 0,
            'missing_percentage' => $totalAssets > 0 ? round(($missingAssets / $totalAssets) * 100, 1) : 0,
            'maintenance_percentage' => $totalAssets > 0 ? round(($maintenanceNeeded / $totalAssets) * 100, 1) : 0,
            
            'financial' => [
                'total_value' => $totalValue,
                'missing_value' => $missingValue,
                'maintenance_value' => $maintenanceValue,
                'assets_at_risk_value' => $assetsAtRiskValue
            ],
            
            'condition_breakdown' => $conditionBreakdown,
            'category_breakdown' => $categoryBreakdown,
            'location_breakdown' => $locationBreakdown,
            
            'kpis' => [
                'asset_recovery_rate' => $assetRecoveryRate,
                'assets_in_good_condition' => $assetsInGoodCondition,
                'maintenance_required_rate' => $maintenanceRequiredRate,
                'value_retention_rate' => $valueRetentionRate
            ],
            
            'audit_duration' => $audit->audit_date->diffInDays($audit->updated_at),
            'conducted_by' => $audit->conductedByUser->name ?? 'System',
            'completion_date' => now()->format('M d, Y H:i'),
            'completion_rate' => $totalAssets > 0 ? 
                round(($auditItems->whereNotNull('condition')->count() / $totalAssets) * 100, 1) : 0,
            'notes_count' => $auditItems->whereNotNull('notes')->count(),
            
            'audit_status' => $auditStatus,
            'audit_status_class' => $auditStatusClass,
            
            'immediate_actions' => [
                'missing_assets' => $missingAssets,
                'poor_condition' => $auditItems->where('condition', 'Poor')->count()
            ],
            'short_term_actions' => [
                'maintenance_needed' => $maintenanceNeeded,
                'fair_condition' => $auditItems->where('condition', 'Fair')->count()
            ]
        ];
    }

    public function showAuditSummary($auditId){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->findOrFail($auditId);
        
        $auditSummary = $this->generateAuditSummary($audit);
        $school_data = SchoolSetup::first();
        return view('assets.audits.audit-summary-report', compact('audit', 'auditSummary', 'school_data'));
    }
    
    public function getAuditSummaryData($auditId){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->findOrFail($auditId);
        return $this->generateAuditSummary($audit);
    }

    public function updateAuditItem(Request $request, $auditItemId){
        try {
            $auditItem = AssetAuditItem::findOrFail($auditItemId);
            
            if ($auditItem->audit->status === 'Completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update items in a completed audit'
                ], 400);
            }

            $validated = $request->validate([
                'is_present' => 'sometimes|boolean',
                'condition' => 'sometimes|nullable|string|in:New,Good,Fair,Poor',
                'needs_maintenance' => 'sometimes|boolean',
                'notes' => 'sometimes|nullable|string'
            ]);

            $auditItem->update($validated);
            AssetLog::createLog(
                $auditItem->asset_id,
                'audit_update',
                'Asset status updated during audit: ' . $auditItem->audit->audit_code,
                $validated,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Audit item updated successfully',
                'data' => $auditItem->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating audit item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProgress($auditId){
        try {
            $audit = AssetAudit::with('auditItems')->findOrFail($auditId);
            
            $total = $audit->auditItems->count();
            $checked = $audit->auditItems->whereNotNull('is_present')->count();
            $missing = $audit->auditItems->where('is_present', false)->count();
            $maintenance = $audit->auditItems->where('needs_maintenance', true)->count();
            $present = $audit->auditItems->where('is_present', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'checked' => $checked,
                    'missing' => $missing,
                    'maintenance' => $maintenance,
                    'present' => $present,
                    'progress_percentage' => $total > 0 ? round(($checked / $total) * 100, 1) : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting audit progress: ' . $e->getMessage()
            ], 500);
        }
    }

    public function conditionReport($id){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->findOrFail($id);
        
        $conditionSummary = $this->generateConditionSummary($audit);
        $school_data = SchoolSetup::first();
        return view('assets.audits.assets-condition-report', compact('audit', 'conditionSummary', 'school_data'));
    }

    public function locationReport($id){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->findOrFail($id);
        
        $locationSummary = $this->generateLocationSummary($audit);
        $school_data = SchoolSetup::first();
        
        return view('assets.audits.assets-location-report', compact('audit', 'locationSummary', 'school_data'));
    }

    public function financialReport($id){
        $audit = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->findOrFail($id);
        
        $financialSummary = $this->generateFinancialSummary($audit);
        $school_data = SchoolSetup::first();
        return view('assets.audits.financial-impact-report', compact('audit', 'financialSummary', 'school_data'));
    }

    private function generateConditionSummary($audit){
        $auditItems = $audit->auditItems;
        $totalAssets = $auditItems->count();

        $conditions = ['New', 'Good', 'Fair', 'Poor'];
        $conditionBreakdown = [];
        
        foreach($conditions as $condition) {
            $items = $auditItems->filter(function($item) use ($condition) {
                return ($item->condition ?? $item->asset->condition ?? 'Unknown') === $condition;
            });
            
            $count = $items->count();
            $percentage = $totalAssets > 0 ? round(($count / $totalAssets) * 100, 1) : 0;
            $value = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            
            $avgAge = $items->filter(function($item) {
                return $item->asset->purchase_date;
            })->avg(function($item) {
                return $item->asset->purchase_date->diffInMonths(now());
            }) ?? 0;
            
            $conditionBreakdown[strtolower($condition)] = [
                'count' => $count,
                'percentage' => $percentage,
                'value' => $value,
                'average_age_months' => round($avgAge, 1),
                'needs_attention' => in_array($condition, ['Fair', 'Poor']),
                'replacement_priority' => $condition === 'Poor' ? 'High' : ($condition === 'Fair' ? 'Medium' : 'Low')
            ];
        }
        
        $categoryConditionBreakdown = [];
        $categoryGroups = $auditItems->groupBy(function($item) {
            return $item->asset->category->name ?? 'Uncategorized';
        });
        
        foreach($categoryGroups as $category => $items) {
            $conditionCounts = [];
            foreach($conditions as $condition) {
                $conditionCounts[strtolower($condition)] = $items->filter(function($item) use ($condition) {
                    return ($item->condition ?? $item->asset->condition ?? 'Unknown') === $condition;
                })->count();
            }
            
            $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $poorCount = $conditionCounts['poor'];
            $fairCount = $conditionCounts['fair'];
            
            $categoryConditionBreakdown[$category] = [
                'total_count' => $items->count(),
                'total_value' => $categoryValue,
                'condition_counts' => $conditionCounts,
                'health_score' => $this->calculateHealthScore($conditionCounts, $items->count()),
                'replacement_needed' => $poorCount,
                'maintenance_needed' => $fairCount,
                'priority_level' => $poorCount > 0 ? 'High' : ($fairCount > 2 ? 'Medium' : 'Low')
            ];
        }
        $ageAnalysis = $this->analyzeAssetAges($auditItems);
        $replacementPlanning = $this->generateReplacementPlan($auditItems);
        $healthMetrics = [
            'overall_health_score' => $this->calculateOverallHealthScore($conditionBreakdown, $totalAssets),
            'assets_needing_replacement' => $conditionBreakdown['poor']['count'],
            'assets_needing_maintenance' => $conditionBreakdown['fair']['count'],
            'assets_in_good_condition' => $conditionBreakdown['new']['count'] + $conditionBreakdown['good']['count'],
            'condition_trend' => $this->determineConditionTrend($conditionBreakdown),
            'estimated_replacement_cost' => $conditionBreakdown['poor']['value'],
            'estimated_maintenance_cost' => $conditionBreakdown['fair']['value'] * 0.15
        ];
        
        return [
            'total_assets' => $totalAssets,
            'condition_breakdown' => $conditionBreakdown,
            'category_condition_breakdown' => $categoryConditionBreakdown,
            'age_analysis' => $ageAnalysis,
            'replacement_planning' => $replacementPlanning,
            'health_metrics' => $healthMetrics,
            'conducted_by' => $audit->conductedByUser->full_name ?? 'System',
            'audit_status' => $audit->status,
            'audit_status_class' => $audit->status === 'Completed' ? 'success' : 'warning'
        ];
    }

    private function generateLocationSummary($audit){
        $auditItems = $audit->auditItems;
        $totalAssets = $auditItems->count();
        
        $locationBreakdown = [];
        $locationGroups = $auditItems->groupBy(function($item) {
            return $item->asset->venue->name ?? 'Unknown Location';
        });
        
        foreach($locationGroups as $location => $items) {
            $presentCount = $items->where('is_present', true)->count();
            $missingCount = $items->where('is_present', false)->count();
            $maintenanceCount = $items->where('needs_maintenance', true)->count();
            
            $totalValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $missingValue = $items->where('is_present', false)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            
            $performanceScore = $items->count() > 0 ? round(($presentCount / $items->count()) * 100, 1) : 0;
            $securityRisk = $this->calculateSecurityRisk($missingCount, $items->count(), $missingValue);
            
            $assetDensity = $items->count();
            $utilizationRate = $performanceScore;
            
            $locationBreakdown[$location] = [
                'total_assets' => $items->count(),
                'present_assets' => $presentCount,
                'missing_assets' => $missingCount,
                'maintenance_needed' => $maintenanceCount,
                'total_value' => $totalValue,
                'missing_value' => $missingValue,
                'performance_score' => $performanceScore,
                'security_risk' => $securityRisk,
                'asset_density' => $assetDensity,
                'utilization_rate' => $utilizationRate,
                'issues_count' => $missingCount + $maintenanceCount,
                'priority_level' => $this->getLocationPriority($missingCount, $maintenanceCount, $missingValue)
            ];
        }
        
        $securityAnalysis = [
            'high_risk_locations' => count(array_filter($locationBreakdown, fn($loc) => $loc['security_risk']['level'] === 'High')),
            'total_missing_value' => array_sum(array_column($locationBreakdown, 'missing_value')),
            'locations_with_issues' => count(array_filter($locationBreakdown, fn($loc) => $loc['issues_count'] > 0)),
            'best_performing_location' => $this->getBestPerformingLocation($locationBreakdown),
            'worst_performing_location' => $this->getWorstPerformingLocation($locationBreakdown)
        ];

        $categoryLocationMatrix = $this->generateCategoryLocationMatrix($auditItems);
        $locationRecommendations = $this->generateLocationRecommendations($locationBreakdown);
        
        return [
            'total_assets' => $totalAssets,
            'total_locations' => count($locationBreakdown),
            'location_breakdown' => $locationBreakdown,
            'security_analysis' => $securityAnalysis,
            'category_location_matrix' => $categoryLocationMatrix,
            'location_recommendations' => $locationRecommendations,
            'conducted_by' => $audit->conductedByUser->full_name ?? 'System',
            'audit_status' => $audit->status,
            'audit_status_class' => $audit->status === 'Completed' ? 'success' : 'warning'
        ];
    }

    private function generateFinancialSummary($audit){
        $auditItems = $audit->auditItems;
        $totalAssets = $auditItems->count();
        
        $totalValue = $auditItems->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        $missingValue = $auditItems->where('is_present', false)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        $maintenanceValue = $auditItems->where('needs_maintenance', true)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        
        $financialMetrics = [
            'total_asset_value' => $totalValue,
            'missing_asset_value' => $missingValue,
            'assets_needing_maintenance_value' => $maintenanceValue,
            'value_at_risk' => $missingValue + ($maintenanceValue * 0.1),
            'value_retention_rate' => $totalValue > 0 ? round((($totalValue - $missingValue) / $totalValue) * 100, 1) : 0,
            'financial_health_score' => $this->calculateFinancialHealthScore($totalValue, $missingValue, $maintenanceValue)
        ];
        
        $costAnalysis = [
            'immediate_replacement_cost' => $missingValue,
            'estimated_maintenance_cost' => $maintenanceValue * 0.15,
            'insurance_claim_potential' => $missingValue * 0.8,
            'depreciation_impact' => $this->calculateDepreciationImpact($auditItems),
            'total_financial_exposure' => $missingValue + ($maintenanceValue * 0.15)
        ];
        
        $budgetImpact = [
            'immediate_budget_need' => $missingValue * 0.5,
            'quarterly_budget_impact' => $costAnalysis['estimated_maintenance_cost'],
            'annual_budget_projection' => $this->calculateAnnualBudgetProjection($totalValue, $missingValue, $maintenanceValue),
            'cost_avoidance_opportunity' => $maintenanceValue * 0.3
        ];
        
        $categoryFinancialBreakdown = [];
        $categoryGroups = $auditItems->groupBy(function($item) {
            return $item->asset->category->name ?? 'Uncategorized';
        });
        
        foreach($categoryGroups as $category => $items) {
            $categoryTotal = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $categoryMissing = $items->where('is_present', false)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            $categoryMaintenance = $items->where('needs_maintenance', true)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            
            $categoryFinancialBreakdown[$category] = [
                'total_value' => $categoryTotal,
                'missing_value' => $categoryMissing,
                'maintenance_value' => $categoryMaintenance,
                'value_at_risk' => $categoryMissing + ($categoryMaintenance * 0.1),
                'risk_percentage' => $categoryTotal > 0 ? round((($categoryMissing + $categoryMaintenance) / $categoryTotal) * 100, 1) : 0,
                'financial_priority' => $this->getFinancialPriority($categoryMissing, $categoryMaintenance, $categoryTotal)
            ];
        }
        
        $roiAnalysis = [
            'asset_management_roi' => $this->calculateAssetManagementROI($totalValue, $missingValue),
            'preventive_maintenance_roi' => $this->calculatePreventiveMaintenanceROI($maintenanceValue),
            'audit_program_value' => $missingValue > 0 ? $missingValue * 0.1 : $totalValue * 0.005,
        ];
        
        return [
            'total_assets' => $totalAssets,
            'financial_metrics' => $financialMetrics,
            'cost_analysis' => $costAnalysis,
            'budget_impact' => $budgetImpact,
            'category_financial_breakdown' => $categoryFinancialBreakdown,
            'roi_analysis' => $roiAnalysis,
            'conducted_by' => $audit->conductedByUser->full_name ?? 'System',
            'audit_status' => $audit->status,
            'audit_status_class' => $audit->status === 'Completed' ? 'success' : 'warning'
        ];
    }

    private function calculateHealthScore($conditionCounts, $totalCount){
        if ($totalCount == 0) return 0;
    
        $score = (
            ($conditionCounts['new'] * 100) +
            ($conditionCounts['good'] * 75) +
            ($conditionCounts['fair'] * 50) +
            ($conditionCounts['poor'] * 25)
        ) / $totalCount;
    
    return round($score, 1);
}

    private function calculateOverallHealthScore($conditionBreakdown, $totalAssets){
        if ($totalAssets == 0) return 0;
        
        $score = (
            ($conditionBreakdown['new']['count'] * 100) +
            ($conditionBreakdown['good']['count'] * 75) +
            ($conditionBreakdown['fair']['count'] * 50) +
            ($conditionBreakdown['poor']['count'] * 25)
        ) / $totalAssets;
        return round($score, 1);
    }

    private function determineConditionTrend($conditionBreakdown){
        $poorPercentage = $conditionBreakdown['poor']['percentage'];
        $fairPercentage = $conditionBreakdown['fair']['percentage'];
        
        if ($poorPercentage > 15) return 'Declining';
        if ($fairPercentage > 30) return 'Concerning';
        if ($conditionBreakdown['new']['percentage'] + $conditionBreakdown['good']['percentage'] > 70) return 'Excellent';
        return 'Stable';
    }

private function analyzeAssetAges($auditItems){
    $assetsWithDates = $auditItems->filter(function($item) {
        return $item->asset->purchase_date;
    });
        
    if ($assetsWithDates->isEmpty()) {
            return ['average_age' => 0, 'age_ranges' => []];
        }
        
        $ages = $assetsWithDates->map(function($item) {
            return $item->asset->purchase_date->diffInMonths(now());
        });
        
        $ageRanges = [
            '0-12_months' => $ages->filter(fn($age) => $age <= 12)->count(),
            '1-3_years' => $ages->filter(fn($age) => $age > 12 && $age <= 36)->count(),
            '3-5_years' => $ages->filter(fn($age) => $age > 36 && $age <= 60)->count(),
            '5_plus_years' => $ages->filter(fn($age) => $age > 60)->count()
        ];
        
        return [
            'average_age' => round($ages->avg(), 1),
            'age_ranges' => $ageRanges
        ];
    }

    private function generateReplacementPlan($auditItems){
        $poorConditionAssets = $auditItems->filter(function($item) {
            return ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Poor';
        });
        
        $fairConditionAssets = $auditItems->filter(function($item) {
            return ($item->condition ?? $item->asset->condition ?? 'Unknown') === 'Fair';
        });
        
        return [
            'immediate_replacement' => [
                'count' => $poorConditionAssets->count(),
                'value' => $poorConditionAssets->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0)
            ],
            'planned_replacement' => [
                'count' => $fairConditionAssets->count(),
                'value' => $fairConditionAssets->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0)
            ]
        ];
    }

    private function calculateSecurityRisk($missingCount, $totalCount, $missingValue){
        $missingRate = $totalCount > 0 ? ($missingCount / $totalCount) * 100 : 0;
        
        if ($missingRate > 10 || $missingValue > 10000) {
            return ['level' => 'High', 'score' => 'Critical', 'class' => 'danger'];
        } elseif ($missingRate > 5 || $missingValue > 5000) {
            return ['level' => 'Medium', 'score' => 'Moderate', 'class' => 'warning'];
        } else {
            return ['level' => 'Low', 'score' => 'Acceptable', 'class' => 'success'];
        }
    }

    private function getLocationPriority($missingCount, $maintenanceCount, $missingValue){
        $totalIssues = $missingCount + $maintenanceCount;
        
        if ($missingCount > 2 || $missingValue > 10000) return 'Critical';
        if ($totalIssues > 3 || $missingValue > 5000) return 'High';
        if ($totalIssues > 1) return 'Medium';
        return 'Low';
    }

    private function getBestPerformingLocation($locationBreakdown){
        return collect($locationBreakdown)->sortByDesc('performance_score')->keys()->first() ?? 'N/A';
    }

    private function getWorstPerformingLocation($locationBreakdown){
        return collect($locationBreakdown)->sortBy('performance_score')->keys()->first() ?? 'N/A';
    }

    private function generateCategoryLocationMatrix($auditItems){
        $matrix = [];
        $locationGroups = $auditItems->groupBy(function($item) {
            return $item->asset->venue->name ?? 'Unknown Location';
        });
        
        foreach($locationGroups as $location => $items) {
            $categoryGroups = $items->groupBy(function($item) {
                return $item->asset->category->name ?? 'Uncategorized';
            });
            
            $matrix[$location] = $categoryGroups->map(function($categoryItems) {
                return [
                    'count' => $categoryItems->count(),
                    'missing' => $categoryItems->where('is_present', false)->count(),
                    'maintenance' => $categoryItems->where('needs_maintenance', true)->count()
                ];
            })->toArray();
        }
        
        return $matrix;
    }

    private function generateLocationRecommendations($locationBreakdown){
        $recommendations = [];
        
        foreach($locationBreakdown as $location => $data) {
            $locationRecs = [];
            
            if ($data['missing_assets'] > 0) {
                $locationRecs[] = "Investigate {$data['missing_assets']} missing assets";
                if ($data['missing_assets'] > 2) {
                    $locationRecs[] = "Conduct security review";
                }
            }
            
            if ($data['maintenance_needed'] > 0) {
                $locationRecs[] = "Schedule maintenance for {$data['maintenance_needed']} assets";
            }
            
            if ($data['performance_score'] < 80) {
                $locationRecs[] = "Improve asset management procedures";
            }
            
            if ($data['security_risk']['level'] === 'High') {
                $locationRecs[] = "Enhance security measures";
            }
            
            if (empty($locationRecs)) {
                $locationRecs[] = "Maintain current good practices";
            }
            
            $recommendations[$location] = $locationRecs;
        }
        
        return $recommendations;
    }

    private function calculateFinancialHealthScore($totalValue, $missingValue, $maintenanceValue){
        if ($totalValue == 0) return 0;
        
        $atRiskValue = $missingValue + ($maintenanceValue * 0.1);
        $healthPercentage = (($totalValue - $atRiskValue) / $totalValue) * 100;
        
        return round($healthPercentage, 1);
    }

    private function calculateDepreciationImpact($auditItems){
        $totalOriginalValue = $auditItems->sum(fn($item) => $item->asset->purchase_price ?? 0);
        $totalCurrentValue = $auditItems->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
        
        return $totalOriginalValue - $totalCurrentValue;
    }

    private function calculateAnnualBudgetProjection($totalValue, $missingValue, $maintenanceValue){
        $annualMaintenance = $totalValue * 0.075;
        $replacementBudget = $missingValue * 0.5;
        $additionalMaintenance = $maintenanceValue * 0.15;
        
        return $annualMaintenance + $replacementBudget + $additionalMaintenance;
    }

    private function getFinancialPriority($missingValue, $maintenanceValue, $totalValue){
        $riskPercentage = $totalValue > 0 ? (($missingValue + $maintenanceValue) / $totalValue) * 100 : 0;
        
        if ($riskPercentage > 25 || $missingValue > 20000) return 'Critical';
        if ($riskPercentage > 15 || $missingValue > 10000) return 'High';
        if ($riskPercentage > 5 || $missingValue > 2000) return 'Medium';
        return 'Low';
    }

    private function calculateAssetManagementROI($totalValue, $missingValue){
        $programCost = $totalValue * 0.005;
        $valueProtected = $totalValue - $missingValue;
        return $programCost > 0 ? round((($valueProtected - $programCost) / $programCost) * 100, 1) : 0;
    }

    private function calculatePreventiveMaintenanceROI($maintenanceValue){
        $preventiveCost = $maintenanceValue * 0.15;
        $emergencyRepairSavings = $maintenanceValue * 0.30;
        
        return $preventiveCost > 0 ? round((($emergencyRepairSavings - $preventiveCost) / $preventiveCost) * 100, 1) : 0;
    }

    public function trendAnalysis(Request $request){
        $startDate = $request->input('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $categoryId = $request->input('category_id');
        $venueId = $request->input('venue_id');
        
        $auditsQuery = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->whereBetween('audit_date', [$startDate, $endDate])->orderBy('audit_date', 'asc');
        
        if ($categoryId) {
            $auditsQuery->whereHas('auditItems.asset', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        if ($venueId) {
            $auditsQuery->whereHas('auditItems.asset', function($query) use ($venueId) {
                $query->where('venue_id', $venueId);
            });
        }
        
        $audits = $auditsQuery->get();
        $trendAnalysis = $this->generateTrendAnalysis($audits, $startDate, $endDate);
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        $school_data = SchoolSetup::first();
        
        return view('assets.audits.trends-analysis-report', compact(
            'audits', 'trendAnalysis', 'categories', 'venues', 'school_data', 'startDate', 'endDate'
        ));
    }

    public function comparisonReport(Request $request){
        $auditIds = $request->input('audit_ids', []);
        $comparisonFocus = $request->input('focus', 'overall');
        
        if (empty($auditIds)) {
            $auditIds = AssetAudit::where('status', 'Completed')
                ->orderBy('audit_date', 'desc')
                ->limit(3)
                ->pluck('id')
                ->toArray();
        }
        
        $auditIds = array_slice($auditIds, 0, 5);
        $audits = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->whereIn('id', $auditIds)->orderBy('audit_date', 'desc')->get();
        
        if ($audits->count() < 2) {
            return redirect()->back()->with('error', 'At least 2 audits are required for comparison.');
        }

        $comparisonAnalysis = $this->generateComparisonAnalysis($audits, $comparisonFocus);
        $allAudits = AssetAudit::orderBy('audit_date', 'desc')->get();
        $school_data = SchoolSetup::first();
        
        return view('assets.audits.comparison-report', compact(
            'audits', 'comparisonAnalysis', 'allAudits', 'comparisonFocus', 'school_data'
        ));
    }

    public function performanceDashboard(Request $request){
        $period = $request->input('period', '6_months');
        $categoryId = $request->input('category_id');
        $venueId = $request->input('venue_id');
        
        $dateRange = $this->calculateDateRange($period);
        $auditsQuery = AssetAudit::with([
            'auditItems.asset.category',
            'auditItems.asset.venue',
            'conductedByUser'
        ])->whereBetween('audit_date', [$dateRange['start'], $dateRange['end']])
        ->orderBy('audit_date', 'desc');
        
        if ($categoryId) {
            $auditsQuery->whereHas('auditItems.asset', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        if ($venueId) {
            $auditsQuery->whereHas('auditItems.asset', function($query) use ($venueId) {
                $query->where('venue_id', $venueId);
            });
        }
        
        $audits = $auditsQuery->get();
        $dashboardData = $this->generatePerformanceDashboard($audits, $period);
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        $school_data = SchoolSetup::first();
        
        return view('assets.audits.performance-dashboard-report', compact(
            'audits', 'dashboardData', 'categories', 'venues', 'school_data', 'period'
        ));
    }


    private function generateTrendAnalysis($audits, $startDate, $endDate){
        if ($audits->isEmpty()) {
            return $this->getEmptyTrendAnalysis();
        }

        $timeSeriesData = [];
        $monthlyData = [];
        
        foreach ($audits as $audit) {
            $auditMonth = $audit->audit_date->format('Y-m');
            $auditData = $this->generateAuditSummary($audit);
            
            $timeSeriesData[] = [
                'audit_id' => $audit->id,
                'audit_code' => $audit->audit_code,
                'audit_date' => $audit->audit_date->format('Y-m-d'),
                'month' => $auditMonth,
                'total_assets' => $auditData['total_assets'],
                'present_assets' => $auditData['present_assets'],
                'missing_assets' => $auditData['missing_assets'],
                'maintenance_needed' => $auditData['maintenance_needed'],
                'present_percentage' => $auditData['present_percentage'],
                'missing_percentage' => $auditData['missing_percentage'],
                'financial_total_value' => $auditData['financial']['total_value'],
                'financial_missing_value' => $auditData['financial']['missing_value'],
                'health_score' => $this->calculateAuditHealthScore($auditData)
            ];
            
            if (!isset($monthlyData[$auditMonth])) {
                $monthlyData[$auditMonth] = [
                    'month' => $auditMonth,
                    'audit_count' => 0,
                    'total_assets' => 0,
                    'present_assets' => 0,
                    'missing_assets' => 0,
                    'maintenance_needed' => 0,
                    'total_value' => 0,
                    'missing_value' => 0
                ];
            }
            
            $monthlyData[$auditMonth]['audit_count']++;
            $monthlyData[$auditMonth]['total_assets'] += $auditData['total_assets'];
            $monthlyData[$auditMonth]['present_assets'] += $auditData['present_assets'];
            $monthlyData[$auditMonth]['missing_assets'] += $auditData['missing_assets'];
            $monthlyData[$auditMonth]['maintenance_needed'] += $auditData['maintenance_needed'];
            $monthlyData[$auditMonth]['total_value'] += $auditData['financial']['total_value'];
            $monthlyData[$auditMonth]['missing_value'] += $auditData['financial']['missing_value'];
        }

        $trendMetrics = $this->calculateTrendMetrics($timeSeriesData);
        $seasonalPatterns = $this->identifySeasonalPatterns($monthlyData);
        $predictiveInsights = $this->generatePredictiveInsights($timeSeriesData);
        
        $performanceTrends = [
            'asset_recovery_trend' => $this->calculateTrend($timeSeriesData, 'present_percentage'),
            'missing_assets_trend' => $this->calculateTrend($timeSeriesData, 'missing_percentage'),
            'maintenance_trend' => $this->calculateTrend($timeSeriesData, 'maintenance_needed'),
            'financial_health_trend' => $this->calculateTrend($timeSeriesData, 'health_score')
        ];
        
        $categoryTrends = $this->analyzeCategoryTrends($audits);
        $locationTrends = $this->analyzeLocationTrends($audits);
        
        return [
            'time_series_data' => $timeSeriesData,
            'monthly_data' => array_values($monthlyData),
            'trend_metrics' => $trendMetrics,
            'performance_trends' => $performanceTrends,
            'seasonal_patterns' => $seasonalPatterns,
            'predictive_insights' => $predictiveInsights,
            'category_trends' => $categoryTrends,
            'location_trends' => $locationTrends,
            'date_range' => ['start' => $startDate, 'end' => $endDate],
            'total_audits' => $audits->count(),
            'analysis_period' => $this->calculateAnalysisPeriod($startDate, $endDate)
        ];
    }

    private function generateComparisonAnalysis($audits, $focus){
        $comparisonData = [];
        $auditSummaries = [];
        
        foreach ($audits as $audit) {
            $summary = $this->generateAuditSummary($audit);
            $auditSummaries[$audit->id] = $summary;
            
            $comparisonData[] = [
                'audit_id' => $audit->id,
                'audit_code' => $audit->audit_code,
                'audit_date' => $audit->audit_date,
                'conducted_by' => $audit->conductedByUser->name ?? 'System',
                'status' => $audit->status,
                'summary' => $summary
            ];
        }
        
        $comparisonMetrics = $this->generateComparisonMetrics($auditSummaries, $focus);
        $performanceRanking = $this->rankAuditPerformance($auditSummaries);
        $comparativeInsights = $this->generateComparativeInsights($auditSummaries, $focus);
        $progressAnalysis = $this->analyzeProgressBetweenAudits($comparisonData);
        
        return [
            'comparison_data' => $comparisonData,
            'comparison_metrics' => $comparisonMetrics,
            'performance_ranking' => $performanceRanking,
            'comparative_insights' => $comparativeInsights,
            'progress_analysis' => $progressAnalysis,
            'focus_area' => $focus,
            'audit_count' => count($comparisonData),
            'comparison_summary' => $this->generateComparisonSummary($auditSummaries)
        ];
    }

    private function generatePerformanceDashboard($audits, $period){
        if ($audits->isEmpty()) {
            return $this->getEmptyDashboardData();
        }
        
        $kpis = $this->calculateDashboardKPIs($audits);
        $performanceMetrics = [];
        foreach ($audits as $audit) {
            $summary = $this->generateAuditSummary($audit);
            $performanceMetrics[] = [
                'date' => $audit->audit_date->format('Y-m-d'),
                'health_score' => $this->calculateAuditHealthScore($summary),
                'recovery_rate' => $summary['present_percentage'],
                'missing_rate' => $summary['missing_percentage'],
                'maintenance_rate' => $summary['maintenance_percentage'],
                'financial_health' => $this->calculateFinancialHealthScore(
                    $summary['financial']['total_value'],
                    $summary['financial']['missing_value'],
                    $summary['financial']['maintenance_value']
                )
            ];
        }
        
        $alerts = $this->generateDashboardAlerts($audits);
        $categoryPerformance = $this->analyzeCategoryPerformance($audits);
        $locationPerformance = $this->analyzeLocationPerformance($audits);
        $trendIndicators = $this->calculateTrendIndicators($performanceMetrics);
        $executiveSummary = $this->generateExecutiveSummary($audits, $kpis);
        
        return [
            'kpis' => $kpis,
            'performance_metrics' => $performanceMetrics,
            'alerts' => $alerts,
            'category_performance' => $categoryPerformance,
            'location_performance' => $locationPerformance,
            'trend_indicators' => $trendIndicators,
            'executive_summary' => $executiveSummary,
            'period' => $period,
            'total_audits' => $audits->count(),
            'latest_audit' => $audits->first(),
            'dashboard_health_score' => $this->calculateOverallDashboardHealth($kpis)
        ];
    }

    private function calculateDateRange($period){   
            $end = now();
        
            switch ($period) {
                case '3_months':
                    $start = now()->subMonths(3);
                break;
            case '6_months':
                $start = now()->subMonths(6);
                break;
            case '1_year':
                $start = now()->subYear();
                break;
            case 'all_time':
                $start = AssetAudit::min('audit_date') ?? now()->subYear();
                break;
            default:
                $start = now()->subMonths(6);
        }
        
        return ['start' => $start, 'end' => $end];
    }

    private function calculateAuditHealthScore($auditSummary){
        $presentPercentage = $auditSummary['present_percentage'];
        $maintenancePercentage = $auditSummary['maintenance_percentage'] ?? 0;
        $healthScore = ($presentPercentage * 0.7) + ((100 - $maintenancePercentage) * 0.3);
        
        return round($healthScore, 1);
    }

    private function calculateTrendMetrics($timeSeriesData){
        if (count($timeSeriesData) < 2) {
            return ['trend' => 'insufficient_data'];
        }
        
        $first = $timeSeriesData[0];
        $last = end($timeSeriesData);
        
        return [
            'assets_change' => $last['total_assets'] - $first['total_assets'],
            'recovery_rate_change' => $last['present_percentage'] - $first['present_percentage'],
            'missing_rate_change' => $last['missing_percentage'] - $first['missing_percentage'],
            'financial_value_change' => $last['financial_total_value'] - $first['financial_total_value'],
            'overall_trend' => $this->determineOverallTrend($first, $last)
        ];
    }


    private function calculateTrend($data, $metric){
        if (count($data) < 2) return 'stable';
        
        $values = array_column($data, $metric);
        $slope = $this->calculateSlope($values);
        
        if ($slope > 0.5) return 'improving';
        if ($slope < -0.5) return 'declining';
        return 'stable';
    }

    private function calculateSlope($values){
        $n = count($values);
        if ($n < 2) return 0;
        
        $x = range(0, $n - 1);
        $xy = 0;
        $x2 = 0;
            $x_mean = array_sum($x) / $n;
            $y_mean = array_sum($values) / $n;
            
            for ($i = 0; $i < $n; $i++) {
                $xy += ($x[$i] - $x_mean) * ($values[$i] - $y_mean);
                $x2 += pow($x[$i] - $x_mean, 2);
            }
            
            return $x2 != 0 ? $xy / $x2 : 0;
    }

    private function calculateAnalysisPeriod($startDate, $endDate){
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        $diffInDays = $start->diffInDays($end);
        
        if ($diffInDays <= 31) {
            return $diffInDays . ' days';
        } elseif ($diffInDays <= 365) {
            $months = $start->diffInMonths($end);
            return $months . ' month' . ($months > 1 ? 's' : '');
        } else {
            $years = $start->diffInYears($end);
            $remainingMonths = $start->addYears($years)->diffInMonths($end);
            
            $period = $years . ' year' . ($years > 1 ? 's' : '');
            if ($remainingMonths > 0) {
                $period .= ' and ' . $remainingMonths . ' month' . ($remainingMonths > 1 ? 's' : '');
            }
            return $period;
        }
    }

    private function identifySeasonalPatterns($monthlyData){
        $patterns = [];
        
        foreach ($monthlyData as $data) {
            $month = date('n', strtotime($data['month'] . '-01'));
            $quarter = ceil($month / 3);
            
            if (!isset($patterns['quarterly'][$quarter])) {
                $patterns['quarterly'][$quarter] = [
                    'audits' => 0,
                    'avg_missing_rate' => 0,
                    'avg_maintenance_rate' => 0
                ];
            }
            
            $patterns['quarterly'][$quarter]['audits']++;
        }
        
        return $patterns;
    }

    private function generatePredictiveInsights($timeSeriesData){
        if (count($timeSeriesData) < 3) {
            return ['message' => 'Insufficient data for predictions'];
        }
        
        $recentData = array_slice($timeSeriesData, -3);
        $missingTrend = $this->calculateTrend($recentData, 'missing_percentage');
        $maintenanceTrend = $this->calculateTrend($recentData, 'maintenance_needed');
        
        return [
            'missing_assets_prediction' => $missingTrend,
            'maintenance_prediction' => $maintenanceTrend,
            'recommended_actions' => $this->generatePredictiveRecommendations($missingTrend, $maintenanceTrend)
        ];
    }

    private function analyzeCategoryTrends($audits){
        $categoryData = [];
        
        foreach ($audits as $audit) {   
            foreach ($audit->auditItems->groupBy('asset.category.name') as $category => $items) {
                if (!isset($categoryData[$category])) {
                    $categoryData[$category] = [];
                }
                
                $categoryData[$category][] = [
                    'audit_date' => $audit->audit_date,
                    'total' => $items->count(),
                    'missing' => $items->where('is_present', false)->count(),
                    'maintenance' => $items->where('needs_maintenance', true)->count()
                ];
            }
        }
        
        $trends = [];
        foreach ($categoryData as $category => $data) {
            if (count($data) >= 2) {
                $missingRates = array_map(function($d) {
                    return $d['total'] > 0 ? ($d['missing'] / $d['total']) * 100 : 0;
                }, $data);
                
                $trends[$category] = [
                    'trend' => $this->calculateTrend($data, 'missing'),
                    'current_missing_rate' => end($missingRates),
                    'data_points' => count($data)
                ];
            }
        }
        
        return $trends;
    }

    private function analyzeLocationTrends($audits){
        $locationData = [];
        
        foreach ($audits as $audit) {
            foreach ($audit->auditItems->groupBy('asset.venue.name') as $location => $items) {
                if (!isset($locationData[$location])) {
                    $locationData[$location] = [];
                }
                
                $locationData[$location][] = [
                    'audit_date' => $audit->audit_date,
                    'total' => $items->count(),
                    'missing' => $items->where('is_present', false)->count(),
                    'maintenance' => $items->where('needs_maintenance', true)->count()
                ];
            }
        }
        
        return $locationData;
    }

    private function generateComparisonMetrics($auditSummaries, $focus){
        $metrics = [
            'total_audits' => count($auditSummaries),
            'average_present_percentage' => 0,
            'average_maintenance_needed' => 0,
            'trend_direction' => 'stable',
            'focus_metrics' => []
        ];
        
        if (empty($auditSummaries)) {
            return $metrics;
        }
        
        $presentPercentages = [];
        $maintenanceNeeded = [];
        
        foreach ($auditSummaries as $summary) {
            $presentPercentages[] = $summary['present_percentage'];
            $maintenanceNeeded[] = $summary['maintenance_needed']; // Using your field name
        }
        
        $metrics['average_present_percentage'] = round(array_sum($presentPercentages) / count($presentPercentages), 2);
        $metrics['average_maintenance_needed'] = round(array_sum($maintenanceNeeded) / count($maintenanceNeeded), 2);
        
        if (count($presentPercentages) >= 2) {
            $first = $presentPercentages[count($presentPercentages) - 1]; // Oldest
            $last = $presentPercentages[0];
            
            if ($last > $first + 5) {
                $metrics['trend_direction'] = 'improving';
            } elseif ($last < $first - 5) {
                $metrics['trend_direction'] = 'declining';
            }
        }
        
        return $metrics;
    }

    private function rankAuditPerformance($auditSummaries){
        $rankings = [];
        
        foreach ($auditSummaries as $auditId => $summary) {
            $score = $this->calculateAuditHealthScore($summary);
            $rankings[] = [
                'audit_id' => $auditId,
                'health_score' => $score,
                'present_percentage' => $summary['present_percentage'],
                'missing_percentage' => $summary['missing_percentage']
            ];
        }

        usort($rankings, function($a, $b) {
            return $b['health_score'] <=> $a['health_score'];
        });
        
        return $rankings;
    }

    private function calculateConsistency($values){
        if (empty($values) || count($values) < 2) {
            return 100;
        }
        
        $mean = array_sum($values) / count($values);
        if ($mean == 0) {
            return 100;
        }

        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $standardDeviation = sqrt($variance / count($values));
        $coefficientOfVariation = ($standardDeviation / $mean) * 100;
        $consistency = max(0, 100 - $coefficientOfVariation);
        return round($consistency, 2);
    }

    private function generateComparativeInsights($auditSummaries, $focus){
        $insights = [];
        $values = [];
        
        foreach ($auditSummaries as $summary) {
            $values[] = $summary['present_percentage'];
        }
        
        if (empty($values)) {
            return [
                'performance_spread' => 0,
                'average_performance' => 0,
                'consistency' => 100,
                'improvement_potential' => 0
            ];
        }
        
        $avgPerformance = array_sum($values) / count($values);
        $bestPerformance = max($values);
        $worstPerformance = min($values);
        
        $insights['performance_spread'] = $bestPerformance - $worstPerformance;
        $insights['average_performance'] = $avgPerformance;
        $insights['consistency'] = $this->calculateConsistency($values);
        $insights['improvement_potential'] = $bestPerformance - $avgPerformance;
        
        if ($focus === 'maintenance') {
            $maintenanceValues = [];
            foreach ($auditSummaries as $summary) {
                $maintenanceValues[] = $summary['maintenance_needed'];
            }
            
            $insights['maintenance_insights'] = [
                'average_maintenance_needed' => array_sum($maintenanceValues) / count($maintenanceValues),
                'highest_maintenance' => max($maintenanceValues),
                'lowest_maintenance' => min($maintenanceValues),
                'maintenance_trend' => $this->calculateMaintenanceTrend($maintenanceValues)
            ];
        }
        
        if ($focus === 'financial') {
            $financialValues = [];
            foreach ($auditSummaries as $summary) {
                if (isset($summary['financial']['total_value'])) {
                    $financialValues[] = $summary['financial']['total_value'];
                }
            }
            
            if (!empty($financialValues)) {
                $insights['financial_insights'] = [
                    'average_value' => array_sum($financialValues) / count($financialValues),
                    'highest_value' => max($financialValues),
                    'lowest_value' => min($financialValues),
                    'value_growth' => $this->calculateValueGrowth($financialValues)
                ];
            }
        }
        
        if ($focus === 'location') {
            $insights['location_insights'] = $this->generateLocationInsights($auditSummaries);
        }
        
        if ($focus === 'category') {
            $insights['category_insights'] = $this->generateCategoryInsights($auditSummaries);
        }
        
        return $insights;
    }

    private function calculateMaintenanceTrend($maintenanceValues){
        if (count($maintenanceValues) < 2) {
            return 'insufficient_data';
        }
        
        $first = $maintenanceValues[count($maintenanceValues) - 1];
        $last = $maintenanceValues[0];
        
        if ($last < $first) {
            return 'improving';
        } elseif ($last > $first) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    private function calculateValueGrowth($financialValues){
        if (count($financialValues) < 2) {
            return 0;
        }
        
        $first = $financialValues[count($financialValues) - 1];
        $last = $financialValues[0];
        
        if ($first == 0) {
            return 0;
        }
        
        return round((($last - $first) / $first) * 100, 2);
    }

    private function generateLocationInsights($auditSummaries){ 
        $locationData = [];
        
        foreach ($auditSummaries as $summary) {
            if (isset($summary['location_breakdown'])) {
                foreach ($summary['location_breakdown'] as $location => $data) {
                    if (!isset($locationData[$location])) {
                        $locationData[$location] = [];
                    }
                    $locationData[$location][] = $data['condition_rate'];
                }
            }
        }
        
        $locationInsights = [];
        foreach ($locationData as $location => $rates) {
            $locationInsights[$location] = [
                'average_condition_rate' => array_sum($rates) / count($rates),
                'consistency' => $this->calculateConsistency($rates),
                'best_rate' => max($rates),
                'worst_rate' => min($rates)
            ];
        }
        
        return $locationInsights;
    }

    private function generateCategoryInsights($auditSummaries){
        $categoryData = [];
        
        foreach ($auditSummaries as $summary) {
            if (isset($summary['category_breakdown'])) {
                foreach ($summary['category_breakdown'] as $category => $data) {
                    if (!isset($categoryData[$category])) {
                        $categoryData[$category] = [];
                    }
                    $categoryData[$category][] = $data['condition_rate'];
                }
            }
        }
        
        $categoryInsights = [];
        foreach ($categoryData as $category => $rates) {
            $categoryInsights[$category] = [
                'average_condition_rate' => array_sum($rates) / count($rates),
                'consistency' => $this->calculateConsistency($rates),
                'best_rate' => max($rates),
                'worst_rate' => min($rates)
            ];
        }
        
        return $categoryInsights;
    }

    private function analyzeProgressBetweenAudits($comparisonData){
        if (count($comparisonData) < 2) {
            return ['message' => 'Need at least 2 audits for progress analysis'];
        }
        
        usort($comparisonData, function($a, $b) {
            return $a['audit_date'] <=> $b['audit_date'];
        });
        
        $first = $comparisonData[0];
        $last = end($comparisonData);
        
        return [
            'time_span' => $first['audit_date']->diffInDays($last['audit_date']),
            'asset_growth' => $last['summary']['total_assets'] - $first['summary']['total_assets'],
            'performance_change' => $last['summary']['present_percentage'] - $first['summary']['present_percentage'],
            'financial_change' => $last['summary']['financial']['total_value'] - $first['summary']['financial']['total_value'],
            'overall_progress' => $this->determineOverallProgress($first['summary'], $last['summary'])
        ];
    }

    private function generateComparisonSummary($auditSummaries){
        $totalAudits = count($auditSummaries);
        $avgPresent = array_sum(array_column($auditSummaries, 'present_percentage')) / $totalAudits;
        $avgMissing = array_sum(array_column($auditSummaries, 'missing_percentage')) / $totalAudits;
        
        return [
            'total_audits_compared' => $totalAudits,
            'average_present_rate' => round($avgPresent, 1),
            'average_missing_rate' => round($avgMissing, 1),
            'comparison_date' => now()->format('Y-m-d H:i')
        ];
    }

    private function calculateDashboardKPIs($audits){
        if ($audits->isEmpty()) {
            return $this->getEmptyKPIs();
        }
        
        $latestAudit = $audits->first();
        $latestSummary = $this->generateAuditSummary($latestAudit);
        $totalAssets = $audits->sum(function($audit) {
            return $audit->auditItems->count();
        });
        
        $totalPresent = $audits->sum(function($audit) {
            return $audit->auditItems->where('is_present', true)->count();
        });
        
        $totalMissing = $audits->sum(function($audit) {
            return $audit->auditItems->where('is_present', false)->count();
        });
        
        $totalValue = $audits->sum(function($audit) {
            return $audit->auditItems->sum(function($item) {
                return $item->asset->current_value ?? $item->asset->purchase_price ?? 0;
            });
        });
        
        return [
            'total_audits' => $audits->count(),
            'latest_audit_date' => $latestAudit->audit_date,
            'total_assets_audited' => $totalAssets,
            'overall_present_rate' => $totalAssets > 0 ? round(($totalPresent / $totalAssets) * 100, 1) : 0,
            'overall_missing_rate' => $totalAssets > 0 ? round(($totalMissing / $totalAssets) * 100, 1) : 0,
            'total_asset_value' => $totalValue,
            'latest_health_score' => $this->calculateAuditHealthScore($latestSummary),
            'audit_frequency' => $this->calculateAuditFrequency($audits),
            'performance_status' => $this->determinePerformanceStatus($latestSummary)
        ];
    }

    private function generateDashboardAlerts($audits){
        $alerts = [];
        $latestAudit = $audits->first();
        $latestSummary = $this->generateAuditSummary($latestAudit);
        
        if ($latestSummary['missing_percentage'] > 10) {
            $alerts[] = [
                'level' => 'critical',
                'type' => 'missing_assets',
                'message' => "High missing asset rate: {$latestSummary['missing_percentage']}%",
                'action' => 'Immediate investigation required'
            ];
        }
        
        if ($latestSummary['maintenance_percentage'] > 25) {
            $alerts[] = [
                'level' => 'warning',
                'type' => 'maintenance',
                'message' => "High maintenance needs: {$latestSummary['maintenance_percentage']}%",
                'action' => 'Schedule maintenance review'
            ];
        }
        
        if ($audits->count() >= 2) {
            $previousAudit = $audits->skip(1)->first();
            $previousSummary = $this->generateAuditSummary($previousAudit);
            
            if ($latestSummary['present_percentage'] < $previousSummary['present_percentage'] - 5) {
                $alerts[] = [
                    'level' => 'warning',
                    'type' => 'declining_performance',
                    'message' => 'Asset recovery rate declining',
                    'action' => 'Review asset management procedures'
                ];
            }
        }
        return $alerts;
    }
    
    private function getEmptyTrendAnalysis(){
        return [
            'message' => 'No audit data available for trend analysis',
            'time_series_data' => [],
            'monthly_data' => [],
            'trend_metrics' => [],
            'performance_trends' => [],
            'total_audits' => 0
        ];
    }

    private function getEmptyDashboardData(){
        return [
            'message' => 'No audit data available for dashboard',
            'kpis' => $this->getEmptyKPIs(),
            'performance_metrics' => [],
            'alerts' => [],
            'total_audits' => 0
        ];
    }

    private function getEmptyKPIs(){
        return [
            'total_audits' => 0,
            'total_assets_audited' => 0,
            'overall_present_rate' => 0,
            'overall_missing_rate' => 0,
            'total_asset_value' => 0,
            'latest_health_score' => 0
        ];
    }

    private function determineOverallProgress($firstSummary, $lastSummary){
        $weights = [
            'present_percentage' => 0.4,
            'maintenance_reduction' => 0.3,
            'asset_growth' => 0.2,
            'financial_growth' => 0.1
        ];
        
        $scores = [];
        
        $presentImprovement = $lastSummary['present_percentage'] - $firstSummary['present_percentage'];
        $scores['present_percentage'] = $this->normalizeScore($presentImprovement, -20, 20);
        
        $maintenanceChange = $firstSummary['maintenance_needed'] - $lastSummary['maintenance_needed'];
        $maxMaintenanceChange = max($firstSummary['total_assets'], $lastSummary['total_assets']) * 0.1; 
        $scores['maintenance_reduction'] = $this->normalizeScore($maintenanceChange, -$maxMaintenanceChange, $maxMaintenanceChange);
        
        $assetGrowth = $lastSummary['total_assets'] - $firstSummary['total_assets'];
        $maxAssetGrowth = $firstSummary['total_assets'] * 0.5;
        $scores['asset_growth'] = $this->normalizeScore($assetGrowth, 0, $maxAssetGrowth);
        
        if (isset($firstSummary['financial']['total_value']) && isset($lastSummary['financial']['total_value'])) {
            $financialGrowth = $lastSummary['financial']['total_value'] - $firstSummary['financial']['total_value'];
            $maxFinancialGrowth = $firstSummary['financial']['total_value'] * 0.3; 
            $scores['financial_growth'] = $this->normalizeScore($financialGrowth, 0, $maxFinancialGrowth);
        } else {
            $scores['financial_growth'] = 50; 
        }
        
        $overallScore = 0;
        foreach ($scores as $metric => $score) {
            $overallScore += $score * $weights[$metric];
        }
        
        if ($overallScore >= 75) {
            $category = 'excellent';
            $description = 'Excellent progress across all audit metrics';
        } elseif ($overallScore >= 60) {
            $category = 'good';
            $description = 'Good progress with room for improvement';
        } elseif ($overallScore >= 40) {
            $category = 'moderate';
            $description = 'Moderate progress, needs attention';
        } else {
            $category = 'poor';
            $description = 'Poor progress, immediate action required';
        }
        
        return [
            'score' => round($overallScore, 2),
            'category' => $category,
            'description' => $description,
            'detailed_scores' => $scores,
            'improvements' => $this->identifyImprovements($scores),
            'concerns' => $this->identifyConcerns($scores)
        ];
    }

    private function normalizeScore($value, $min, $max){
        if ($max == $min) { 
            return 50;
        }
        
        $normalized = (($value - $min) / ($max - $min)) * 100;
        return max(0, min(100, $normalized));
    }

    private function identifyImprovements($scores){
        $improvements = [];
        
        if ($scores['present_percentage'] >= 70) {
            $improvements[] = 'Asset tracking accuracy has improved significantly';
        }
        
        if ($scores['maintenance_reduction'] >= 70) {
            $improvements[] = 'Maintenance requirements have been reduced effectively';
        }
        
        if ($scores['asset_growth'] >= 70) {
            $improvements[] = 'Asset portfolio has grown substantially';
        }
        
        if ($scores['financial_growth'] >= 70) {
            $improvements[] = 'Total asset value has increased considerably';
        }
        
        return $improvements;
    }

    private function identifyConcerns($scores){
        $concerns = [];
        
        if ($scores['present_percentage'] <= 30) {
            $concerns[] = 'Asset tracking accuracy has declined - investigate missing assets';
        }
        
        if ($scores['maintenance_reduction'] <= 30) {
            $concerns[] = 'Maintenance requirements have increased - review asset conditions';
        }
        
        if ($scores['asset_growth'] <= 20) {
            $concerns[] = 'Asset portfolio growth is stagnant or declining';
        }
        
        if ($scores['financial_growth'] <= 20) {
            $concerns[] = 'Total asset value has not increased as expected';
        }
        return $concerns;
    }

    private function determineOverallTrend($first, $last){
        $improvementScore = 0;
        $totalMetrics = 0;
        
        if (isset($first['present_percentage']) && isset($last['present_percentage'])) {
            $improvement = $last['present_percentage'] - $first['present_percentage'];
            if ($improvement > 5) $improvementScore++;
            elseif ($improvement < -5) $improvementScore--;
            $totalMetrics++;
        }
        
        if (isset($first['missing_percentage']) && isset($last['missing_percentage'])) {
            $improvement = $first['missing_percentage'] - $last['missing_percentage'];
            if ($improvement > 2) $improvementScore++;
            elseif ($improvement < -2) $improvementScore--;
            $totalMetrics++;
        }
        
        if (isset($first['financial_total_value']) && isset($last['financial_total_value'])) {
            $improvement = $last['financial_total_value'] - $first['financial_total_value'];
            if ($improvement > 0) $improvementScore++;
            elseif ($improvement < 0) $improvementScore--;
            $totalMetrics++;
        }
        
        if ($totalMetrics == 0) return 'insufficient_data';
        
        $score = $improvementScore / $totalMetrics;
        
        if ($score > 0.5) return 'improving';
        if ($score < -0.5) return 'declining';
        return 'stable';
    }
    
    private function generatePredictiveRecommendations($missingTrend, $maintenanceTrend){
        $recommendations = [];
        
        if ($missingTrend === 'declining') {
            $recommendations[] = 'Immediate security review required - missing assets trend increasing';
            $recommendations[] = 'Implement stricter asset tracking procedures';
            $recommendations[] = 'Consider RFID or barcode tracking system';
        }
        
        if ($maintenanceTrend === 'declining') {
            $recommendations[] = 'Increase preventive maintenance schedule';
            $recommendations[] = 'Review asset replacement policies';
            $recommendations[] = 'Budget for increased maintenance costs';
        }
        
        if ($missingTrend === 'improving' && $maintenanceTrend === 'improving') {
            $recommendations[] = 'Continue current asset management practices';
            $recommendations[] = 'Consider expanding successful procedures to other areas';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Maintain current monitoring and review practices';
        }
        
        return $recommendations;
    }
    
    private function analyzeCategoryPerformance($audits){
        $categoryPerformance = [];
        foreach ($audits as $audit) {
            $categoryGroups = $audit->auditItems->groupBy('asset.category.name');
            
            foreach ($categoryGroups as $category => $items) {
                if (!isset($categoryPerformance[$category])) {
                    $categoryPerformance[$category] = [
                        'total_audits' => 0,
                        'total_assets' => 0,
                        'total_present' => 0,
                        'total_missing' => 0,
                        'total_maintenance' => 0,
                        'total_value' => 0
                    ];
                }
                
                $categoryPerformance[$category]['total_audits']++;
                $categoryPerformance[$category]['total_assets'] += $items->count();
                $categoryPerformance[$category]['total_present'] += $items->where('is_present', true)->count();
                $categoryPerformance[$category]['total_missing'] += $items->where('is_present', false)->count();
                $categoryPerformance[$category]['total_maintenance'] += $items->where('needs_maintenance', true)->count();
                $categoryPerformance[$category]['total_value'] += $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            }
        }
        
        foreach ($categoryPerformance as $category => &$performance) {
            $performance['present_rate'] = $performance['total_assets'] > 0 ? 
                round(($performance['total_present'] / $performance['total_assets']) * 100, 1) : 0;
            $performance['missing_rate'] = $performance['total_assets'] > 0 ? 
                round(($performance['total_missing'] / $performance['total_assets']) * 100, 1) : 0;
            $performance['maintenance_rate'] = $performance['total_assets'] > 0 ? 
                round(($performance['total_maintenance'] / $performance['total_assets']) * 100, 1) : 0;
            $performance['average_value'] = $performance['total_assets'] > 0 ? 
                round($performance['total_value'] / $performance['total_assets'], 2) : 0;
        }
        
        return $categoryPerformance;
    }
    
    private function analyzeLocationPerformance($audits){
        $locationPerformance = [];
        
        foreach ($audits as $audit) {
            $locationGroups = $audit->auditItems->groupBy('asset.venue.name');
            
            foreach ($locationGroups as $location => $items) {
                if (!isset($locationPerformance[$location])) {
                    $locationPerformance[$location] = [
                        'total_audits' => 0,
                        'total_assets' => 0,
                        'total_present' => 0,
                        'total_missing' => 0,
                        'total_maintenance' => 0,
                        'total_value' => 0
                    ];
                }
                
                $locationPerformance[$location]['total_audits']++;
                $locationPerformance[$location]['total_assets'] += $items->count();
                $locationPerformance[$location]['total_present'] += $items->where('is_present', true)->count();
                $locationPerformance[$location]['total_missing'] += $items->where('is_present', false)->count();
                $locationPerformance[$location]['total_maintenance'] += $items->where('needs_maintenance', true)->count();
                $locationPerformance[$location]['total_value'] += $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
            }
        }
        
        foreach ($locationPerformance as $location => &$performance) {
            $performance['present_rate'] = $performance['total_assets'] > 0 ? 
                round(($performance['total_present'] / $performance['total_assets']) * 100, 1) : 0;
            $performance['missing_rate'] = $performance['total_assets'] > 0 ? 
                round(($performance['total_missing'] / $performance['total_assets']) * 100, 1) : 0;
            $performance['maintenance_rate'] = $performance['total_assets'] > 0 ? 
                round(($performance['total_maintenance'] / $performance['total_assets']) * 100, 1) : 0;
            $performance['security_score'] = $this->calculateLocationSecurityScore($performance);
        }
        
        return $locationPerformance;
    }
    
    private function calculateLocationSecurityScore($performance){
        $baseScore = 100;
        $baseScore -= $performance['missing_rate'] * 2;
        $baseScore -= $performance['maintenance_rate'] * 1;
        
        return max(0, round($baseScore, 1));
    }
    
    private function calculateTrendIndicators($performanceMetrics){
        if (count($performanceMetrics) < 2) {
            return [
                'health_score_trend' => 'stable',
                'recovery_rate_trend' => 'stable',
                'missing_rate_trend' => 'stable',
                'overall_trend_strength' => 'weak'
            ];
        }
        
        $healthScores = array_column($performanceMetrics, 'health_score');
        $recoveryRates = array_column($performanceMetrics, 'recovery_rate');
        $missingRates = array_column($performanceMetrics, 'missing_rate');
        
        return [
            'health_score_trend' => $this->calculateTrend($performanceMetrics, 'health_score'),
            'recovery_rate_trend' => $this->calculateTrend($performanceMetrics, 'recovery_rate'),
            'missing_rate_trend' => $this->calculateTrend($performanceMetrics, 'missing_rate'),
            'overall_trend_strength' => $this->calculateTrendStrength($healthScores, $recoveryRates, $missingRates)
        ];
    }
    
    private function calculateTrendStrength($healthScores, $recoveryRates, $missingRates){
        $trends = [
            $this->calculateSlope($healthScores),
            $this->calculateSlope($recoveryRates),
            -$this->calculateSlope($missingRates) // Negative because lower missing rate is better
        ];
        
        $avgTrend = array_sum($trends) / count($trends);
        
        if (abs($avgTrend) > 2) return 'strong';
        if (abs($avgTrend) > 0.5) return 'moderate';
        return 'weak';
    }
    
    private function generateExecutiveSummary($audits, $kpis){
        $latestAudit = $audits->first();
        $totalAssets = $kpis['total_assets_audited'];
        $presentRate = $kpis['overall_present_rate'];
        $healthScore = $kpis['latest_health_score'];
        
        $status = 'good';
        $statusClass = 'success';
        $keyPoints = [];
        
        if ($healthScore >= 85) {
            $status = 'excellent';
            $keyPoints[] = 'Asset management performance is excellent across all metrics';
        } elseif ($healthScore >= 70) {
            $status = 'good';
            $keyPoints[] = 'Asset management performance is good with minor areas for improvement';
        } elseif ($healthScore >= 50) {
            $status = 'fair';
            $statusClass = 'warning';
            $keyPoints[] = 'Asset management performance needs attention in several areas';
        } else {
            $status = 'poor';
            $statusClass = 'danger';
            $keyPoints[] = 'Asset management performance requires immediate intervention';
        }
        
        if ($presentRate >= 95) {
            $keyPoints[] = 'Asset tracking accuracy is outstanding';
        } elseif ($presentRate >= 85) {
            $keyPoints[] = 'Asset tracking accuracy is satisfactory';
        } else {
            $keyPoints[] = 'Asset tracking accuracy needs improvement';
        }
        
        if ($kpis['overall_missing_rate'] > 10) {
            $keyPoints[] = 'High missing asset rate requires immediate investigation';
        }
        
        return [
            'status' => $status,
            'status_class' => $statusClass,
            'health_score' => $healthScore,
            'key_points' => $keyPoints,
            'total_assets' => $totalAssets,
            'present_rate' => $presentRate,
            'audit_count' => $audits->count(),
            'period_covered' => $this->calculatePeriodCovered($audits),
            'recommendations' => $this->generateExecutiveRecommendations($kpis, $status)
        ];
    }
    
    private function calculatePeriodCovered($audits){
        if ($audits->count() < 2) {
            return 'Single audit';
        }
        
        $earliest = $audits->last()->audit_date;
        $latest = $audits->first()->audit_date;
        
        return $earliest->diffForHumans($latest, true);
    }
    
    private function generateExecutiveRecommendations($kpis, $status){
        $recommendations = [];
        
        if ($status === 'excellent') {
            $recommendations[] = 'Maintain current asset management practices';
            $recommendations[] = 'Consider sharing best practices with other departments';
        } elseif ($status === 'good') {
            $recommendations[] = 'Focus on reducing missing asset rates';
            $recommendations[] = 'Implement predictive maintenance where possible';
        } elseif ($status === 'fair') {
            $recommendations[] = 'Conduct comprehensive review of asset management procedures';
            $recommendations[] = 'Increase audit frequency for problem areas';
            $recommendations[] = 'Provide additional training for asset custodians';
        } else {
            $recommendations[] = 'Immediate action required: implement emergency asset recovery procedures';
            $recommendations[] = 'Conduct security review for all high-value assets';
            $recommendations[] = 'Consider external asset management consultation';
        }
        
        return $recommendations;
    }
    
    private function calculateOverallDashboardHealth($kpis){
        $healthFactors = [
            'present_rate' => $kpis['overall_present_rate'] * 0.4,
            'missing_rate_impact' => (100 - $kpis['overall_missing_rate']) * 0.3,
            'audit_frequency' => $this->normalizeAuditFrequency($kpis['audit_frequency'] ?? 0) * 0.2,
            'health_score' => $kpis['latest_health_score'] * 0.1
        ];
        
        $overallHealth = array_sum($healthFactors);
        
        return round($overallHealth, 1);
    }
    
    private function normalizeAuditFrequency($frequency){
        if ($frequency >= 4 && $frequency <= 6) {
            return 100;
        } elseif ($frequency >= 2 && $frequency <= 8) {
            return 75;
        } elseif ($frequency >= 1) {
            return 50;
        } else {
            return 25;
        }
    }
    
    private function calculateAuditFrequency($audits){
        if ($audits->count() < 2) {
            return 1;
        }
        
        $earliest = $audits->last()->audit_date;
        $latest = $audits->first()->audit_date;
        $daysDifference = $earliest->diffInDays($latest);
        
        if ($daysDifference == 0) {
            return $audits->count();
        }
        $auditsPerYear = ($audits->count() / $daysDifference) * 365;
        return round($auditsPerYear, 1);
    }
    
    private function determinePerformanceStatus($summary){
        $healthScore = $this->calculateAuditHealthScore($summary);
        
        if ($healthScore >= 90) {
            return ['status' => 'excellent', 'class' => 'success'];
        } elseif ($healthScore >= 75) {
            return ['status' => 'good', 'class' => 'info'];
        } elseif ($healthScore >= 60) {
            return ['status' => 'fair', 'class' => 'warning'];
        } else {
            return ['status' => 'poor', 'class' => 'danger'];
        }
    }
}
