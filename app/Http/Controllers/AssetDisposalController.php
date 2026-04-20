<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AssetLog;
use Illuminate\Support\Facades\Auth;

class AssetDisposalController extends Controller{
    public function index(Request $request){
        $disposalsQuery = AssetDisposal::with(['asset', 'authorizedByUser']);
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $disposalsQuery->whereBetween('disposal_date', [$request->start_date, $request->end_date]);
        }
        
        if ($request->filled('disposal_method')) {
            $disposalsQuery->where('disposal_method', $request->disposal_method);
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $disposalsQuery->whereHas('asset', function($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('asset_code', 'like', $search);
            });
        }
        
        $disposals = $disposalsQuery->orderBy('disposal_date', 'desc')->paginate(15)->withQueryString();
        return view('assets.disposals.index', compact('disposals'));
    }

    public function create($assetId = null){
        if ($assetId) {
            $asset = Asset::findOrFail($assetId);
            
            if ($asset->isDisposed()) {
                return redirect()->route('disposals.index')
                    ->with('error', 'This asset is already disposed.');
            }
            
            if ($asset->isAssigned()) {
                return redirect()->route('disposals.index')
                    ->with('error', 'This asset is currently assigned. Please return it before disposal.');
            }
            
            return view('assets.disposals.create-disposal', compact('asset'));
        }
        
        $availableAssets = Asset::whereNotIn('status', ['Disposed', 'Assigned'])->orderBy('name')->get();
        return view('assets.disposals.create-disposal', compact('availableAssets'));
    }


    public function store(Request $request){
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'disposal_date' => 'required|date',
            'disposal_method' => 'required|string|in:Sold,Scrapped,Donated,Recycled',
            'disposal_amount' => 'nullable|numeric|required_if:disposal_method,Sold',
            'reason' => 'required|string',
            'recipient' => 'nullable|string|required_if:disposal_method,Donated',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);
        if ($asset->isDisposed()) {
            return redirect()->back()->with('error', 'This asset is already disposed.');
        }

        if ($asset->isAssigned()) {
            return redirect()->back()->with('error', 'This asset is currently assigned. Please return it before disposal.');
        }

        if ($asset->isInMaintenance()) {
            return redirect()->back()->with('error', 'This asset is currently in maintenance. Please complete maintenance before disposal.');
        }

        $validated['authorized_by'] = Auth::id();
        $disposal = AssetDisposal::create($validated);
        $asset->update([
            'status' => 'Disposed',
            'current_value' => $validated['disposal_method'] === 'Sold' ? $validated['disposal_amount'] : null
        ]);

        AssetLog::createLog(
            $asset->id,
            'disposal',
            "Asset was disposed via {$validated['disposal_method']}",
            [
                'disposal_method' => $validated['disposal_method'],
                'disposal_date' => $validated['disposal_date'],
                'disposal_amount' => $validated['disposal_amount'] ?? null,
                'reason' => $validated['reason'],
                'recipient' => $validated['recipient'] ?? null,
            ],
            Auth::id()
        );
        return redirect()->back()->with('message', 'Asset disposal recorded successfully.');
    }

    public function show($id){
        $disposal = AssetDisposal::findOrFail($id);
        $disposal->load(['asset', 'authorizedByUser']);
        return view('disposals.show-disposal', compact('disposal'));
    }

    public function edit($id){
        $disposal = AssetDisposal::findOrFail($id);
        $disposal->load('asset');
        return view('assets.disposals.edit-disposal', compact('disposal'));
    }

    public function update(Request $request, AssetDisposal $disposal){
        $validated = $request->validate([
            'disposal_date' => 'required|date',
            'disposal_method' => 'required|string|in:Sold,Scrapped,Donated,Recycled',
            'disposal_amount' => 'nullable|numeric|required_if:disposal_method,Sold',
            'reason' => 'required|string',
            'recipient' => 'nullable|string|required_if:disposal_method,Donated',
            'notes' => 'nullable|string',
        ]);
        
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($disposal->{$key} != $value) {
                $changes[$key] = [
                    'old' => $disposal->{$key},
                    'new' => $value
                ];
            }
        }
        
        $disposal->update($validated);
        if (!empty($changes)) {
            AssetLog::createLog(
                $disposal->asset_id,
                'update',
                'Asset disposal information was updated',
                $changes,
                Auth::id()
            );
        }
        return redirect()->back()->with('message', 'Disposal record updated successfully.');
    }

    public function destroy(AssetDisposal $disposal){
        $assetId = $disposal->asset_id;
        $asset = Asset::find($assetId);
        
        if ($asset) {
            $asset->update(['status' => 'Available']);
            
            AssetLog::createLog(
                $assetId,
                'update',
                'Asset disposal was reversed',
                [
                    'status' => [
                        'old' => 'Disposed',
                        'new' => 'Available'
                    ],
                    'disposal_record' => 'Deleted'
                ],
                Auth::id()
            );
        }
        $disposal->delete();
        return redirect()->back()->with('message', 'Disposal record cancelled successfully.');
    }

    public function disposalSummaryReport(Request $request){
        $disposals = AssetDisposal::with([
            'asset.category', 
            'asset.venue', 
            'authorizedByUser'
        ])->orderBy('disposal_date', 'desc')->get();
        
        $totalDisposals = $disposals->count();
        $totalAmount = $disposals->sum('disposal_amount');
        
        $disposalsByMethod = [
            'Sold' => $disposals->where('disposal_method', 'Sold')->count(),
            'Donated' => $disposals->where('disposal_method', 'Donated')->count(),
            'Scrapped' => $disposals->where('disposal_method', 'Scrapped')->count(),
            'Recycled' => $disposals->where('disposal_method', 'Recycled')->count(),
        ];
        
        $amountsByMethod = [
            'Sold' => $disposals->where('disposal_method', 'Sold')->sum('disposal_amount'),
            'Donated' => $disposals->where('disposal_method', 'Donated')->sum('disposal_amount'),
            'Scrapped' => $disposals->where('disposal_method', 'Scrapped')->sum('disposal_amount'),
            'Recycled' => $disposals->where('disposal_method', 'Recycled')->sum('disposal_amount'),
        ];
        
        $recentDisposals = $disposals->filter(function($disposal) {
            return $disposal->disposal_date >= now()->subDays(30);
        })->count();
        
        $disposalsWithAmount = $disposals->filter(function($disposal) {
            return $disposal->disposal_amount > 0;
        });
        $averageAmount = $disposalsWithAmount->count() > 0 ? $disposalsWithAmount->avg('disposal_amount') : 0;
        
        $summary = [
            'total_disposals' => $totalDisposals,
            'total_amount' => $totalAmount,
            'recent_disposals' => $recentDisposals,
            'average_amount' => $averageAmount,
            'disposals_by_method' => $disposalsByMethod,
            'amounts_by_method' => $amountsByMethod,
        ];
        
        return view('assets.disposals.reports.disposals-summary-report', compact(
            'disposals',
            'summary'
        ));
    }

    public function disposalByDateAndStatusReport(Request $request){
        $dateFrom = $request->get('date_from', now()->subMonths(12)->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());
        
        if (is_string($dateFrom)) {
            $dateFrom = \Carbon\Carbon::parse($dateFrom);
        }

        if (is_string($dateTo)) {
            $dateTo = \Carbon\Carbon::parse($dateTo);
        }
        
        $disposals = AssetDisposal::with([
            'asset.category', 
            'asset.venue', 
            'authorizedByUser'
        ])->whereBetween('disposal_date', [$dateFrom, $dateTo])->orderBy('disposal_date', 'desc')->get();

        $disposalsByMonth = [];
        $monthlyTotals = [];
        $monthlyAmounts = [];
        
        $currentDate = $dateFrom->copy()->startOfMonth();
        while ($currentDate <= $dateTo->endOfMonth()) {
            $monthKey = $currentDate->format('Y-m');
            $monthLabel = $currentDate->format('M Y');
            
            $disposalsByMonth[$monthKey] = [
                'label' => $monthLabel,
                'total_count' => 0,
                'total_amount' => 0,
                'by_method' => [
                    'Sold' => ['count' => 0, 'amount' => 0],
                    'Donated' => ['count' => 0, 'amount' => 0],
                    'Scrapped' => ['count' => 0, 'amount' => 0],
                    'Recycled' => ['count' => 0, 'amount' => 0],
                ],
                'disposals' => []
            ];
            
            $monthlyTotals[$monthKey] = 0;
            $monthlyAmounts[$monthKey] = 0;
            $currentDate->addMonth();
        }
        
        foreach ($disposals as $disposal) {
            $monthKey = $disposal->disposal_date->format('Y-m');
            
            if (isset($disposalsByMonth[$monthKey])) {
                $disposalsByMonth[$monthKey]['total_count']++;
                $disposalsByMonth[$monthKey]['total_amount'] += $disposal->disposal_amount ?? 0;
                $disposalsByMonth[$monthKey]['disposals'][] = $disposal;
                
                $method = $disposal->disposal_method;
                if (isset($disposalsByMonth[$monthKey]['by_method'][$method])) {
                    $disposalsByMonth[$monthKey]['by_method'][$method]['count']++;
                    $disposalsByMonth[$monthKey]['by_method'][$method]['amount'] += $disposal->disposal_amount ?? 0;
                }
                
                $monthlyTotals[$monthKey]++;
                $monthlyAmounts[$monthKey] += $disposal->disposal_amount ?? 0;
            }
        }
        
        $totalDisposals = $disposals->count();
        $totalAmount = $disposals->sum('disposal_amount');
        
        $peakMonth = '';
        $peakCount = 0;
        foreach ($monthlyTotals as $month => $count) {
            if ($count > $peakCount) {
                $peakCount = $count;
                $peakMonth = $disposalsByMonth[$month]['label'];
            }
        }
        
        $activeMonths = collect($monthlyTotals)->filter(function($count) {
            return $count > 0;
        })->count();
        $averagePerMonth = $activeMonths > 0 ? $totalDisposals / $activeMonths : 0;
        $methodTotals = [
            'Sold' => ['count' => 0, 'amount' => 0],
            'Donated' => ['count' => 0, 'amount' => 0],
            'Scrapped' => ['count' => 0, 'amount' => 0],
            'Recycled' => ['count' => 0, 'amount' => 0],
        ];
        
        foreach ($disposals as $disposal) {
            $method = $disposal->disposal_method;
            if (isset($methodTotals[$method])) {
                $methodTotals[$method]['count']++;
                $methodTotals[$method]['amount'] += $disposal->disposal_amount ?? 0;
            }
        }
        
        $summary = [
            'total_disposals' => $totalDisposals,
            'total_amount' => $totalAmount,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'peak_month' => $peakMonth,
            'peak_count' => $peakCount,
            'average_per_month' => $averagePerMonth,
            'method_totals' => $methodTotals,
            'monthly_chart_data' => [
                'labels' => array_values(array_map(function($month) { 
                    return $month['label']; 
                }, $disposalsByMonth)),
                'counts' => array_values($monthlyTotals),
                'amounts' => array_values($monthlyAmounts)
            ]
        ];
        
        return view('assets.disposals.reports.disposals-by-date-status-report', compact(
            'disposalsByMonth',
            'summary',
            'dateFrom',
            'dateTo'
        ));
    }
}