<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Sponsor;
use App\Models\Admission;
use App\Helpers\TermHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller{
    private const CACHE_DURATION = 5;

    public function search(Request $request){
        if (!$request->ajax()) {
            return abort(404);
        }

        $query = trim($request->get('query'));

        if (empty($query)) {
            return $this->emptyResponse();
        }

        $cacheKey = $this->generateCacheKey($query);
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($query) {
            $results = [
                'users' => collect(),
                'students' => collect(),
                'sponsors' => collect(),
                'admissions' => collect(),
            ];

            if (Gate::allows('access-hr')) {
                $results['users'] = $this->searchUsers($query);
            }

            if (Gate::allows('access-students')) {
                $results['students'] = $this->searchStudents($query);
            }

            if (Gate::allows('access-sponsors')) {
                $results['sponsors'] = $this->searchSponsors($query);
            }

            if (Gate::allows('access-admissions')) {
                $results['admissions'] = $this->searchAdmissions($query);
            }

            $counts = [
                'users' => $results['users']->count(),
                'students' => $results['students']->count(),
                'sponsors' => $results['sponsors']->count(),
                'admissions' => $results['admissions']->count(),
            ];

            $html = view('search.results', $results)->render();
            return response()->json([
                'html' => $html,
                'count' => $counts,
                'cached' => true,
                'cache_time' => now()->toISOString(),
            ]);
        });
    }

    private function generateCacheKey(string $query){
        $termId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $userId = auth()->id();
        $userRoles = auth()->user()->roles->pluck('name')->join(',');

        return sprintf(
            'search:%s:term:%s:user:%s:roles:%s',
            md5(strtolower($query)),
            $termId,
            $userId,
            md5(strtolower($userRoles))
        );
    }

    private function searchStudents(string $query){
        $termId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $cacheKey = "students:term:{$termId}:query:" . md5(strtolower($query));

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($query, $termId) {
            $students = Student::whereHas('studentTerms', function ($q) use ($termId) {
                $q->where('term_id', $termId)
                  ->where('status', 'Current');
            })->where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('id_number', 'LIKE', "%{$query}%");
            });

            if (!auth()->user()->hasRoles('Administrator')) {
                $students = $students->whereNull('deleted_at');

                if (auth()->user()->hasRoles('Class Teacher')) {
                    $students = $students->whereHas('classes', function ($q) use ($termId) {
                        $q->where('klass_student.term_id', $termId)
                          ->where('klasses.user_id', auth()->id());
                    });
                }
            }

            return $students->with(['currentClassRelation' => function ($q) use ($termId) {
                $q->where('klass_student.term_id', $termId);
            }])
            ->select(['id', 'first_name', 'last_name', 'id_number', 'status'])
            ->limit(5)->get()->map(function ($student) {
                $student->current_class = $student->currentClassRelation->first()?->name ?? 'No Class';
                unset($student->currentClassRelation);
                return $student;
            });
        });
    }

    private function searchUsers(string $query){
        $cacheKey = "users:query:" . md5(strtolower($query)) . ":user:" . auth()->id();
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($query) {
            $users = User::where(function ($q) use ($query) {
                $q->where('firstname', 'LIKE', "%{$query}%")
                  ->orWhere('lastname', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('id_number', 'LIKE', "%{$query}%");
            });

            if (!auth()->user()->hasRoles('Administrator')) {
                $users = $users->where('active', true)
                               ->whereNull('deleted_at');
            }
            return $users->select(['id', 'firstname', 'lastname', 'email', 'phone', 'id_number', 'position'])
                         ->limit(5)
                         ->get();
        });
    }

    private function searchSponsors(string $query){
        $cacheKey = "sponsors:query:" . md5(strtolower($query));
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($query) {
            $sponsors = Sponsor::where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('id_number', 'LIKE', "%{$query}%");
            });

            if (!auth()->user()->hasRoles('Administrator')) {
                $sponsors = $sponsors->whereNull('deleted_at');
            }

            return $sponsors->select(['id', 'first_name', 'last_name', 'email', 'phone', 'id_number'])
                            ->limit(5)
                            ->get();
        });
    }

    private function searchAdmissions(string $query){
        $cacheKey = "admissions:query:" . md5(strtolower($query));
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($query) {
            $admissions = Admission::where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('id_number', 'LIKE', "%{$query}%");
            });

            if (!auth()->user()->hasRoles('Administrator')) {
                $admissions = $admissions->whereNull('deleted_at');
            }
            return $admissions->select(['id', 'first_name', 'last_name', 'id_number', 'status'])->limit(5) ->get();
        });
    }

    private function emptyResponse(){
        return response()->json([
            'html' => '',
            'count' => [
                'users' => 0,
                'students' => 0,
                'sponsors' => 0,
                'admissions' => 0,
            ],
        ]);
    }
}
