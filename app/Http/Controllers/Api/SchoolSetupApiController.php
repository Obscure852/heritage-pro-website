<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Models\Venue;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\Qualification;
use App\Models\Student;
use App\Models\License;
use App\Helpers\TermHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SchoolSetupApiController extends Controller{
    
    public function getSchoolInfo(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('staff.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required permission'
                ], 403);
            }
            
            $cacheKey = 'school_setup_info';
            $data = Cache::remember($cacheKey, 600, function() {
                $schoolSetup = SchoolSetup::latest()->first();
                
                if (!$schoolSetup) {
                    return null;
                }
                
                $personnel = $this->getSchoolLeadership();

                // Get current term student count
                $currentTermId = TermHelper::getCurrentTerm()->id;
                $currentTermStudents = Student::whereHas('studentTerms', function ($q) use ($currentTermId) {
                    $q->where('term_id', $currentTermId)->where('status', 'Current');
                })->count();

                // Get active streams count
                $activeStreams = Grade::where('active', true)->count();

                // Get special needs students count (students with non-null type)
                $specialNeedsStudents = Student::whereHas('studentTerms', function ($q) use ($currentTermId) {
                    $q->where('term_id', $currentTermId)->where('status', 'Current');
                })->whereNotNull('student_type_id')->count();

                // Get staff counts by category
                $staffCounts = $this->getStaffCounts();

                // Get license details
                $currentYear = TermHelper::getCurrentTerm()->year;
                $license = License::where('year', $currentYear)
                    ->where('active', true)
                    ->first();

                $licenseDetails = null;
                if ($license) {
                    $duration = $license->start_date->diffInDays($license->end_date);
                    $licenseDetails = [
                        'key' => $license->key,
                        'duration_days' => $duration,
                        'start_date' => $license->start_date->format('Y-m-d'),
                        'end_date' => $license->end_date->format('Y-m-d'),
                        'year' => $license->year,
                        'name' => $license->name,
                        'active' => $license->active
                    ];
                }

                return [
                    'school_name' => $schoolSetup->school_name,
                    'school_id' => $schoolSetup->school_id,
                    'ownership' => $schoolSetup->ownership,
                    'slogan' => $schoolSetup->slogan,
                    'telephone' => $schoolSetup->telephone,
                    'fax' => $schoolSetup->fax,
                    'email_address' => $schoolSetup->email_address,
                    'physical_address' => $schoolSetup->physical_address,
                    'postal_address' => $schoolSetup->postal_address,
                    'website' => $schoolSetup->website,
                    'region' => $schoolSetup->region,
                    'type' => $schoolSetup->type,
                    'boarding' => $schoolSetup->boarding,
                    'current_term_students' => $currentTermStudents,
                    'active_streams' => $activeStreams,
                    'special_needs_students_count' => $specialNeedsStudents,
                    'staff_counts' => $staffCounts,
                    'license' => $licenseDetails,
                    'school_head' => $personnel['school_head'],
                    'deputy_school_head' => $personnel['deputy_school_head'],
                    'facilities' => $this->getFacilitiesData(),
                    'qualified_teachers' => $this->getQualifiedTeachersData(),
                    'last_updated' => $schoolSetup->updated_at->toIso8601String()
                ];
            });
            
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'School setup information not found',
                    'data' => null
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'School information retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('School setup info endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch school information',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    private function getSchoolLeadership(){
        $schoolHeadPositions = ['School Head', 'Principal', 'Headmaster', 'Headmistress'];
        $deputyPositions = ['Deputy School Head', 'Deputy Principal', 'Assistant Principal', 'Vice Principal'];
        
        $schoolHead = User::where('active', 1)
            ->where('status', 'Current')
            ->whereIn('position', $schoolHeadPositions)
            ->select('firstname', 'middlename', 'lastname', 'email', 'position')
            ->first();
        
        $deputyHead = User::where('active', 1)
            ->where('status', 'Current')
            ->whereIn('position', $deputyPositions)
            ->select('firstname', 'middlename', 'lastname', 'email', 'position')
            ->first();
        
        return [
            'school_head' => $schoolHead ? [
                'name' => trim($schoolHead->full_name),
                'email' => $schoolHead->email,
                'position' => $schoolHead->position
            ] : null,
            'deputy_school_head' => $deputyHead ? [
                'name' => trim($deputyHead->full_name),
                'email' => $deputyHead->email,
                'position' => $deputyHead->position
            ] : null
        ];
    }
    
    public function getSchoolLogo(Request $request){
        try {
            $user = $request->user();
            if (!$user || !$user->tokenCan('school.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            $schoolSetup = SchoolSetup::latest()->first();
            
            if (!$schoolSetup || !$schoolSetup->logo_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logo not found'
                ], 404);
            }
            
            if (!Storage::exists($schoolSetup->logo_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logo file not found'
                ], 404);
            }
            
            $logoContent = Storage::get($schoolSetup->logo_path);
            $mimeType = Storage::mimeType($schoolSetup->logo_path);
            
            return response($logoContent, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            Log::error('School logo fetch error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch logo'
            ], 500);
        }
    }



    /**
     * Get school facilities data
     */
    public function getFacilities(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            if (!$user->tokenCan('staff.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing staff.read permission'
                ], 403);
            }

            $facilities = Cache::remember('school_facilities', 3600, function () {
                return $this->getFacilitiesData();
            });

            return response()->json([
                'success' => true,
                'data' => $facilities
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching school facilities', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching school facilities',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get qualified teachers information
     */
    public function getQualifiedTeachers(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            if (!$user->tokenCan('staff.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing staff.read permission'
                ], 403);
            }

            $teachers = Cache::remember('qualified_teachers', 3600, function () {
                return $this->getQualifiedTeachersData();
            });

            return response()->json([
                'success' => true,
                'data' => $teachers
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching qualified teachers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching qualified teachers',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * Get facilities data
     */
    private function getFacilitiesData(){
        // Get total classrooms (venues of type 'classroom')
        $totalClassrooms = Venue::where('type', 'classroom')->count();

        // Get total streams (grades)
        $totalStreams = Grade::where('active', 1)->count();

        // Calculate students per classroom average
        $classes = Klass::with('students')->where('active', 1)->get();
        $totalStudents = 0;
        $activeClasses = 0;

        foreach ($classes as $class) {
            $studentCount = $class->students->count();
            if ($studentCount > 0) {
                $totalStudents += $studentCount;
                $activeClasses++;
            }
        }

        $studentsPerClassroomAverage = $activeClasses > 0 ? round($totalStudents / $activeClasses, 2) : 0;

        // Get available facilities
        $availableFacilities = Venue::select('id', 'name', 'type', 'capacity')
            ->get()->map(function ($venue) {
                return [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'type' => $venue->type,
                    'capacity' => $venue->capacity,
                    'utilization_percentage' => $venue->utilization_percentage,
                    'is_over_capacity' => $venue->is_over_capacity
                ];
            });

        return [
            'total_classrooms' => $totalClassrooms,
            'total_streams' => $totalStreams,
            'students_per_classroom_average' => $studentsPerClassroomAverage,
            'available_facilities' => $availableFacilities,
            'facility_types' => Venue::select('type')->distinct()->pluck('type')
        ];
    }

    /**
     * Get qualified teachers data
     */
    private function getQualifiedTeachersData(){
        $qualifiedTeachers = User::whereHas('qualifications')
            ->where('active', 1)
            ->where('status', 'Current')
            ->with(['qualifications' => function ($query) {
                $query->select('qualifications.id', 'qualification', 'qualification_code');
            }])
            ->select('id', 'firstname', 'lastname', 'email', 'position', 'department')
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => trim($teacher->firstname . ' ' . $teacher->lastname),
                    'email' => $teacher->email,
                    'position' => $teacher->position,
                    'department' => $teacher->department,
                    'qualifications' => $teacher->qualifications->map(function ($qual) {
                        return [
                            'id' => $qual->id,
                            'qualification' => $qual->qualification,
                            'qualification_code' => $qual->qualification_code
                        ];
                    })
                ];
            });

        return [
            'total_qualified_teachers' => $qualifiedTeachers->count(),
            'teachers' => $qualifiedTeachers
        ];
    }

    /**
     * Get staff counts by category based on area_of_work
     */
    private function getStaffCounts(){
        // Base query for active current staff
        $baseQuery = User::where('active', 1)
            ->where('status', 'Current')
            ->whereNull('deleted_at');

        // Non-teaching staff (all staff except teachers)
        $nonTeachingStaff = (clone $baseQuery)
            ->where(function($query) {
                $query->where('area_of_work', '!=', 'Teaching')
                      ->orWhereNull('area_of_work');
            })->count();

        // Teaching staff
        $teachingStaff = (clone $baseQuery)
            ->where('area_of_work', 'Teaching')
            ->count();

        // Administrative staff
        $administrativeStaff = (clone $baseQuery)
            ->where('area_of_work', 'Administration')
            ->count();

        // Support staff (neither Administration nor Teaching)
        $supportStaff = (clone $baseQuery)
            ->whereNotIn('area_of_work', ['Administration', 'Teaching'])
            ->whereNotNull('area_of_work')
            ->count();

        return [
            'non_teaching' => $nonTeachingStaff,
            'teaching' => $teachingStaff,
            'administrative' => $administrativeStaff,
            'support' => $supportStaff,
            'total_staff' => $nonTeachingStaff + $administrativeStaff + $supportStaff
        ];
    }
}
