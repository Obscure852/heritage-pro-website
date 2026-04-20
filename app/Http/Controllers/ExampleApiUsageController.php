<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExampleApiUsageController extends Controller
{
    /**
     * Example: How to access the school info API from within Laravel
     */
    public function getSchoolInfoFromApi()
    {
        try {
            // Get the bearer token from the request
            $bearerToken = request()->bearerToken();

            if (!$bearerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'No access token found'
                ], 401);
            }

            // Make internal API call using the same token
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,
                'Accept' => 'application/json'
            ])->get(url('/api/school/info'));

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'School info retrieved successfully',
                        'data' => $data['data']
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Failed to retrieve school info'
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'API request failed',
                    'status' => $response->status()
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Error accessing school info API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Example: Display school info in a view
     */
    public function showSchoolInfoPage()
    {
        try {
            $schoolInfo = $this->getSchoolInfoFromApi();

            if ($schoolInfo->getStatusCode() === 200) {
                $data = json_decode($schoolInfo->getContent(), true);
                return view('school.info', ['schoolData' => $data['data']]);
            } else {
                return view('school.info', ['error' => 'Unable to load school information']);
            }

        } catch (\Exception $e) {
            Log::error('Error loading school info page', [
                'error' => $e->getMessage()
            ]);

            return view('school.info', ['error' => 'An error occurred']);
        }
    }

    /**
     * Example: Access other school endpoints
     */
    public function getSchoolFacilities()
    {
        try {
            $bearerToken = request()->bearerToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,
                'Accept' => 'application/json'
            ])->get(url('/api/school/facilities'));

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error accessing school facilities API', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve facilities data'
            ];
        }
    }

    /**
     * Example: Batch API calls
     */
    public function getAllSchoolData()
    {
        try {
            $bearerToken = request()->bearerToken();
            $headers = [
                'Authorization' => 'Bearer ' . $bearerToken,
                'Accept' => 'application/json'
            ];

            // Make multiple API calls concurrently
            $responses = Http::pool(function ($pool) use ($headers) {
                return [
                    $pool->withHeaders($headers)->get(url('/api/school/info')),
                    $pool->withHeaders($headers)->get(url('/api/school/facilities')),
                    $pool->withHeaders($headers)->get(url('/api/school/qualified-teachers')),
                ];
            });

            return [
                'school_info' => $responses[0]->successful() ? $responses[0]->json()['data'] : null,
                'facilities' => $responses[1]->successful() ? $responses[1]->json()['data'] : null,
                'teachers' => $responses[2]->successful() ? $responses[2]->json()['data'] : null,
            ];

        } catch (\Exception $e) {
            Log::error('Error in batch school data API calls', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve school data'
            ];
        }
    }
}
