<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCode;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AttendanceCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the settings page with all attendance codes
     */
    public function index()
    {
        $codes = AttendanceCode::ordered()->get();
        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();
        $currentTerm = Term::currentOrLastActiveTerm();

        return view('attendance.settings', [
            'codes' => $codes,
            'terms' => $terms,
            'currentTerm' => $currentTerm,
        ]);
    }

    /**
     * Show the form to create a new attendance code
     */
    public function create()
    {
        return view('attendance.settings-create');
    }

    /**
     * Store a new attendance code
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:attendance_codes,code',
            'description' => 'required|string|max:100',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_present' => 'boolean',
        ], [
            'color.regex' => 'The color must be a valid hex color (e.g., #ff0000).',
        ]);

        try {
            $maxOrder = AttendanceCode::max('order') ?? 0;

            AttendanceCode::create([
                'order' => $maxOrder + 1,
                'code' => $request->code,
                'description' => $request->description,
                'color' => $request->color,
                'is_present' => $request->boolean('is_present'),
                'is_active' => true,
            ]);

            Attendance::clearCodesCache();

            return redirect()->route('attendance.settings')->with('message', 'Attendance code added successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating attendance code', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'An error occurred while adding the attendance code.');
        }
    }

    /**
     * Update an existing attendance code
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('attendance_codes')->ignore($id)],
            'description' => 'required|string|max:100',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_present' => 'boolean',
            'is_active' => 'boolean',
        ], [
            'color.regex' => 'The color must be a valid hex color (e.g., #ff0000).',
        ]);

        try {
            $code = AttendanceCode::findOrFail($id);
            $code->update([
                'code' => $request->code,
                'description' => $request->description,
                'color' => $request->color,
                'is_present' => $request->boolean('is_present'),
                'is_active' => $request->boolean('is_active'),
            ]);

            Attendance::clearCodesCache();

            return redirect()->back()->with('message', 'Attendance code updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating attendance code', [
                'error' => $e->getMessage(),
                'code_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the attendance code.');
        }
    }

    /**
     * Delete an attendance code
     */
    public function destroy($id)
    {
        try {
            $code = AttendanceCode::findOrFail($id);
            $code->delete();

            Attendance::clearCodesCache();

            return redirect()->back()->with('message', 'Attendance code deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting attendance code', [
                'error' => $e->getMessage(),
                'code_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while deleting the attendance code.');
        }
    }

    /**
     * Update the order of attendance codes
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:attendance_codes,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->order as $index => $id) {
                    AttendanceCode::where('id', $id)->update(['order' => $index + 1]);
                }
            });

            Attendance::clearCodesCache();

            return response()->json(['success' => true, 'message' => 'Order updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error updating attendance code order', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update order.'], 500);
        }
    }

    /**
     * Toggle the active status of an attendance code
     */
    public function toggleActive($id)
    {
        try {
            $code = AttendanceCode::findOrFail($id);
            $code->is_active = !$code->is_active;
            $code->save();

            Attendance::clearCodesCache();

            return redirect()->back()->with('message', 'Attendance code status updated.');
        } catch (\Exception $e) {
            Log::error('Error toggling attendance code status', [
                'error' => $e->getMessage(),
                'code_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the status.');
        }
    }
}
