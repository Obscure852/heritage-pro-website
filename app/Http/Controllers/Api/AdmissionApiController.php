<?php

namespace App\Http\Controllers\Api;

use App\Models\Admission;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdmissionApiController extends Controller{
    
    public function index(){
        try {
            $admissions = Admission::with(['sponsor:id,first_name,last_name,email,phone,telephone'])
                ->select([
                    'id',
                    'sponsor_id',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'gender',
                    'date_of_birth',
                    'nationality',
                    'phone',
                    'id_number',
                    'grade_applying_for',
                    'application_date',
                    'status',
                    'year'
                ])->paginate(10);
    
            return response()->json([
                'success' => true,
                'count' => $admissions->total(),
                'data' => AdmissionResource::collection($admissions),
                'pagination' => [
                    'current_page' => $admissions->currentPage(),
                    'last_page' => $admissions->lastPage(),
                    'per_page' => $admissions->perPage(),
                    'total' => $admissions->total(),
                ]
            ]);
    
        } catch (\Exception $e) {
            Log::error('API Error Details: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Check Laravel logs for details'
            ], 500);
        }
    }

    public function show($id){
        try {
            $admission = Admission::with(['sponsor:id,first_name,last_name,email,phone,telephone'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => new AdmissionResource($admission)
            ]);

        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Admission not found or error occurred',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function statistics(){
        try {
            $stats = [
                'total' => Admission::count(),
                'by_status' => [
                    'pending' => Admission::where('status', 'Pending')->count(),
                    'approved' => Admission::where('status', 'Approved')->count(),
                    'rejected' => Admission::where('status', 'Rejected')->count()
                ],
                'by_gender' => [
                    'male' => Admission::where('gender', 'Male')->count(),
                    'female' => Admission::where('gender', 'Female')->count()
                ],
                'by_grade' => Admission::select('grade_applying_for', DB::raw('count(*) as count'))
                    ->groupBy('grade_applying_for')
                    ->get()
                    ->pluck('count', 'grade_applying_for')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('API Statistics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
