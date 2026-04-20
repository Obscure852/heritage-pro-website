<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AssignmentsController extends Controller{

    public function index(Request $request){
        $assignmentsQuery = AssetAssignment::with(['asset', 'assignable', 'assignedByUser']);
        if ($request->filled('status')) {
            $assignmentsQuery->where('status', $request->status);
        }
        
        if ($request->filled('asset_id')) {
            $assignmentsQuery->where('asset_id', $request->asset_id);
        }
        
        if ($request->filled('user_id')) {
            $assignmentsQuery->where('assignable_type', 'App\\Models\\User')
                             ->where('assignable_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $assignmentsQuery->where('assigned_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $assignmentsQuery->where('assigned_date', '<=', $request->date_to);
        }
        
        if ($request->filled('overdue')) {
            $now = now()->format('Y-m-d');
            $assignmentsQuery->where('status', 'Assigned')
                             ->whereNotNull('expected_return_date')
                             ->where('expected_return_date', '<', $now);
        }
        
        $sortField = $request->sort ?? 'assigned_date';
        $sortDirection = $request->direction ?? 'desc';
        $allowedSortFields = ['assigned_date', 'expected_return_date', 'status'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $assignmentsQuery->orderBy($sortField, $sortDirection);
        } else {
            $assignmentsQuery->orderBy('assigned_date', 'desc');
        }
        
        $assignments = $assignmentsQuery->paginate(15)->withQueryString();
        $assets = Asset::orderBy('name')->get();
        $users = User::orderBy('lastname')->get();

        $availableAssets = Asset::where('status', 'Available')->with(['category'])->orderBy('name')->get();
        
        return view('assets.assignments.index', compact('assignments', 'assets', 'users', 'availableAssets'));
    }

    public function show($id){
        $assignment = AssetAssignment::with(['asset', 'assignable', 'assignedByUser', 'receivedByUser'])->findOrFail($id);
        return view('assets.assignments.assignment-detail-view', compact('assignment'));
    }

    public function processReturn(Request $request, $id){
        $assignment = AssetAssignment::findOrFail($id);
        
        if ($assignment->status !== 'Assigned' || $assignment->actual_return_date) {
            return redirect()->back()->with('error', 'This assignment has already been completed or returned.');
        }
        
        $validated = $request->validate([
            'actual_return_date' => 'required|date',
            'condition_on_return' => 'required|string|in:New,Good,Fair,Poor',
            'return_notes' => 'nullable|string',
        ]);
        
        $asset = $assignment->asset;
        
        $assignment->actual_return_date = $validated['actual_return_date'];
        $assignment->condition_on_return = $validated['condition_on_return'];
        $assignment->return_notes = $validated['return_notes'];
        $assignment->status = 'Returned';
        $assignment->received_by = auth()->id();
        $assignment->save();
        
        $asset->status = 'Available';
        $asset->condition = $validated['condition_on_return'];
        $asset->save();
        
        AssetLog::createLog(
            $asset->id,
            'return',
            "Asset returned from user: {$assignment->assignable->firstname} {$assignment->assignable->lastname}",
            [
                'return_date' => $validated['actual_return_date'],
                'condition' => $validated['condition_on_return'],
                'notes' => $validated['return_notes']
            ],
            auth()->id()
        );
        
        if ($assignment->assignable->hasValidEmail()) {
            $this->sendReturnConfirmationEmail($assignment->assignable, [
                'asset_name' => $asset->name,
                'asset_code' => $asset->asset_code,
                'return_date' => $validated['actual_return_date'],
                'condition' => $validated['condition_on_return']
            ]);
        }
        
        return redirect()->route('assets.assignments.index')
            ->with('message', "Asset '{$asset->name}' has been successfully returned.");
    }

    public function overdue(){
        $now = now()->format('Y-m-d');
        $overdueAssignments = AssetAssignment::where('status', 'Assigned')
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', $now)
            ->with(['asset', 'assignable', 'assignedByUser'])
            ->orderBy('expected_return_date')
            ->paginate(15);
        
        return view('assets.assignments.overdue', compact('overdueAssignments'));
    }

    public function userAssignments($userId){
        $user = User::findOrFail($userId);
        $assignments = AssetAssignment::with([
                'asset',
                'asset.category',
                'asset.venue',
                'assignedByUser',
                'receivedByUser'
            ])->where('assignable_type', 'App\\Models\\User')
              ->where('assignable_id', $user->id)
              ->latest('assigned_date')
              ->get();
        
        $availableAssets = Asset::where('status', 'Available')
            ->with(['category', 'venue'])
            ->orderBy('name')
            ->get();
        
        $categories = AssetCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $stats = [
            'total' => $assignments->count(),
            'active' => $assignments->where('status', 'Assigned')->count(),
            'returned' => $assignments->where('status', 'Returned')->count(),
            'overdue' => $assignments->where('status', 'Assigned')->filter(function($assignment) {
                    return $assignment->isOverdue();
                })->count(),
            'total_value' => $assignments->where('status', 'Assigned')->sum(function($assignment) {
                    return $assignment->asset->current_value ?? 0;
                })
        ];
        
        $filter = request('filter');

        if ($filter === 'active') {
            $filteredAssignments = $assignments->where('status', 'Assigned');
            $activeTab = 'current';
        } elseif ($filter === 'returned') {
            $filteredAssignments = $assignments->where('status', 'Returned');
            $activeTab = 'history';
        } elseif ($filter === 'overdue') {
            $filteredAssignments = $assignments->where('status', 'Assigned')->filter(function($assignment) {
                    return $assignment->isOverdue();
                });
            $activeTab = 'current';
        } else {
            $filteredAssignments = $assignments;
            $activeTab = 'current';
        }
        
        $hasOverdueAssets = $assignments->where('status', 'Assigned')->contains(function($assignment) {
                return $assignment->isOverdue();
            });
        
        $recentLogs = AssetLog::whereIn('asset_id', $assignments->pluck('asset_id'))
            ->with('asset', 'performedByUser')
            ->latest()
            ->take(5)
            ->get();
        
        return view('assets.assignments.user-assignments', compact(
            'user',
            'assignments',
            'filteredAssignments',
            'availableAssets',
            'categories',
            'stats',
            'activeTab',
            'hasOverdueAssets',
            'recentLogs'
        ));
    }

    private function sendAssignmentNotificationEmail(User $user, array $details){
        $subject = "Asset Assignment: {$details['asset_name']}";
        
        $message = "Dear {$user->getFullNameAttribute()},\n\n";
        $message .= "We would like to inform you that the following asset has been assigned to you:\n\n";
        $message .= "Asset Details:\n";
        $message .= "- Name: {$details['asset_name']}\n";
        $message .= "- Asset Code: {$details['asset_code']}\n";
        $message .= "- Assigned Date: {$details['assigned_date']}\n";
        
        if (!empty($details['expected_return_date'])) {
            $message .= "- Expected Return Date: {$details['expected_return_date']}\n";
        }
        
        $message .= "- Condition on Assignment: {$details['condition']}\n";
        
        if (!empty($details['notes'])) {
            $message .= "- Notes: {$details['notes']}\n";
        }
        
        $message .= "\nPlease take good care of this asset. If you have any questions or issues, please contact the IT department.\n\n";
        $message .= "Regards,\nAsset Management Team";
        
        Mail::raw($message, function($mail) use ($user, $subject) {
            $mail->to($user->email)
                 ->subject($subject);
        });
    }

    private function sendReturnConfirmationEmail(User $user, array $details){
        $subject = "Asset Return Confirmation: {$details['asset_name']}";
        
        $message = "Dear {$user->getFullNameAttribute()},\n\n";
        $message .= "We confirm that the following asset has been returned:\n\n";
        $message .= "Asset Details:\n";
        $message .= "- Name: {$details['asset_name']}\n";
        $message .= "- Asset Code: {$details['asset_code']}\n";
        $message .= "- Return Date: {$details['return_date']}\n";
        $message .= "- Condition on Return: {$details['condition']}\n";
        
        $message .= "\nThank you for returning this asset. If you have any questions, please contact the IT department.\n\n";
        $message .= "Regards,\nAsset Management Team";
        
        Mail::raw($message, function($mail) use ($user, $subject) {
            $mail->to($user->email)
                 ->subject($subject);
        });
    }

    public function currentAssignmentsReport(Request $request){
        $currentAssignments = AssetAssignment::with([
            'asset.category',
            'asset.venue',
            'assignable',
            'assignedByUser',
            'receivedByUser'
        ])
        ->where('status', 'Assigned')
        ->whereNull('actual_return_date')
        ->orderBy('assigned_date', 'desc')
        ->get();

        $assignmentMetrics = [
            'total_current_assignments' => $currentAssignments->count(),
            'overdue_assignments' => $currentAssignments->filter(function($assignment) {
                return $assignment->isOverdue();
            })->count(),
            'due_soon_assignments' => $currentAssignments->filter(function($assignment) {
                if (!$assignment->expected_return_date) return false;
                $daysUntilDue = now()->diffInDays($assignment->expected_return_date, false);
                return $daysUntilDue >= 0 && $daysUntilDue <= 7 && !$assignment->isOverdue();
            })->count(),
            'no_due_date_assignments' => $currentAssignments->filter(function($assignment) {
                return is_null($assignment->expected_return_date);
            })->count(),
            'average_assignment_duration' => $this->calculateAverageAssignmentDuration($currentAssignments),
            'longest_assignment_days' => $this->getLongestAssignmentDuration($currentAssignments),
        ];

        $assignmentsByStatus = [
            'overdue' => $currentAssignments->filter(function($assignment) {
                return $assignment->isOverdue();
            })->sortBy(function($assignment) {
                return $assignment->expected_return_date ? 
                    now()->diffInDays($assignment->expected_return_date) : 0;
            })->reverse(),
            
            'due_soon' => $currentAssignments->filter(function($assignment) {
                if (!$assignment->expected_return_date) return false;
                $daysUntilDue = now()->diffInDays($assignment->expected_return_date, false);
                return $daysUntilDue >= 0 && $daysUntilDue <= 7 && !$assignment->isOverdue();
            })->sortBy('expected_return_date'),
            
            'normal' => $currentAssignments->filter(function($assignment) {
                if (!$assignment->expected_return_date) return true;
                $daysUntilDue = now()->diffInDays($assignment->expected_return_date, false);
                return $daysUntilDue > 7;
            })->sortBy('expected_return_date'),
        ];

        $assignmentsByAssigneeType = [
            'users' => $currentAssignments->filter(function($assignment) {
                return $assignment->assignable_type === 'App\\Models\\User';
            }),
            'departments' => $currentAssignments->filter(function($assignment) {
                return $assignment->assignable_type === 'App\\Models\\Department';
            }),
        ];

        $assignmentsByCategory = $currentAssignments->groupBy(function($assignment) {
            return $assignment->asset->category->name ?? 'Uncategorized';
        })->map(function($assignments, $categoryName) {
            return [
                'category_name' => $categoryName,
                'total_assignments' => $assignments->count(),
                'overdue_count' => $assignments->filter(function($assignment) {
                    return $assignment->isOverdue();
                })->count(),
                'assignments' => $assignments->sortBy('assigned_date'),
            ];
        })->sortByDesc('total_assignments');

        $assignmentsByLocation = $currentAssignments->groupBy(function($assignment) {
            return $assignment->asset->venue->name ?? 'No Location';
        })->map(function($assignments, $locationName) {
            return [
                'location_name' => $locationName,
                'total_assignments' => $assignments->count(),
                'overdue_count' => $assignments->filter(function($assignment) {
                    return $assignment->isOverdue();
                })->count(),
                'assignments' => $assignments->sortBy('assigned_date'),
            ];
        })->sortByDesc('total_assignments');

        $topAssignees = $currentAssignments->groupBy(function($assignment) {
            if ($assignment->assignable_type === 'App\\Models\\User') {
                return 'User: ' . ($assignment->assignable->firstname . ' ' . $assignment->assignable->lastname);
            } else {
                return 'Department: ' . ($assignment->assignable->name ?? 'Unknown');
            }
        })->map(function($assignments, $assigneeName) {
            $overdue = $assignments->filter(function($assignment) {
                return $assignment->isOverdue();
            });
            
            return [
                'assignee_name' => $assigneeName,
                'assignee_type' => explode(': ', $assigneeName)[0],
                'total_assignments' => $assignments->count(),
                'overdue_assignments' => $overdue->count(),
                'overdue_percentage' => $assignments->count() > 0 ? 
                    round(($overdue->count() / $assignments->count()) * 100, 1) : 0,
                'assignments' => $assignments->sortBy('assigned_date'),
            ];
        })->sortByDesc('total_assignments');

        $durationAnalysis = $currentAssignments->map(function($assignment) {
            $daysAssigned = $assignment->assigned_date->diffInDays(now());
            $isOverdue = $assignment->isOverdue();
            $overdueDays = 0;
            
            if ($isOverdue && $assignment->expected_return_date) {
                $overdueDays = $assignment->expected_return_date->diffInDays(now());
            }
            
            return [
                'assignment' => $assignment,
                'days_assigned' => $daysAssigned,
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
                'duration_category' => $this->getAssignmentDurationCategory($daysAssigned),
            ];
        });

        $durationCategories = [
            'short_term' => $durationAnalysis->filter(function($data) {
                return $data['days_assigned'] <= 30;
            })->count(),
            'medium_term' => $durationAnalysis->filter(function($data) {
                return $data['days_assigned'] > 30 && $data['days_assigned'] <= 90;
            })->count(),
            'long_term' => $durationAnalysis->filter(function($data) {
                return $data['days_assigned'] > 90 && $data['days_assigned'] <= 365;
            })->count(),
            'extended' => $durationAnalysis->filter(function($data) {
                return $data['days_assigned'] > 365;
            })->count(),
        ];

        $conditionConcerns = $currentAssignments->filter(function($assignment) {
            return in_array($assignment->condition_on_assignment, ['Fair', 'Poor']);
        })->sortBy(function($assignment) {
            $conditionPriority = ['Poor' => 1, 'Fair' => 2, 'Good' => 3, 'New' => 4];
            return $conditionPriority[$assignment->condition_on_assignment] ?? 5;
        });

        $recentAssignments = $currentAssignments->filter(function($assignment) {
            return $assignment->assigned_date->diffInDays(now()) <= 30;
        })->sortByDesc('assigned_date');

        $longTermAssignments = $currentAssignments->filter(function($assignment) {
            return $assignment->assigned_date->diffInDays(now()) > 365;
        })->sortBy('assigned_date');

        $summaryStats = [
            'assignment_metrics' => $assignmentMetrics,
            'duration_categories' => $durationCategories,
            'condition_concerns_count' => $conditionConcerns->count(),
            'recent_assignments_count' => $recentAssignments->count(),
            'long_term_assignments_count' => $longTermAssignments->count(),
            'users_with_assignments' => $assignmentsByAssigneeType['users']->groupBy('assignable_id')->count(),
            'departments_with_assignments' => $assignmentsByAssigneeType['departments']->groupBy('assignable_id')->count(),
            'categories_with_assignments' => $assignmentsByCategory->count(),
            'locations_with_assignments' => $assignmentsByLocation->count(),
        ];

        return view('assets.assignments.reports.assignments-report', compact(
            'currentAssignments',
            'assignmentMetrics',
            'assignmentsByStatus',
            'assignmentsByCategory',
            'assignmentsByLocation',
            'assignmentsByAssigneeType',
            'topAssignees',
            'durationAnalysis',
            'durationCategories',
            'conditionConcerns',
            'recentAssignments',
            'longTermAssignments',
            'summaryStats'
        ));
    }

    private function calculateAverageAssignmentDuration($assignments){
        if ($assignments->isEmpty()) return 0;
        $totalDays = $assignments->sum(function($assignment) {
            return $assignment->assigned_date->diffInDays(now());
        });
        return round($totalDays / $assignments->count(), 1);
    }

    private function getLongestAssignmentDuration($assignments){
        if ($assignments->isEmpty()) return 0;
        return $assignments->max(function($assignment) {
            return $assignment->assigned_date->diffInDays(now());
        });
    }

    private function getAssignmentDurationCategory($days){
        if ($days <= 30) return 'Short Term';
        if ($days <= 90) return 'Medium Term';
        if ($days <= 365) return 'Long Term';
        return 'Extended';
    }

    public function assignmentHistoryReport(Request $request){
        $startDate = $request->get('start_date') ? 
            Carbon::parse($request->get('start_date')) : 
            now()->subYear();
        $endDate = $request->get('end_date') ? 
            Carbon::parse($request->get('end_date')) : 
            now();

        $completedAssignments = AssetAssignment::with([
            'asset.category',
            'asset.venue', 
            'assignable',
            'assignedByUser',
            'receivedByUser'
        ])->where('status', 'Returned')
            ->whereNotNull('actual_return_date')
            ->whereBetween('actual_return_date', [$startDate, $endDate])
            ->orderBy('actual_return_date', 'desc')
            ->get();

        $historyMetrics = [
            'total_completed_assignments' => $completedAssignments->count(),
            'total_unique_assets' => $completedAssignments->pluck('asset_id')->unique()->count(),
            'total_unique_assignees' => $completedAssignments->map(function($assignment) {
                return $assignment->assignable_type . '-' . $assignment->assignable_id;
            })->unique()->count(),
            'average_assignment_duration' => $this->calculateHistoricalAverageDuration($completedAssignments),
            'shortest_assignment_days' => $this->getShortestAssignmentDuration($completedAssignments),
            'longest_assignment_days' => $this->getLongestAssignmentDuration($completedAssignments),
            'on_time_returns' => $completedAssignments->filter(function($assignment) {
                return !$assignment->expected_return_date || 
                    $assignment->actual_return_date <= $assignment->expected_return_date;
            })->count(),
            'late_returns' => $completedAssignments->filter(function($assignment) {
                return $assignment->expected_return_date && 
                    $assignment->actual_return_date > $assignment->expected_return_date;
            })->count(),
        ];

        $historyMetrics['on_time_percentage'] = $completedAssignments->count() > 0 ? 
            round(($historyMetrics['on_time_returns'] / $completedAssignments->count()) * 100, 1) : 0;

        $monthlyTrends = $completedAssignments->groupBy(function($assignment) {
            return $assignment->actual_return_date->format('Y-m');
        })->map(function($assignments, $month) {
            $onTimeCount = $assignments->filter(function($assignment) {
                return !$assignment->expected_return_date || 
                    $assignment->actual_return_date <= $assignment->expected_return_date;
            })->count();
            
            $avgDuration = $assignments->avg(function($assignment) {
                return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            });

            return [
                'month' => $month,
                'month_name' => Carbon::parse($month . '-01')->format('M Y'),
                'total_returns' => $assignments->count(),
                'on_time_returns' => $onTimeCount,
                'late_returns' => $assignments->count() - $onTimeCount,
                'on_time_percentage' => $assignments->count() > 0 ? 
                    round(($onTimeCount / $assignments->count()) * 100, 1) : 0,
                'average_duration' => round($avgDuration, 1),
            ];
        })->sortBy('month');

        $durationAnalysis = $completedAssignments->map(function($assignment) {
            $actualDuration = $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            $expectedDuration = $assignment->expected_return_date ? 
                $assignment->assigned_date->diffInDays($assignment->expected_return_date) : null;
            
            $isOverdue = $assignment->expected_return_date && 
                        $assignment->actual_return_date > $assignment->expected_return_date;
            $overdueDays = $isOverdue ? 
                $assignment->expected_return_date->diffInDays($assignment->actual_return_date) : 0;

            return [
                'assignment' => $assignment,
                'actual_duration' => $actualDuration,
                'expected_duration' => $expectedDuration,
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
                'duration_category' => $this->getAssignmentDurationCategory($actualDuration),
                'duration_variance' => $expectedDuration ? 
                    $actualDuration - $expectedDuration : null,
            ];
        });

        $durationCategories = [
            'short_term' => $durationAnalysis->filter(function($data) {
                return $data['actual_duration'] <= 30;
            })->count(),
            'medium_term' => $durationAnalysis->filter(function($data) {
                return $data['actual_duration'] > 30 && $data['actual_duration'] <= 90;
            })->count(),
            'long_term' => $durationAnalysis->filter(function($data) {
                return $data['actual_duration'] > 90 && $data['actual_duration'] <= 365;
            })->count(),
            'extended' => $durationAnalysis->filter(function($data) {
                return $data['actual_duration'] > 365;
            })->count(),
        ];

        $conditionAnalysis = $completedAssignments->map(function($assignment) {
            $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
            $assignmentCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
            $returnCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
            $conditionChange = $returnCondition - $assignmentCondition;
            
            return [
                'assignment' => $assignment,
                'condition_on_assignment' => $assignment->condition_on_assignment,
                'condition_on_return' => $assignment->condition_on_return,
                'condition_change' => $conditionChange,
                'condition_change_category' => $this->getConditionChangeCategory($conditionChange),
            ];
        });

        $conditionChangeStats = [
            'improved' => $conditionAnalysis->filter(function($data) {
                return $data['condition_change'] > 0;
            })->count(),
            'maintained' => $conditionAnalysis->filter(function($data) {
                return $data['condition_change'] === 0;
            })->count(),
            'deteriorated' => $conditionAnalysis->filter(function($data) {
                return $data['condition_change'] < 0;
            })->count(),
        ];

        $assigneePerformance = $completedAssignments->groupBy(function($assignment) {
            if ($assignment->assignable_type === 'App\\Models\\User') {
                return 'User: ' . ($assignment->assignable->firstname . ' ' . $assignment->assignable->lastname);
            } else {
                return 'Department: ' . ($assignment->assignable->name ?? 'Unknown');
            }
        })->map(function($assignments, $assigneeName) {
            $onTimeReturns = $assignments->filter(function($assignment) {
                return !$assignment->expected_return_date || 
                    $assignment->actual_return_date <= $assignment->expected_return_date;
            });

            $avgDuration = $assignments->avg(function($assignment) {
                return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            });

            $conditionDeteriorated = $assignments->filter(function($assignment) {
                $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
                $assignmentCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
                $returnCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
                return $returnCondition < $assignmentCondition;
            });

            return [
                'assignee_name' => $assigneeName,
                'assignee_type' => explode(': ', $assigneeName)[0],
                'total_assignments' => $assignments->count(),
                'on_time_returns' => $onTimeReturns->count(),
                'late_returns' => $assignments->count() - $onTimeReturns->count(),
                'on_time_percentage' => $assignments->count() > 0 ? 
                    round(($onTimeReturns->count() / $assignments->count()) * 100, 1) : 0,
                'average_duration' => round($avgDuration, 1),
                'condition_deteriorated_count' => $conditionDeteriorated->count(),
                'condition_care_score' => $assignments->count() > 0 ? 
                    round((($assignments->count() - $conditionDeteriorated->count()) / $assignments->count()) * 100, 1) : 100,
                'performance_rating' => $this->calculateAssigneePerformanceRating($onTimeReturns->count(), $assignments->count(), $conditionDeteriorated->count()),
            ];
        })->sortByDesc('total_assignments');

        $categoryPerformance = $completedAssignments->groupBy(function($assignment) {
            return $assignment->asset->category->name ?? 'Uncategorized';
        })->map(function($assignments, $categoryName) {
            $avgDuration = $assignments->avg(function($assignment) {
                return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            });

            $onTimeReturns = $assignments->filter(function($assignment) {
                return !$assignment->expected_return_date || 
                    $assignment->actual_return_date <= $assignment->expected_return_date;
            });

            $conditionDeteriorated = $assignments->filter(function($assignment) {
                $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
                $assignmentCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
                $returnCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
                return $returnCondition < $assignmentCondition;
            });

            return [
                'category_name' => $categoryName,
                'total_assignments' => $assignments->count(),
                'unique_assets' => $assignments->pluck('asset_id')->unique()->count(),
                'average_duration' => round($avgDuration, 1),
                'on_time_percentage' => $assignments->count() > 0 ? 
                    round(($onTimeReturns->count() / $assignments->count()) * 100, 1) : 0,
                'condition_deterioration_rate' => $assignments->count() > 0 ? 
                    round(($conditionDeteriorated->count() / $assignments->count()) * 100, 1) : 0,
                'total_days_utilized' => $assignments->sum(function($assignment) {
                    return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
                }),
            ];
        })->sortByDesc('total_assignments');

        $frequentlyAssignedAssets = $completedAssignments->groupBy('asset_id')->map(function($assignments, $assetId) {
            $asset = $assignments->first()->asset;
            $avgDuration = $assignments->avg(function($assignment) {
                return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
            });

            $conditionDeteriorated = $assignments->filter(function($assignment) {
                $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
                $assignmentCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
                $returnCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
                return $returnCondition < $assignmentCondition;
            });

            return [
                'asset' => $asset,
                'assignment_count' => $assignments->count(),
                'average_duration' => round($avgDuration, 1),
                'total_days_assigned' => $assignments->sum(function($assignment) {
                    return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
                }),
                'condition_deterioration_count' => $conditionDeteriorated->count(),
                'last_assignment_date' => $assignments->max('actual_return_date'),
                'utilization_score' => $this->calculateAssetUtilizationScore($assignments),
            ];
        })->sortByDesc('assignment_count')->take(20);

        $returnPatterns = [
            'early_returns' => $completedAssignments->filter(function($assignment) {
                return $assignment->expected_return_date && 
                    $assignment->actual_return_date < $assignment->expected_return_date;
            })->count(),
            'on_time_returns' => $completedAssignments->filter(function($assignment) {
                return $assignment->expected_return_date && 
                    $assignment->actual_return_date->equalTo($assignment->expected_return_date);
            })->count(),
            'late_returns' => $historyMetrics['late_returns'],
            'no_due_date_returns' => $completedAssignments->filter(function($assignment) {
                return !$assignment->expected_return_date;
            })->count(),
        ];

        $seasonalAnalysis = $completedAssignments->groupBy(function($assignment) {
            $month = $assignment->actual_return_date->month;
            if (in_array($month, [12, 1, 2])) return 'Winter';
            if (in_array($month, [3, 4, 5])) return 'Spring';
            if (in_array($month, [6, 7, 8])) return 'Summer';
            return 'Fall';
        })->map(function($assignments, $season) {
            return [
                'season' => $season,
                'total_returns' => $assignments->count(),
                'average_duration' => round($assignments->avg(function($assignment) {
                    return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
                }), 1),
            ];
        });

        $summaryStats = [
            'history_metrics' => $historyMetrics,
            'duration_categories' => $durationCategories,
            'condition_change_stats' => $conditionChangeStats,
            'return_patterns' => $returnPatterns,
            'date_range' => [
                'start' => $startDate->format('M d, Y'),
                'end' => $endDate->format('M d, Y'),
                'days' => $startDate->diffInDays($endDate),
            ],
            'top_performers_count' => $assigneePerformance->filter(function($assignee) {
                return $assignee['performance_rating'] === 'Excellent';
            })->count(),
            'needs_attention_count' => $assigneePerformance->filter(function($assignee) {
                return $assignee['performance_rating'] === 'Needs Attention';
            })->count(),
        ];

        return view('assets.assignments.reports.assignment-history-report', compact(
            'completedAssignments',
            'historyMetrics',
            'monthlyTrends',
            'durationAnalysis',
            'durationCategories',
            'conditionAnalysis',
            'conditionChangeStats',
            'assigneePerformance',
            'categoryPerformance',
            'frequentlyAssignedAssets',
            'returnPatterns',
            'seasonalAnalysis',
            'summaryStats',
            'startDate',
            'endDate'
        ));
    }

    private function calculateHistoricalAverageDuration($assignments){
        if ($assignments->isEmpty()) return 0;
        
        $totalDays = $assignments->sum(function($assignment) {
            return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
        });
        
        return round($totalDays / $assignments->count(), 1);
    }

    private function getShortestAssignmentDuration($assignments){
        if ($assignments->isEmpty()) return 0;
        
        return $assignments->min(function($assignment) {
            return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
        });
    }

    private function getConditionChangeCategory($change){
        if ($change > 0) return 'Improved';
        if ($change < 0) return 'Deteriorated';
        return 'Maintained';
    }

    private function calculateAssigneePerformanceRating($onTimeReturns, $totalAssignments, $conditionDeteriorated){
        $onTimeRate = $totalAssignments > 0 ? ($onTimeReturns / $totalAssignments) * 100 : 0;
        $conditionCareRate = $totalAssignments > 0 ? (($totalAssignments - $conditionDeteriorated) / $totalAssignments) * 100 : 100;
        
        $overallScore = ($onTimeRate + $conditionCareRate) / 2;
        
        if ($overallScore >= 90) return 'Excellent';
        if ($overallScore >= 80) return 'Good';
        if ($overallScore >= 70) return 'Average';
        if ($overallScore >= 60) return 'Below Average';
        return 'Needs Attention';
    }

    private function calculateAssetUtilizationScore($assignments){
        $avgDuration = $assignments->avg(function($assignment) {
            return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
        });
        
        $assignmentFrequency = $assignments->count();
        return round(($assignmentFrequency * 10) + ($avgDuration / 10), 1);
    }

    public function assignmentsByUserReport(Request $request){
        $startDate = $request->get('start_date') ? 
            Carbon::parse($request->get('start_date')) : 
            now()->subMonths(6);
        $endDate = $request->get('end_date') ? 
            Carbon::parse($request->get('end_date')) : 
            now();

        $usersWithAssignments = User::whereHas('assetAssignments', function($query) use ($startDate, $endDate) {
            $query->where(function($q) use ($startDate, $endDate) {
                $q->where('status', 'Assigned')
                ->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->where('status', 'Returned')
                        ->whereNotNull('actual_return_date')
                        ->whereBetween('actual_return_date', [$startDate, $endDate]);
                });
            });
        })
        ->with([
            'assetAssignments.asset.category',
            'assetAssignments.asset.venue',
            'assetAssignments.assignedByUser',
            'assetAssignments.receivedByUser'
        ])
        ->orderBy('lastname')
        ->orderBy('firstname')
        ->get();

        $userAnalysis = $usersWithAssignments->map(function($user) use ($startDate, $endDate) {
            $allAssignments = $user->assetAssignments->filter(function($assignment) use ($startDate, $endDate) {
                return $assignment->status === 'Assigned' || 
                    ($assignment->status === 'Returned' && 
                        $assignment->actual_return_date && 
                        $assignment->actual_return_date->between($startDate, $endDate));
            });

            $currentAssignments = $allAssignments->where('status', 'Assigned');
            $completedAssignments = $allAssignments->where('status', 'Returned');

            $totalAssignments = $allAssignments->count();
            $currentCount = $currentAssignments->count();
            $completedCount = $completedAssignments->count();

            $onTimeReturns = $completedAssignments->filter(function($assignment) {
                return !$assignment->expected_return_date || 
                    $assignment->actual_return_date <= $assignment->expected_return_date;
            });

            $lateReturns = $completedAssignments->filter(function($assignment) {
                return $assignment->expected_return_date && 
                    $assignment->actual_return_date > $assignment->expected_return_date;
            });

            $overdueCurrent = $currentAssignments->filter(function($assignment) {
                return $assignment->isOverdue();
            });

            $avgCompletedDuration = $completedAssignments->count() > 0 ? 
                $completedAssignments->avg(function($assignment) {
                    return $assignment->assigned_date->diffInDays($assignment->actual_return_date);
                }) : 0;

            $avgCurrentDuration = $currentAssignments->count() > 0 ?
                $currentAssignments->avg(function($assignment) {
                    return $assignment->assigned_date->diffInDays(now());
                }) : 0;

            $conditionImproved = $completedAssignments->filter(function($assignment) {
                $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
                $assignmentCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
                $returnCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
                return $returnCondition > $assignmentCondition;
            });

            $conditionDeteriorated = $completedAssignments->filter(function($assignment) {
                $conditionValues = ['New' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];
                $assignmentCondition = $conditionValues[$assignment->condition_on_assignment] ?? 0;
                $returnCondition = $conditionValues[$assignment->condition_on_return] ?? 0;
                return $returnCondition < $assignmentCondition;
            });

            $uniqueAssets = $allAssignments->pluck('asset_id')->unique();
            $uniqueCategories = $allAssignments->pluck('asset.category.name')->filter()->unique();

            $recentActivity = $allAssignments->filter(function($assignment) {
                return $assignment->assigned_date->diffInDays(now()) <= 30 ||
                    ($assignment->actual_return_date && $assignment->actual_return_date->diffInDays(now()) <= 30);
            })->count();

            return [
                'user' => $user,
                'total_assignments' => $totalAssignments,
                'current_assignments' => $currentCount,
                'completed_assignments' => $completedCount,
                'on_time_returns' => $onTimeReturns->count(),
                'late_returns' => $lateReturns->count(),
                'overdue_current' => $overdueCurrent->count(),
                'no_due_date_count' => $currentAssignments->filter(function($a) { return !$a->expected_return_date; })->count(),
                'on_time_percentage' => $completedCount > 0 ? round(($onTimeReturns->count() / $completedCount) * 100, 1) : 0,
                'overdue_percentage' => $currentCount > 0 ? round(($overdueCurrent->count() / $currentCount) * 100, 1) : 0,
                'avg_completed_duration' => round($avgCompletedDuration, 1),
                'avg_current_duration' => round($avgCurrentDuration, 1),
                'condition_improved' => $conditionImproved->count(),
                'condition_deteriorated' => $conditionDeteriorated->count(),
                'condition_care_percentage' => $completedCount > 0 ? 
                    round((($completedCount - $conditionDeteriorated->count()) / $completedCount) * 100, 1) : 100,
                'unique_assets' => $uniqueAssets->count(),
                'unique_categories' => $uniqueCategories->count(),
                'recent_activity' => $recentActivity,
                'assignments_collection' => $allAssignments,
                'current_assignments_collection' => $currentAssignments,
                'completed_assignments_collection' => $completedAssignments,
            ];
        })->sortByDesc('total_assignments');

        $overallStats = [
            'total_users_with_assignments' => $userAnalysis->count(),
            'total_active_users' => $userAnalysis->where('current_assignments', '>', 0)->count(),
            'users_with_overdue' => $userAnalysis->where('overdue_current', '>', 0)->count(),
            'users_with_perfect_record' => $userAnalysis->filter(function($user) {
                return $user['completed_assignments'] > 0 && $user['on_time_percentage'] == 100 && $user['condition_deteriorated'] == 0;
            })->count(),
            'total_current_assignments' => $userAnalysis->sum('current_assignments'),
            'total_completed_assignments' => $userAnalysis->sum('completed_assignments'),
            'overall_on_time_rate' => $userAnalysis->sum('completed_assignments') > 0 ? 
                round(($userAnalysis->sum('on_time_returns') / $userAnalysis->sum('completed_assignments')) * 100, 1) : 0,
            'users_with_recent_activity' => $userAnalysis->where('recent_activity', '>', 0)->count(),
        ];

        $topUsers = [
            'most_assignments' => $userAnalysis->sortByDesc('total_assignments')->take(10),
            'best_on_time_rate' => $userAnalysis->filter(function($user) {
                return $user['completed_assignments'] >= 3; // Minimum 3 completed assignments
            })->sortByDesc('on_time_percentage')->take(10),
            'most_current_assignments' => $userAnalysis->sortByDesc('current_assignments')->take(10),
            'most_asset_variety' => $userAnalysis->sortByDesc('unique_assets')->take(10),
        ];

        $usersNeedingAttention = [
            'with_overdue' => $userAnalysis->where('overdue_current', '>', 0)->sortByDesc('overdue_current'),
            'frequent_late_returns' => $userAnalysis->filter(function($user) {
                return $user['completed_assignments'] >= 3 && $user['on_time_percentage'] < 70;
            })->sortBy('on_time_percentage'),
            'condition_deterioration' => $userAnalysis->where('condition_deteriorated', '>', 0)->sortByDesc('condition_deteriorated'),
        ];

        $workloadDistribution = [
            'no_assignments' => User::whereDoesntHave('assetAssignments', function($query) {
                $query->where('status', 'Assigned');
            })->count(),
            'light_workload' => $userAnalysis->whereBetween('current_assignments', [1, 2])->count(),
            'moderate_workload' => $userAnalysis->whereBetween('current_assignments', [3, 5])->count(),
            'heavy_workload' => $userAnalysis->whereBetween('current_assignments', [6, 10])->count(),
            'very_heavy_workload' => $userAnalysis->where('current_assignments', '>', 10)->count(),
        ];

        $departmentAnalysis = $userAnalysis->groupBy(function($userData) {
            return $userData['user']->department ?? 'No Department';
        })->map(function($departmentUsers, $departmentName) {
            $totalUsers = $departmentUsers->count();
            $activeUsers = $departmentUsers->where('current_assignments', '>', 0)->count();
            $totalCurrentAssignments = $departmentUsers->sum('current_assignments');
            $totalCompletedAssignments = $departmentUsers->sum('completed_assignments');
            $onTimeReturns = $departmentUsers->sum('on_time_returns');

            return [
                'department_name' => $departmentName,
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_current_assignments' => $totalCurrentAssignments,
                'total_completed_assignments' => $totalCompletedAssignments,
                'department_on_time_rate' => $totalCompletedAssignments > 0 ? 
                    round(($onTimeReturns / $totalCompletedAssignments) * 100, 1) : 0,
                'users_with_overdue' => $departmentUsers->where('overdue_current', '>', 0)->count(),
                'avg_assignments_per_user' => $totalUsers > 0 ? round($totalCurrentAssignments / $totalUsers, 1) : 0,
            ];
        })->sortByDesc('total_current_assignments');

        $monthlyActivity = $userAnalysis->flatMap(function($userData) {
            return $userData['assignments_collection']->map(function($assignment) {
                return [
                    'month' => $assignment->assigned_date->format('Y-m'),
                    'user_id' => $assignment->assignable_id,
                ];
            });
        })->groupBy('month')->map(function($monthData, $month) {
            return [
                'month' => $month,
                'month_name' => Carbon::parse($month . '-01')->format('M Y'),
                'total_assignments' => $monthData->count(),
                'unique_users' => $monthData->pluck('user_id')->unique()->count(),
            ];
        })->sortBy('month');

        return view('assets.assignments.reports.assignments-by-user-report', compact(
            'userAnalysis',
            'overallStats',
            'topUsers',
            'usersNeedingAttention',
            'workloadDistribution',
            'departmentAnalysis',
            'monthlyActivity',
            'startDate',
            'endDate'
        ));
    }


}
