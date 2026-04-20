<?php

namespace App\Helpers;

use App\Models\Admission;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookAllocation;
use App\Models\CommentBank;
use App\Models\ConditionSet;
use App\Models\ConditionSetCondition;
use App\Models\Department;
use App\Models\FeeCondition;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Nationality;
use App\Models\Notification;
use App\Models\OptionalSubject;
use App\Models\Publisher;
use App\Models\SchoolSetup;
use App\Models\ScoreComment;
use App\Models\Sponsor;
use App\Models\SponsorFilter;
use App\Models\Student;
use App\Models\StudentFilter;
use App\Models\StudentType;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserFilter;
use App\Models\Venue;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CacheHelper {
    public $cacheDuration = 3600;


    public static function getDashboardUsers() {
        return Cache::remember('users', now()->addHours(24), function () {
            return User::with('roles:id,name')->select('id', 'firstname', 'lastname', 'email', 'department', 'status', 'area_of_work')->get();
        });
    }

    public static function getDashboardNotifications($user) {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $cacheKey = 'notifications_dashboard.' . $selectedTermId;
    
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user, $selectedTermId) {
            $query = Notification::query()
                ->with([
                    'recipients' => function ($query) {
                        $query->select('users.id', 'firstname', 'lastname', 'email')->withTrashed();
                    },
                    'notificationComments' => function ($query) {
                        $query->latest()->take(5)->select('id', 'notification_id', 'user_id', 'body', 'created_at');
                    },
                    'notificationComments.user:id,firstname,lastname',
                    'attachments:id,notification_id,original_name,file_path',
                    'department:id,name',
                    'user:id,firstname,lastname'
                ])
                ->select([
                    'notifications.id', 'term_id', 'user_id', 'title', 'body',
                    'is_general', 'department_id', 'area_of_work', 'allow_comments',
                    'is_pinned', 'start_date', 'end_date', 'created_at', 'updated_at'
                ])
                ->where('term_id', $selectedTermId)
                ->where(function ($query) {
                    $now = now();
                    $query->where(function ($q) use ($now) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', $now);
                    })->where(function ($q) use ($now) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $now);
                    });
                })->whereDoesntHave('sponsorRecipients')->when(!$user->hasRoles('Administrator'), function ($query) use ($user) {
                    $query->where(function ($q) use ($user) {
                        $q->where('is_general', true)
                          ->orWhere('department_id', $user->department_id)
                          ->orWhere('area_of_work', $user->area_of_work)
                          ->orWhereHas('recipients', function ($q) use ($user) {
                              $q->where('user_id', $user->id);
                          });
                    });
                })->orderByDesc('is_pinned')->latest('created_at')->take(10);
    
            return $query->get()->map(function ($notification) {
                return self::formatNotification($notification);
            });
        });
    }
    

    private static function formatNotification($notification) {
        return ['id'                                                                                          => $notification->id,
                'title'                                                                                       => $notification->title,
                'body'                                                                                        => $notification->body,
                'is_general'                                                                                  => $notification->is_general,
                'is_pinned'                                                                                   => $notification->is_pinned,
                'department'                                                                                  => $notification->department ? ['id'   => $notification->department->id,
                                                                                                                                              'name' => $notification->department->name] : null,
                'area_of_work'                                                                                => $notification->area_of_work,
                'dates'                                                                                       => ['start'         => optional($notification->start_date)->format('Y-m-d'),
                                                                                                                  'end'           => optional($notification->end_date)->format('Y-m-d'),
                                                                                                                  'created'       => $notification->created_at->format('Y-m-d H:i:s'),
                                                                                                                  'updated'       => $notification->updated_at->format('Y-m-d H:i:s'),
                                                                                                                  'created_human' => $notification->created_at->diffForHumans(),],
                'creator'                                                                                     => ['id'   => $notification->user->id,
                                                                                                                  'name' => $notification->user->full_name,],
                'allow_comments'                                                                              => $notification->allow_comments,
                'comments'                                                                                    => $notification->notificationComments->take(5)->map(function ($comment) {
                    return ['id'         => $comment->id, 'comment' => $comment->comment,
                            'user'       => ['id' => $comment->user->id, 'name' => $comment->user->full_name,],
                            'created_at' => $comment->created_at->diffForHumans()];
                })->toArray(),
                'attachments'                                                                                 => $notification->attachments->map(function ($attachment) {
                    return ['id'   => $attachment->id, 'filename' => $attachment->filename,
                            'path' => $attachment->file_path,];
                })->toArray(),
                'recipients'                                                                                  => $notification->recipients->map(function ($recipient) {
                    return ['id' => $recipient->id, 'name' => $recipient->full_name, 'email' => $recipient->email,];
                })->toArray(),];
    }

    public static function forgetDashboardNotifications() {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $cacheKey = 'notifications_dashboard.' . $selectedTermId;
        Cache::forget($cacheKey);
    }

    public static function getStudentData($selectedTermId) {
        $cacheKey = 'student_dashboard.' . $selectedTermId;

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($selectedTermId) {
            $count = DB::table('student_term')->where('term_id', $selectedTermId)->where('status', 'Current')->count();

            if ($count === 0) {
                return collect([]);
            }

            return Student::select('gender', 'klasses.grade_id', DB::raw('COUNT(DISTINCT students.id) as student_count'))->join('student_term', 'students.id', '=', 'student_term.student_id')->join('klasses', 'student_term.grade_id', '=', 'klasses.grade_id')->where('student_term.term_id', $selectedTermId)->where('student_term.status', 'Current')->groupBy('klasses.grade_id', 'students.gender')->get();
        });
    }

    public static function forgetStudentsDashboard($selectedTermId) {
        $cacheKey = 'students_dashboard' . $selectedTermId;
        Cache::forget($cacheKey);
    }

    public static function getDashboardGrades($schoolType, ?int $selectedTermId = null) {
        $resolver = app(\App\Services\SchoolModeResolver::class);
        $mode = \App\Models\SchoolSetup::normalizeType($schoolType) ?? \App\Models\SchoolSetup::TYPE_JUNIOR;
        $filter = $resolver->selectedLevelFilter($mode);
        $resolvedTermId = $selectedTermId ?? session('selected_term_id') ?? optional(TermHelper::getCurrentTerm())->id;
        $cacheKey = 'grades_dashboard:' . $mode . ':' . $filter . ':' . ($resolvedTermId ?? 'none');

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($resolver, $mode, $resolvedTermId) {
            $query = Grade::query()
                ->where('active', true)
                ->whereIn('name', $resolver->dashboardGradeNames($mode))
                ->orderBy('sequence')
                ->orderBy('id');

            if ($resolvedTermId) {
                $query->where('term_id', $resolvedTermId);
            }

            return $query->get()
                ->unique('name')
                ->values();
        });
    }

    public static function getNationalities() {
        return Cache::remember('nationalities', now()->addHours(24), function () {
            try {
                $nationalities = Nationality::select('name')->get();
                return $nationalities;
            } catch (QueryException $e) {
                Log::error('Nationalities could not be retrieved: ' . $e->getMessage(), [$e]);
            }
        });
    }

    public static function forgetNationalities() {
        Cache::forget('nationalities');
    }

    public static function getCurrentTermStudents($term) {
        return Cache::remember('students_by_term', now()->addDays(30), function () use ($term) {
            return Student::where('term_id', $term->id)->where('status', 'Current')->get();
        });
    }

    public static function forgetCurrentTermStudents() {
        Cache::forget('students_by_term');
    }

    public static function getStudentsInTerm($selectedTermId) {
        return Cache::remember('students_in_term' . $selectedTermId, now()->addHours(24), function () use ($selectedTermId) {
            return Student::whereHas('terms', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('status', 'Current');
            })->with(['terms' => function ($query) use ($selectedTermId) {
                $query->where('id', $selectedTermId)->withPivot('year', 'status');
            }])->where('status', 'Current')->get();
        });
    }

    public static function forgetStudentsInTerm() {
        Cache::forget('students_in_term');
    }

    public static function getStudentsTermData1() {
        $user = Auth::user();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $userRoles = $user->roles->pluck('name')->sort()->implode('_');
        $userRolesHash = md5($userRoles);

        $version = self::getCacheVersion($selectedTermId);

        $cacheKey = "students_term_data_term_{$selectedTermId}_version_{$version}_roles_{$userRolesHash}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($user, $selectedTermId) {
            $studentsQuery = Student::query()->with(['currentClassRelation', 'terms'])->where('status', 'Current');
            if ($user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Students Admin',
                'Students Edit', 'Student View'])) {
                return $studentsQuery->get();
            } elseif ($user->hasRoles('Class Teacher')) {
                $klassIds = $user->klass()->pluck('id')->toArray();

                if (!empty($klassIds)) {
                    return $studentsQuery->whereHas('currentClassRelation', function ($query) use ($klassIds) {
                        $query->whereIn('klass_id', $klassIds);
                    })->get();
                } else {
                    return collect();
                }
            } else {
                return collect();
            }
        });
    }

    public static function getStudentsTermData(){
        $user = Auth::user();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $cacheKey = "filtered_students_data_term_{$selectedTermId}_user_{$user->id}";
    
        return Cache::remember($cacheKey, now()->addHours(2), function () use ($user, $selectedTermId) {
            $query = Student::with(['currentClassRelation', 'terms'])
                ->whereHas('terms', function ($q) use ($selectedTermId) {
                    $q->where('terms.id', $selectedTermId);
                });
    
            if ($user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin','Academic Edit', 'Students Admin', 'Students Edit', 'Student View'])) {
            } elseif ($user->hasRoles('Class Teacher') || Klass::where('user_id', $user->id)->where('term_id', $selectedTermId)->exists()) {
                $klassIds = $user->klass()->where('term_id', $selectedTermId)->pluck('id')->toArray();

                if (empty($klassIds)) {
                    return [
                        'students' => collect(),
                        'studentsWithNoClasses' => 0,
                        'studentsWithNoClassesCollection' => collect(),
                        'duplicateStudents' => collect()
                    ];
                }

                $query->whereHas('currentClassRelation', function ($subQ) use ($klassIds) {
                    $subQ->whereIn('klass_id', $klassIds);
                });
            } elseif (KlassSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists()
                   || OptionalSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists()) {
                // Subject teacher: get students from core subjects they teach
                $coreStudentIds = KlassSubject::where('term_id', $selectedTermId)
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id);
                    })
                    ->with(['klass.students' => function ($q) use ($selectedTermId) {
                        $q->wherePivot('term_id', $selectedTermId);
                    }])
                    ->get()
                    ->pluck('klass.students')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();

                // Get students from optional subjects they teach
                $optStudentIds = OptionalSubject::where('term_id', $selectedTermId)
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id);
                    })
                    ->with('students')
                    ->get()
                    ->pluck('students')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();

                $allStudentIds = array_unique(array_merge($coreStudentIds, $optStudentIds));

                if (empty($allStudentIds)) {
                    return [
                        'students' => collect(),
                        'studentsWithNoClasses' => 0,
                        'studentsWithNoClassesCollection' => collect(),
                        'duplicateStudents' => collect()
                    ];
                }

                $query->whereIn('id', $allStudentIds);
            } else {
                return [
                    'students' => collect(),
                    'studentsWithNoClasses' => 0,
                    'studentsWithNoClassesCollection' => collect(),
                    'duplicateStudents' => collect()
                ];
            }
    
            $status = request('status');
            if ($status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            } else {
                $query->where('status', 'Current');
            }
    
            if ($gender = request('gender')) {
                $query->where('gender', $gender);
            }
    
            if ($class = request('class')) {
                $query->whereHas('currentClassRelation', function ($q) use ($class) {
                    $q->where('name', $class);
                });
            }
    
            if ($grade = request('grade')) {
                $query->whereHas('currentGrade', function ($q) use ($grade) {
                    $q->where('name', $grade);
                });
            }
            
            $students = $query->get();
            $studentsWithNoClassesQuery = (clone $query)->whereDoesntHave('currentClassRelation');
            $studentsWithNoClasses = $studentsWithNoClassesQuery->count();
            $studentsWithNoClassesCollection = $studentsWithNoClassesQuery->get();
            
            $duplicateQuery = Student::select('first_name', 'last_name', DB::raw('COUNT(*) as count'))
                ->whereNull('deleted_at')
                ->whereHas('terms', function ($q) use ($selectedTermId) {
                    $q->where('terms.id', $selectedTermId);
                })
                ->groupBy('first_name', 'last_name')
                ->having('count', '>', 1);
            
            $potentialDuplicates = $duplicateQuery->get();
            
            $duplicateStudents = collect();
            foreach ($potentialDuplicates as $dup) {
                $match = Student::where('first_name', $dup->first_name)
                    ->where('last_name', $dup->last_name)
                    ->whereHas('terms', function ($q) use ($selectedTermId) {
                        $q->where('terms.id', $selectedTermId);
                    })
                    ->get();
                
                $duplicateStudents = $duplicateStudents->merge($match);
            }
            
            return [
                'students' => $students,
                'studentsWithNoClasses' => $studentsWithNoClasses, 
                'studentsWithNoClassesCollection' => $studentsWithNoClassesCollection,
                'duplicateStudents' => $duplicateStudents
            ];
        });
    }

    protected static function getCacheVersion($selectedTermId) {
        $versionKey = "students_term_data_version_{$selectedTermId}";
        return Cache::rememberForever($versionKey, function () {
            return 1;
        });
    }

    public static function forgetStudentsTermData(){
        $user = Auth::user();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $cacheKey = "filtered_students_data_term_{$selectedTermId}_user_{$user->id}";
        
        Cache::forget($cacheKey);
    }
    
    public static function getSponsorsData() {
        $user = Auth::user();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $fullAccessRoles = ['Administrator', 'HOD', 'SMS Admin', 'Academic Admin', 'Sponsors Admin', 'Sponsors Edit',
            'Sponsors View'];

        $version = self::getSponsorsCacheVersion();

        if ($user->hasAnyRoles($fullAccessRoles)) {
            $cacheKey = 'sponsors_full_access_version_' . $version;
            return Cache::remember($cacheKey, now()->addHours(24), function () {
                return Sponsor::where('status', 'Current')->get();
            });
        } elseif ($user->hasRoles('Class Teacher') || Klass::where('user_id', $user->id)->where('term_id', $selectedTermId)->exists()) {
            $klassIds = $user->klass()->where('term_id', $selectedTermId)->pluck('id')->toArray();

            if (empty($klassIds)) {
                return collect();
            }

            sort($klassIds);
            $klassIdsKeyPart = implode('_', $klassIds);
            $cacheKey = 'sponsors_class_teacher_' . $klassIdsKeyPart . '_version_' . $version;

            return Cache::remember($cacheKey, now()->addHours(24), function () use ($klassIds) {
                return Sponsor::whereHas('students', function ($query) use ($klassIds) {
                    $query->whereHas('currentClassRelation', function ($subQuery) use ($klassIds) {
                        $subQuery->whereIn('klass_id', $klassIds);
                    });
                })->with(['students' => function ($query) use ($klassIds) {
                    $query->whereHas('currentClassRelation', function ($subQuery) use ($klassIds) {
                        $subQuery->whereIn('klass_id', $klassIds);
                    })->select('id', 'sponsor_id', 'first_name', 'last_name');
                }])->where('status', 'Current')->get();
            });
        } elseif (KlassSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists()
               || OptionalSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists()) {
            // Subject teacher: get students from core subjects they teach
            $coreStudentIds = KlassSubject::where('term_id', $selectedTermId)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id);
                })
                ->with(['klass.students' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                }])
                ->get()
                ->pluck('klass.students')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->toArray();

            // Get students from optional subjects they teach
            $optStudentIds = OptionalSubject::where('term_id', $selectedTermId)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id);
                })
                ->with('students')
                ->get()
                ->pluck('students')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->toArray();

            $allStudentIds = array_unique(array_merge($coreStudentIds, $optStudentIds));

            if (empty($allStudentIds)) {
                return collect();
            }

            sort($allStudentIds);
            $cacheKey = 'sponsors_teacher_' . md5(implode('_', $allStudentIds)) . '_version_' . $version;

            return Cache::remember($cacheKey, now()->addHours(24), function () use ($allStudentIds) {
                return Sponsor::whereHas('students', function ($query) use ($allStudentIds) {
                    $query->whereIn('students.id', $allStudentIds);
                })->with(['students' => function ($query) use ($allStudentIds) {
                    $query->whereIn('students.id', $allStudentIds)
                          ->select('id', 'sponsor_id', 'first_name', 'last_name');
                }])->where('status', 'Current')->get();
            });
        } else {
            return collect();
        }
    }

    protected static function getSponsorsCacheVersion() {
        $versionKey = "sponsors_data_version";
        return Cache::rememberForever($versionKey, function () {
            return 1;
        });
    }

    public static function forgetSponsorsData() {
        $versionKey = "sponsors_data_version";
        $currentVersion = Cache::get($versionKey, 1);
        Cache::forever($versionKey, $currentVersion + 1);
    }

    public static function getAdmissions() {
        return Cache::remember('admissions', now()->addHours(24), function () {
            return Admission::all();
        });
    }

    public static function forgetAdmissions() {
        Cache::forget('admissions');
    }

    public static function getCurrentTermAdmissions($term) {
        return Cache::remember('admissions_by_term', now()->addHours(24), function () use ($term) {
            return Admission::where('term_id', $term->id)->get();
        });
    }

    public static function forgetCurrentTermAdmissions() {
        Cache::forget('admissions_by_term');
    }

    public static function getNotifications() {
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $cacheKey = 'notifications_by_' . $termId;
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($termId) {
            return Notification::where('term_id', $termId)->get();
        });
    }

    public static function forgetNotifications() {
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $cacheKey = 'notifications_by_' . $termId;
        return Cache::forget($cacheKey);
    }

    public static function getTerms() {
        return Cache::remember('terms', 60 * 100, function () {
            return Term::all();
        });
    }

    public static function forgetTerms() {
        Cache::forget('terms');
    }

    public static function getGrades() {
        return Cache::remember('grades', now()->addHours(24), function (){
            return Grade::where('active', 1)->get();
        });
    }

    public static function forgetGrades() {
        Cache::forget('grades');
    }

    public static function getCommentBank() {
        return Cache::remember('comments_bank', now()->addHours(24), function () {
            return CommentBank::all();
        });
    }

    public static function forgetCommentBank() {
        Cache::forget('comments_bank');
        Cache::forget('comments_bank_type_0');
        Cache::forget('comments_bank_type_1');
    }

    public static function getSubjectMasterList($level = null) {
        return Cache::remember('master_subject_list', 24 * 60, function () use ($level) {
            if ($level) {
                return Subject::where('level', $level)->get();
            }
            return Subject::all();
        });
    }

    public static function forgetSubjectMasterList() {
        Cache::forget('master_subject_list');
    }

    public static function getAllVenues() {
        return Cache::remember('venues', now()->addHours(24), function () {
            return Venue::latest('id')->get();
        });
    }

    public static function forgetAllVenues() {
        Cache::forget('venues');
    }

    public static function getDepartments() {
        return Cache::remember('departments', now()->addHours(24), function () {
            return Department::all();
        });
    }

    public static function forgetDepartments() {
        Cache::forget('departments');
    }

    public static function getSubjectsComments() {
        return Cache::remember('subject_comments', now()->addHours(24), function () {
            return ScoreComment::all();
        });
    }

    public static function forgetSubjectComments() {
        Cache::forget('subject_comments');
    }

    public static function getUsers() {
        return Cache::remember('users', now()->addHours(24), function () {
            return User::where('status', 'Current')->get();
        });
    }

    public static function forgetUsers() {
        Cache::forget('users');
    }

    public static function getStaff(){
        $cacheKey = 'hr_users_table_data';
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            return User::query()->select([
                    'id', 
                    'firstname', 
                    'lastname', 
                    'avatar', 
                    'gender', 
                    'email', 
                    'date_of_birth',
                    'id_number', 
                    'position', 
                    'phone',
                    'department',
                    'status'
                ])->where('status', 'Current')->get();
        });
    }

    public static function forgetStaff() {
        $cacheKey = 'hr_users_table_data';
        Cache::forget($cacheKey);
    }

    public static function getSponsors() {
        return Cache::remember('sponsors', now()->addHours(24), function () {
            return Sponsor::where('status', 'Current')->get();
        });
    }

    public static function forgetSponsors() {
        Cache::forget('sponsors');
    }

    public static function getStudentsData() {
        Cache::forget('students_term_data');
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        return Cache::remember('students_term_data', now()->addHours(24), function () use ($selectedTermId) {
            return Student::inTermWithActiveGrade($selectedTermId)->get();
        });
    }

    public static function forgetStudentsData() {
        Cache::forget('students_term_data');
    }

    public static function getStudentsTermCount() {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        Log::info($selectedTermId);
        $cacheKey = 'students_term_count_term_' . $selectedTermId;
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($selectedTermId) {
            return Student::whereDoesntHave('classes', function ($query) use ($selectedTermId) {
                $query->where('klass_student.term_id', $selectedTermId);
            })->whereHas('terms', function ($query) use ($selectedTermId) {
                $query->where('student_term.term_id', $selectedTermId)->where('student_term.status', 'Current');
            })->count();
        });
    }

    public static function forgetStudentsCount($selectedTermId) {
        $cacheKey = 'students_term_count_term_' . $selectedTermId;
        Cache::forget($cacheKey);
    }

    public static function getStudentFilters() {
        $student_filters = Cache::remember('student_filters', now()->addHours(24), function () {
            return StudentFilter::query()->select(['id', 'name', 'created_at'])->orderBy('name')->get();
        });
        return $student_filters;
    }

    public static function forgetStudentFilters() {
        Cache::forget('student_filters');
    }

    public static function getStudentTypes() {
        $types = Cache::remember('types', now()->addHours(24), function () {
            return StudentType::query()->select(['id', 'type', 'exempt', 'description', 'color',
                'created_at'])->orderBy('type')->get();
        });
        return $types;
    }

    public static function forgetStudentTypes() {
        Cache::forget('types');
    }

    public static function getConditionSets() {
        return Cache::remember('condition_sets', 60, function () {
            return ConditionSet::all();
        });
    }

    public static function forgetConditionSets() {
        Cache::forget('condition_sets');
    }

    public static function getConditionSetConditions() {
        return Cache::remember('condition_set_conditions', 60, function () {
            return ConditionSetCondition::all();
        });
    }

    public static function forgeConditionSetConditions() {
        Cache::forget('condition_set_conditions');
    }

    public static function getBookAllocations($startDate = null, $endDate = null) {
        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $cacheKey = 'book_allocations_report';
        } else {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
            $cacheKey = 'book_allocations_report_current_year';
        }

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate, $endDate) {
            return BookAllocation::with(['student:id,first_name,last_name', 'student.currentClassRelation:id,name',
                'copy:id,book_id,accession_number',
                'copy.book:id,title',])->select('id', 'student_id', 'copy_id', 'allocation_date', 'due_date', 'return_date')->whereBetween('allocation_date', [$startDate,
                $endDate])->get();
        });
    }


    public static function forgetBookAllocations() {
        Cache::forget('book_allocations_report');
    }

    public static function getBooksWithCopiesReport() {
        $books = Cache::remember('books_with_copies_report', now()->addMinutes(30), function () {
            return Book::with(['copies' => function ($query) {
                $query->select('id', 'book_id', 'accession_number', 'status')->orderBy('accession_number');
            }])->select('id', 'title')->orderBy('title')->get();
        });

        return $books;
    }

    public static function forgetBookWithCopies() {
        Cache::forget('books_with_copies_report');
    }

    public static function getTextBooks() {
        $textbooks = Cache::remember('books', now()->addHours(24), function () {
            return Book::with(['author:id,first_name,last_name', 'publisher:id,name'])->select(['id', 'title', 'isbn',
                'author_id', 'publisher_id', 'genre', 'status', 'created_at'])->orderBy('title')->get();
        });
        return $textbooks;
    }

    public static function getPublishers() {
        $publishers = Cache::remember('publishers', now()->addHours(24), function () {
            return Publisher::select(['id', 'name'])->orderBy('name')->get();
        });
        return $publishers;
    }


    public static function getAuthors() {
        $authors = Cache::remember('authors', now()->addHours(24), function () {
            return Author::select(['id', 'first_name', 'last_name'])->withCount('books')->orderBy('first_name')->get();
        });
        return $authors;
    }

    public static function forgetTextBooks() {
        Cache::forget('books');
    }

    public static function forgetPublishers() {
        Cache::forget('publishers');
    }

    public static function forgetAuthors() {
        Cache::forget('authors');
    }


    public static function getUserFilterList() {
        $filters = Cache::remember('user_filters', now()->addHours(24), function () {
            return UserFilter::select('id', 'name', 'created_at')->orderBy('name')->get();
        });
        return $filters;
    }

    public static function forgetUserFilterList() {
        Cache::forget('user_filters');
    }


    public static function getSponsorFilterList() {
        $filters = Cache::remember('sponsor_filters', now()->addHours(24), function () {
            return SponsorFilter::select('id', 'name', 'created_at')->orderBy('name')->get();
        });
        return $filters;
    }

    public static function forgetSponsorFilterList() {
        Cache::forget('sponsor_filters');
    }

    public static function getClassSubjects(?string $assessmentContext = null, ?string $mode = null) {
        $userId = auth()->id();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $resolver = app(\App\Services\SchoolModeResolver::class);
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $resolver->mode()) ?? SchoolSetup::TYPE_JUNIOR;
        $resolvedContext = $resolver->resolveAssessmentContext($assessmentContext, $resolvedMode)
            ?? $resolver->defaultAssessmentContext($resolvedMode);
        $cacheKey = "class_subjects_user_{$userId}_term_{$selectedTermId}_mode_{$resolvedMode}_context_{$resolvedContext}";
    
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($selectedTermId, $userId, $resolver, $resolvedMode, $resolvedContext) {
            try {
                $user = User::find($userId);
                $hasRole = $user->roles->contains(function ($role) {
                    return in_array($role->name, ['Administrator', 'Academic Admin', 'HOD', 'Assessment Admin']);
                });
                $allowedLevels = $resolver->levelsForAssessmentContext($resolvedContext, $resolvedMode);
    
                $query = KlassSubject::with(['subject.subject', 'teacher', 'klass.grade', 'klass.students'])
                    ->where('term_id', $selectedTermId)
                    ->whereHas('klass.grade', function ($gradeQuery) use ($allowedLevels) {
                        $gradeQuery->whereIn('level', $allowedLevels);
                    })
                    ->whereHas('subject.subject', function ($query) {
                        $query->where('components', 0);
                    });
    
                if (!$hasRole) {
                    $directSubordinateIds = $user->subordinates->pluck('id')->toArray();
                    $teacherIds = array_merge([$userId], $directSubordinateIds);
                    $query->where(function ($q) use ($teacherIds) {
                        $q->whereIn('user_id', $teacherIds)
                          ->orWhereIn('assistant_user_id', $teacherIds);
                    });
                }
    
                $klass_subjects = $query->get()
                    ->filter(function ($item) use ($user) {
                        return Gate::forUser($user)->allows('enterMarks', $item);
                    })
                    ->values();

                return $klass_subjects->map(function ($item) {
                    return [
                        'id'              => $item->id,
                        'subject_name'    => strtoupper(substr(optional($item->subject->subject)->name, 0, 3)) ?? 'N/A',
                        'subject_teacher' => optional($item->teacher)->firstname ? substr(optional($item->teacher)->firstname, 0, 1) . '. ' . optional($item->teacher)->lastname : 'No Teacher',
                        'klass_name'      => optional($item->klass)->name,
                        'student_count'   => optional($item->klass)->students->count()
                    ];
                });
    
            } catch (Exception $e) {
                Log::error('Error fetching class subjects: ' . $e->getMessage(), [
                    'user_id' => $userId,
                    'term_id' => $selectedTermId,
                    'trace'   => $e->getTraceAsString()
                ]);
                return collect([]);
            }
        });
    }

    public static function forgetClassSubjects($userId = null, $termId = null){
        $modes = [
            SchoolSetup::TYPE_PRIMARY,
            SchoolSetup::TYPE_JUNIOR,
            SchoolSetup::TYPE_SENIOR,
            SchoolSetup::TYPE_PRE_F3,
            SchoolSetup::TYPE_JUNIOR_SENIOR,
            SchoolSetup::TYPE_K12,
        ];
        $contexts = [
            \App\Services\SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY,
            \App\Services\SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR,
            \App\Services\SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR,
        ];

        if ($userId === null && $termId === null) {
            $users = User::pluck('id')->toArray();
            $terms = Term::pluck('id')->toArray();
            
            foreach ($users as $uid) {
                foreach ($terms as $tid) {
                    foreach ($modes as $mode) {
                        foreach ($contexts as $context) {
                            $cacheKey = "class_subjects_user_{$uid}_term_{$tid}_mode_{$mode}_context_{$context}";
                            Cache::forget($cacheKey);
                        }
                    }
                }
            }
            
        } else if ($userId !== null && $termId === null) {
            $terms = Term::pluck('id')->toArray(); 
            
            foreach ($terms as $tid) {
                foreach ($modes as $mode) {
                    foreach ($contexts as $context) {
                        $cacheKey = "class_subjects_user_{$userId}_term_{$tid}_mode_{$mode}_context_{$context}";
                        Cache::forget($cacheKey);
                    }
                }
            }
        } else if ($userId === null && $termId !== null) {
            $users = User::pluck('id')->toArray();
            
            foreach ($users as $uid) {
                foreach ($modes as $mode) {
                    foreach ($contexts as $context) {
                        $cacheKey = "class_subjects_user_{$uid}_term_{$termId}_mode_{$mode}_context_{$context}";
                        Cache::forget($cacheKey);
                    }
                }
            }
        } else {
            foreach ($modes as $mode) {
                foreach ($contexts as $context) {
                    $cacheKey = "class_subjects_user_{$userId}_term_{$termId}_mode_{$mode}_context_{$context}";
                    Cache::forget($cacheKey);
                }
            }
        }
    }


    public static function getOptionalSubjectAllocations($id, $selectedTermId) {
        $cacheKey = "optional_subject_allocations_{$id}_term_{$selectedTermId}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($id, $selectedTermId) {
            return OptionalSubject::with(['students' => function ($query) {
                $query->select('students.id', 'first_name', 'last_name', 'gender', 'student_type_id')
                      ->with('type')
                      ->orderBy('first_name');
            }, 'teacher'])->where('term_id', $selectedTermId)->findOrFail($id);
        });
    }

    public static function forgetOptionalSubjectAllocations($id, $selectedTermId) {
        $cacheKey = "optional_subject_allocations_{$id}_term_{$selectedTermId}";
        Cache::forget($cacheKey);
    }

    public static function getUnallocatedHouseStudents($termId) {
        $cacheKey = "unallocated_house_students_term_{$termId}_" . date('Y-m-d-H');
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($termId) {
            return Student::whereHas('terms', function ($query) use ($termId) {
                $query->where('student_term.term_id', $termId)
                      ->where('student_term.status', 'Current');
            })->whereDoesntHave('houses', function ($query) use ($termId) {
                $query->where('student_house.term_id', $termId);
            })->whereHas('classes', function ($query) use ($termId) {
                $query->where('klass_student.term_id', $termId);
            })->get();
        });
    }


    public static function forgetUnallocatedHouseStudents($termIdOrHouseId, ?int $termId = null) {
        $termId = $termId ?? $termIdOrHouseId;
        $cacheKey = "unallocated_house_students_term_{$termId}_" . date('Y-m-d-H');
        Cache::forget($cacheKey);
    }

    public static function getUnallocatedHouseUsers(int $termId) {
        $cacheKey = "unallocated_house_users_term_{$termId}_" . date('Y-m-d-H');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($termId) {
            return User::query()
                ->where('status', 'Current')
                ->whereDoesntHave('allocatedHouses', function ($query) use ($termId) {
                    $query->where('user_house.term_id', $termId);
                })
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->get();
        });
    }

    public static function forgetUnallocatedHouseUsers($termIdOrHouseId, ?int $termId = null): void
    {
        $termId = $termId ?? $termIdOrHouseId;
        $cacheKey = "unallocated_house_users_term_{$termId}_" . date('Y-m-d-H');
        Cache::forget($cacheKey);
    }

    public static function getKlassesForTerm($termId, $user, $hasRole, ?string $assessmentContext = null, ?string $mode = null) {
        $resolver = app(\App\Services\SchoolModeResolver::class);
        $resolvedMode = \App\Models\SchoolSetup::normalizeType($mode ?? $resolver->mode()) ?? \App\Models\SchoolSetup::TYPE_JUNIOR;
        $resolvedContext = $resolver->resolveAssessmentContext($assessmentContext, $resolvedMode);
        $cacheContext = $resolvedContext ?? 'all';
        $cacheKey = "klasses_for_term_{$termId}_user_{$user->id}_context_{$cacheContext}_mode_{$resolvedMode}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($termId, $user, $hasRole, $resolver, $resolvedMode, $resolvedContext) {
            $levels = $resolvedContext !== null
                ? $resolver->levelsForAssessmentContext($resolvedContext, $resolvedMode)
                : $resolver->supportedLevels($resolvedMode);

            if ($hasRole) {
                $klasses = Klass::with(['teacher', 'grade'])
                    ->where('term_id', $termId)
                    ->whereHas('grade', function ($query) use ($levels) {
                        $query->whereIn('level', $levels);
                    })
                    ->withCount('students')
                    ->get();
            } else {
                $directSubordinateIds = $user->subordinates->pluck('id')->toArray();
                $teacherIds = array_merge([$user->id], $directSubordinateIds);
                
                $klasses = Klass::with(['teacher', 'grade'])
                    ->where('term_id', $termId)
                    ->whereHas('grade', function ($query) use ($levels) {
                        $query->whereIn('level', $levels);
                    })
                    ->where(function ($query) use ($teacherIds) {
                        $query->whereIn('user_id', $teacherIds);
                    })->withCount('students')->get();
            }
            
            if ($klasses->isEmpty()) {
                \Log::warning("No classes found for term ID: $termId and user: {$user->id}");
                return collect([]);
            }
            
            return $klasses->sortBy(function ($class) {
                preg_match('/^(\d+)([A-Za-z]*)/', $class->name, $matches);
                
                if (empty($matches)) {
                    return [999, $class->name];
                }
                
                $number = (int)($matches[1] ?? 0);
                $letter = $matches[2] ?? '';
                
                return [$number, $letter];
            })->values()->all();
        });
    }

    public static function forgetKlassesForTerm($termId, $user) {
        $resolver = app(\App\Services\SchoolModeResolver::class);
        $modes = [
            \App\Models\SchoolSetup::TYPE_PRIMARY,
            \App\Models\SchoolSetup::TYPE_JUNIOR,
            \App\Models\SchoolSetup::TYPE_SENIOR,
            \App\Models\SchoolSetup::TYPE_PRE_F3,
            \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR,
            \App\Models\SchoolSetup::TYPE_K12,
        ];

        foreach ($modes as $mode) {
            $contexts = array_merge(['all'], $resolver->availableAssessmentContexts($mode));

            foreach ($contexts as $context) {
                $cacheKey = "klasses_for_term_{$termId}_user_{$user->id}_context_{$context}_mode_{$mode}";
                Cache::forget($cacheKey);
            }
        }

        Cache::forget("klasses_for_term_{$termId}_user_{$user->id}");
    }

}
