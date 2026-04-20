<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nationality;
use App\Helpers\CacheHelper;
use App\Jobs\SendAdmissionEmail;
use App\Models\SchoolSetup;
use App\Models\Admission;
use App\Models\AdmissionAcademic;
use App\Models\AdmissionHealthInformation;
use App\Models\OnlineApplicationAttachment;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OnlineApplicationController extends Controller{

    public function index(){
        $nationalities = Nationality::all();
        $grades = CacheHelper::getGrades();
        $terms = StudentController::terms();

        $school_data = SchoolSetup::first();
        $activeTab = 'student';
        return view('admissions.online-applications',['grades' => $grades,'nationalities' => $nationalities,'terms' => $terms,'school_data' => $school_data,'activeTab' => $activeTab]);
    }

    public function createOnlineAdmissionRecord(Request $request){
        $validated = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'gender' => 'required|string|max:191',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string|max:191',
            'phone' => 'nullable|string|max:191',
            'id_number' => 'required|string|max:191',
            'grade' => 'required|string|max:191',
            'year' => 'required|string|max:191',
            'application_date' => 'required|date',
            'status' => 'required|string|max:191',
            'term_id' => 'required|string|max:191',
            'year' => 'required|integer',
        ]);

        // Create a temporary parent record
        $temporaryParent = Sponsor::create([
            'connect_id' => 0, // Adjust as necessary
            'title' => 'Temporary',
            'first_name' => 'Temporary',
            'last_name' => 'Parent',
            'email' => null,
            'gender' => 'Other',
            'date_of_birth' => now(),
            'nationality' => 'Unknown',
            'relation' => 'Unknown',
            'status' => 'Current',
            'id_number' => 'TEMP' . time(),
            'phone' => null,
            'profession' => null,
            'work_place' => null,
            'telephone' => null,
            'filter' => null,
            'year' => now()->year,
            'last_updated_by' => auth()->id(),
        ]);

        $admission = Admission::create([
            'sponsor_id' => $temporaryParent->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'nationality' => $validated['nationality'],
            'phone' => $validated['phone'],
            'id_number' => $validated['id_number'],
            'grade_applying_for' => $validated['grade'],
            'academic_year_applying_for' => $validated['year'],
            'application_date' => $validated['application_date'],
            'status' => 'New Online',
            'term_id' => $validated['term_id'],
            'year' => $validated['year'],
            'last_updated_by' => auth()->id(),
        ]);
        Cache::forget('sponsors');
        return redirect()->route('admissions.show-parent-online-applications', ['admissionId' => $admission->id]);
    }


    public function createOnlineApplicatonParentRecord(Request $request){
        $validated = $request->validate([
            'title' => 'nullable|string|max:6',
            'admission_id' => 'required|integer|exists:admissions,id',
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'gender' => 'required|string|max:191',
            'date_of_birth' => 'required|date',
            'id_number' => 'required|string|max:191',
            'phone' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
            'nationality' => 'nullable|string|max:191',
            'relation' => 'nullable|string|max:191',
            'profession' => 'nullable|string|max:191',
            'work_place' => 'nullable|string|max:191',
            'telephone' => 'nullable|string|max:191',
        ]);
        
        $admission = Admission::find($request->input('admission_id'));
        $parent = Sponsor::find($admission->sponsor_id);

        $parent->update([
            'connect_id' => $admission->sponsor_id,
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'id_number' => $validated['id_number'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'nationality' => $validated['nationality'],
            'relation' => $validated['relation'],
            'profession' => $validated['profession'],
            'work_place' => $validated['work_place'],
            'telephone' => $validated['telephone'],
            'status' => 'Current',
        ]);
        return redirect()->route('admissions.show-health-online-applications', ['admissionId' => $admission->id]);
    }


    public function showParentForm($admissionId){
        $admission = Admission::find($admissionId);
        $school_data = SchoolSetup::first();

        $nationalities = Nationality::all();
        $grades = CacheHelper::getGrades();
        $terms = StudentController::terms();

        $activeTab = 'parent';
        return view('admissions.online-applications', compact('admission', 'activeTab','school_data','nationalities','grades','terms'));
    }

    public function showHealthForm($admissionId){
        $admission = Admission::find($admissionId);
        $school_data = SchoolSetup::first();

        $nationalities = Nationality::all();
        $grades = CacheHelper::getGrades();
        $terms = StudentController::terms();
        $healthInfo = AdmissionHealthInformation::where('admission_id', $admissionId)->first();
        $activeTab = 'health';
        return view('admissions.online-applications', compact('admission', 'activeTab','school_data','nationalities','grades','terms','healthInfo'));
    }


    public function showAcademicForm($admissionId){
        $admission = Admission::find($admissionId);
        $school_data = SchoolSetup::first();

        $nationalities = Nationality::all();
        $grades = CacheHelper::getGrades();
        $terms = StudentController::terms();
        $activeTab = 'academic';
        return view('admissions.online-applications', compact('admission', 'activeTab','school_data','nationalities','grades','terms'));
    }


    public function showAttachmentsForm($admissionId){
        $admission = Admission::find($admissionId);
        $school_data = SchoolSetup::first();

        $nationalities = Nationality::all();
        $grades = CacheHelper::getGrades();
        $terms = StudentController::terms();
        $activeTab = 'attachments';
        return view('admissions.online-applications', compact('admission', 'activeTab','school_data','nationalities','grades','terms'));
    }

    public function createStudentAttachmentsRecord(Request $request){
        $request->validate([
            'admission_id' => 'required|integer|exists:admissions,id',
            'attachments.identification' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'attachments.report' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'attachments.application_fee_receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $admissionId = $request->input('admission_id');
        $admission = Admission::find($admissionId);

        foreach ($request->file('attachments') as $type => $file) {
            $fileName = $admission->first_name . '_' . $admission->last_name . '_' . $type . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('attachments', $fileName);

            OnlineApplicationAttachment::create([
                'admission_id' => $admissionId,
                'attachment_type' => $type,
                'file_path' => $filePath,
            ]);
        }

        return redirect()->route('admissions.online-application-complete', ['admissionId' => $admissionId]);
    }
    
    public function createStudentHealthRecord(Request $request){
        $request->validate([
            'admission_id' => 'exists:admissions,id',
            'health_history' => 'nullable|string|max:255',
            'immunization_records' => 'file|mimes:pdf,jpg,jpeg,png|max:10240|nullable', 
            'other_allergies' => 'nullable|string|max:255',
            'other_disabilities' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:255',
        ]);
    
        $data = $request->except('_token', 'immunization_records');
        $admission = Admission::find($request->input('admission_id'));
    
        if ($request->hasFile('immunization_records')) {
            $admission = Admission::find($request->input('admission_id'));
            $studentName = strtolower(str_replace(' ', '-', $admission->first_name . '-' . $admission->last_name));
            $extension = $request->file('immunization_records')->extension();
            $filename = "{$studentName}-immunization.{$extension}";
            $path = $request->file('immunization_records')->storeAs('admissions/medicals', $filename);
            $data['immunization_records'] = $path;
        }
    
        $booleanFields = [
            'peanuts', 'red_meat', 'vegetarian',
            'left_leg', 'right_leg', 'left_hand', 'right_hand',
            'left_eye', 'right_eye', 'left_ear', 'right_ear'
        ];
    
        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field);
        }
    
        AdmissionHealthInformation::updateOrInsert(
            ['admission_id' => $request->input('admission_id')],
            $data
        );
        return redirect()->route('admissions.show-academic-online-applications', ['admissionId' => $admission->id]);
    }


    public function createStudentAcademicRecord(Request $request){
        $request->validate([
            'admission_id' => 'exists:admissions,id',
            'science' => 'nullable|string|regex:/^[A-F]+[+-]?$/',
            'mathematics' => 'nullable|string|regex:/^[A-F]+[+-]?$/',
            'english' => 'nullable|string|regex:/^[A-F]+[+-]?$/',
        ]);
    
        $data = $request->only(['admission_id', 'science', 'mathematics', 'english']);
        $admission = Admission::find($request->input('admission_id'));
        $admission->status = 'New Online';
        $admission->save();
        
        AdmissionAcademic::updateOrInsert(
            ['admission_id' => $request->input('admission_id')],
            $data
        );
        return redirect()->route('admissions.show-attachments-online-applications', ['admissionId' => $admission->id]);
    }

    public function onlineApplicationComplete($admissionId){
        try {
            $admission = Admission::findOrFail($admissionId);
            $parentEmailAddress = Sponsor::where('id', $admission->sponsor_id)->pluck('email')->first();
    
            if (!$parentEmailAddress) {
                throw new \Exception("Parent email address not found for sponsor ID: {$admission->sponsor_id}");
            }
            SendAdmissionEmail::dispatch($admission, $parentEmailAddress);
        }catch (\Exception $e) {
            Log::error("Error completing online application for ID: {$admissionId}", ['error' => $e->getMessage()]);
        }
        return view('admissions.online-application-complete', compact('admission'));
    }
}
