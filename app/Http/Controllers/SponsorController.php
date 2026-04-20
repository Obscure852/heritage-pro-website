<?php

namespace App\Http\Controllers;

use App\Exports\ParentsAnalysisListExport;
use App\Helpers\CacheHelper;
use App\Exports\ParentsAnalysisContactListExport;
use App\Exports\SponsorImportExportReport;
use App\Helpers\TermHelper;
use App\Models\Grade;
use App\Models\SchoolSetup;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\OtherInformation;
use App\Models\SponsorFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SponsorController extends Controller{

    private const SPONSOR_TITLES = ['Mr', 'Mrs', 'Ms', 'Dr', 'Miss'];
    private const SPONSOR_GENDERS = ['M', 'F'];
    private const SPONSOR_RELATIONS = ['Mother', 'Grandmother', 'Father', 'Grandfather', 'Brother', 'Sister', 'Uncle', 'Auntie', 'Relative', 'Other'];
    private const SPONSOR_STATUSES = ['Current', 'Deleted', 'Past'];
    private const GENERATED_EMAIL_DOMAINS = ['yahoo.co.uk', 'gmail.com', 'mail.com'];
    private const GENERATED_MOBILE_PREFIXES = ['71', '72', '73', '75', '76'];
    private const GENERATED_LANDLINE_PREFIXES = ['2', '3', '4', '6'];

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request){
        $sponsors = CacheHelper::getSponsorsData();
    
        if ($status = $request->input('status')) {
            $sponsors = $sponsors->filter(function ($sponsor) use ($status) {
                return $sponsor->status === $status;
            });
        }
    
        if ($sponsorFilterId = $request->input('sponsor')) {
            $sponsors = $sponsors->filter(function ($sponsor) use ($sponsorFilterId) {
                return $sponsor->sponsor_filter_id == $sponsorFilterId;
            });
        }
    
        if ($grade = $request->input('grade')) {
            $sponsors = $sponsors->filter(function ($sponsor) use ($grade) {
                return $sponsor->students->contains(function ($student) use ($grade) {
                    return optional($student->currentGrade)->name == $grade;
                });
            });
        }
        
        if ($request->ajax()) {
            return view('sponsors.partials.sponsors-list', compact('sponsors'));
        }
        $filters = CacheHelper::getSponsorFilterList();
        $grades = Grade::where('active',1)->get();
        return view('sponsors.index', compact('sponsors','filters','grades'));
    }

    private function generateRandomMobile(): string{
        $prefix = self::GENERATED_MOBILE_PREFIXES[array_rand(self::GENERATED_MOBILE_PREFIXES)];
        $suffix = str_pad(rand(0, 999_999), 6, '0', STR_PAD_LEFT);
        return $prefix . $suffix;
    }

    private function generateRandomLandline(): string{
        $prefix = self::GENERATED_LANDLINE_PREFIXES[array_rand(self::GENERATED_LANDLINE_PREFIXES)];
        $suffix = str_pad(rand(0, 999_999), 6, '0', STR_PAD_LEFT);
        return $prefix . $suffix;
    }

    private function generateRandomDateOfBirth(): string{
        $min = Carbon::now()->subYears(100)->startOfDay()->timestamp;
        $max = Carbon::now()->subYears(16)->startOfDay()->timestamp;
        $randTs = random_int($min, $max);
        return Carbon::createFromTimestamp($randTs)->toDateString();
    }

    private function sponsorValidator(Request $request){
        return Validator::make($request->all(), [
            'title' => ['required', 'string', Rule::in(self::SPONSOR_TITLES)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => [
                'nullable',
                'date_format:d/m/Y',
            ],
            'gender' => ['required', 'string', Rule::in(self::SPONSOR_GENDERS)],
            'id_number' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s-]+$/'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'relation' => ['nullable', 'string', Rule::in(self::SPONSOR_RELATIONS)],
            'status' => ['nullable', 'string', Rule::in(self::SPONSOR_STATUSES)],
            'phone' => ['nullable', 'string', 'regex:/^(?:(?:00267|267)?[7][1-9][\d\s]{6,10})$/'],
            'telephone' => ['nullable', 'string', 'regex:/^(?:(?:00267|267)?[2-6](?:\s*\d){6})$/'],
            'profession' => ['nullable', 'string', 'max:255'],
            'work_place' => ['nullable', 'string', 'max:255'],
            'sponsor_filter_id' => ['nullable', 'integer', 'exists:sponsor_filters,id'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'last_updated_by' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'Please select a title',
            'title.in' => 'Please select a valid title',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.email' => 'Please enter a valid email address',
            'date_of_birth.before' => 'Sponsor must be at least 16 years old',
            'date_of_birth.after' => 'Sponsor age cannot exceed 100 years',
            'gender.required' => 'Please select a gender',
            'gender.in' => 'Please select a valid gender',
            'id_number.regex' => 'ID/Passport number can only contain letters, numbers, spaces and hyphens',
            'relation.in' => 'Please select a valid relation',
            'status.in' => 'Please select a valid status',
            'phone.regex' => 'Please enter a valid Botswana mobile number (must start with 71-79)',
            'telephone.regex' => 'Please enter a valid Botswana telephone number',
            'sponsor_filter_id.exists' => 'Selected filter is not valid',
            'year.digits' => 'Year must be a four digit number',
        ]);
    }

    private function normalizeSponsorName(?string $value): string{
        $value = preg_replace('/\s+/', ' ', trim((string) $value));

        return ucwords(strtolower($value));
    }

    private function normalizeOptionalText(?string $value): ?string{
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_replace('/\s+/', ' ', $value);
    }

    private function buildSponsorEmailLocalPart(string $firstName, string $lastName): string{
        $localPart = strtolower(preg_replace('/[^A-Za-z0-9]+/', '', $firstName . $lastName));

        return $localPart !== '' ? $localPart : 'sponsor';
    }

    private function sponsorValueExists(string $column, string $value, ?int $ignoreId = null): bool{
        return Sponsor::where($column, $value)
            ->whereNull('deleted_at')
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    private function ensureUniqueSponsorValue(string $column, string $value, ?int $ignoreId, string $message): void{
        if ($this->sponsorValueExists($column, $value, $ignoreId)) {
            throw ValidationException::withMessages([$column => $message]);
        }
    }

    private function generateUniqueSponsorEmail(string $firstName, string $lastName, ?int $ignoreId = null): string{
        $localPart = $this->buildSponsorEmailLocalPart($firstName, $lastName);

        for ($attempt = 0; $attempt < 50; $attempt++) {
            $domain = self::GENERATED_EMAIL_DOMAINS[array_rand(self::GENERATED_EMAIL_DOMAINS)];
            $suffix = $attempt === 0 ? '' : (string) random_int(10, 999);
            $email = $localPart . $suffix . '@' . $domain;

            if (!$this->sponsorValueExists('email', $email, $ignoreId)) {
                return $email;
            }
        }

        throw ValidationException::withMessages([
            'email' => 'Unable to generate a unique email address automatically. Please enter one manually.',
        ]);
    }

    private function generateUniqueSponsorPhone(?int $ignoreId = null): string{
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $phone = self::formatPhoneNumber($this->generateRandomMobile(), true);

            if (!$this->sponsorValueExists('phone', $phone, $ignoreId)) {
                return $phone;
            }
        }

        throw ValidationException::withMessages([
            'phone' => 'Unable to generate a unique phone number automatically. Please enter one manually.',
        ]);
    }

    private function generateUniqueSponsorIdNumber(?int $ignoreId = null): string{
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $idNumber = str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT);

            if (!$this->sponsorValueExists('id_number', $idNumber, $ignoreId)) {
                return $idNumber;
            }
        }

        throw ValidationException::withMessages([
            'id_number' => 'Unable to generate a unique ID number automatically. Please enter one manually.',
        ]);
    }

    private function generateConnectId(): int{
        do {
            $connectId = mt_rand(10000, 99999);
        } while (Sponsor::where('connect_id', $connectId)->exists());

        return $connectId;
    }

    private function prepareSponsorPayload(Request $request, ?Sponsor $sponsor = null): array{
        $sponsorId = $sponsor?->id;
        $firstName = $this->normalizeSponsorName($request->input('first_name'));
        $lastName = $this->normalizeSponsorName($request->input('last_name'));

        $email = $request->filled('email')
            ? strtolower(trim((string) $request->input('email')))
            : $this->generateUniqueSponsorEmail($firstName, $lastName, $sponsorId);

        $phone = $request->filled('phone')
            ? self::formatPhoneNumber($request->input('phone'), true)
            : $this->generateUniqueSponsorPhone($sponsorId);

        $telephone = $request->filled('telephone')
            ? self::formatPhoneNumber($request->input('telephone'), false)
            : self::formatPhoneNumber($this->generateRandomLandline(), false);

        $idNumber = $request->filled('id_number')
            ? $this->normalizeOptionalText($request->input('id_number'))
            : $this->generateUniqueSponsorIdNumber($sponsorId);

        $this->ensureUniqueSponsorValue('email', $email, $sponsorId, 'This email address is already in use');
        $this->ensureUniqueSponsorValue('phone', $phone, $sponsorId, 'This phone number is already registered');
        $this->ensureUniqueSponsorValue('id_number', $idNumber, $sponsorId, 'This ID/Passport number is already registered');

        $payload = [
            'title' => $request->input('title'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'date_of_birth' => $request->filled('date_of_birth') ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_of_birth'))->format('Y-m-d') : $this->generateRandomDateOfBirth(),
            'gender' => $request->input('gender'),
            'id_number' => $idNumber,
            'nationality' => $this->normalizeOptionalText($request->input('nationality')) ?? 'Motswana',
            'relation' => $this->normalizeOptionalText($request->input('relation')) ?? 'Relative',
            'status' => $this->normalizeOptionalText($request->input('status')) ?? 'Current',
            'sponsor_filter_id' => $request->filled('sponsor_filter_id') ? (int) $request->input('sponsor_filter_id') : null,
            'phone' => $phone,
            'telephone' => $telephone,
            'profession' => $this->normalizeOptionalText($request->input('profession')),
            'work_place' => $this->normalizeOptionalText($request->input('work_place')),
            'last_updated_by' => $this->normalizeOptionalText($request->input('last_updated_by')) ?? auth()->user()->full_name ?? 'System',
        ];

        if (Schema::hasColumn('sponsors', 'year')) {
            $payload['year'] = $request->filled('year') ? (int) $request->input('year') : (int) now()->year;
        }

        return $payload;
    }
    
    public function create(){
        try {
            $nationalities = CacheHelper::getNationalities();
            $filters = CacheHelper::getSponsorFilterList();
            return view('sponsors.sponsor-new', compact('nationalities', 'filters'));
        } catch (Throwable $e) {
            return back()->with('error', 'An error occurred while loading the page.');
        }
    }

    public function store(Request $request) {
        $validator = $this->sponsorValidator($request);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        try {
            DB::beginTransaction();

            $sponsor = Sponsor::create(array_merge(
                ['connect_id' => $this->generateConnectId()],
                $this->prepareSponsorPayload($request)
            ));
    
            CacheHelper::forgetSponsorsData();
            DB::commit();
            Log::info('Sponsor created successfully', [
                'sponsor_id' => $sponsor->id,
                'created_by' => $request->last_updated_by ?? auth()->user()->full_name ?? 'System'
            ]);
    
            return redirect()->route('sponsors.sponsor-edit', $sponsor->id)->with('message', 'Sponsor created successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sponsor: ' . $e->getMessage(), [
                'data' => $request->except(['_token']),
                'error' => $e->getMessage()
            ]);
    
            return redirect()
                ->back()
                ->with('error', 'An error occurred while creating the sponsor. Please try again.')
                ->withInput();
        }
    }

    public static function formatPhoneNumber($number, $isMobile = true){
        $number = preg_replace('/[^0-9]/', '', $number);
    
        if (strlen($number) == 8 && $isMobile) {
            $number = '00267' . $number;
        } elseif (strlen($number) == 7 && !$isMobile) {
            $number = '00267' . $number;
        } elseif (strlen($number) == 11) {
            $number = '00' . $number;
        } elseif (strlen($number) == 13 && str_starts_with($number, '00267')) {
            return $number;
        } else {
            throw new \InvalidArgumentException('Invalid phone number format');
        }
        if ($isMobile) {
            if (!preg_match('/^00267[7][1-9][0-9]{6}$/', $number)) {
                throw new \InvalidArgumentException('Invalid mobile number format');
            }
        } else {
            if (!preg_match('/^00267[2-6][0-9]{6}$/', $number)) {
                throw new \InvalidArgumentException('Invalid telephone number format');
            }
        }
        return $number;
    }

    
    public function edit($id){
        try {
            $sponsor = Sponsor::with([
                'students' => function ($query) {
                    $query->with('currentClassRelation') 
                        ->where('status', 'Current')
                        ->select('id', 'sponsor_id', 'first_name', 'last_name', 'status');
                },
                'messages' => function ($query) {
                    $query->select('id', 'sponsor_id', 'body', 'sms_count', 'created_at');
                },
                'otherInformation'
            ])->findOrFail($id);
    
            $data = [
                'sponsor' => $sponsor,
                'nationalities' => CacheHelper::getNationalities(),
                'school_data' => cache()->remember('school_setup', now()->addDay(), function () {
                    return SchoolSetup::select('id', 'school_name')->first();
                }),
                'filters' => CacheHelper::getSponsorFilterList(),
            ];
    
            return view('sponsors.sponsors-view', $data);
    
        } catch (ModelNotFoundException $e) {
            Log::warning("Sponsor not found (ID: {$id})");
            return back()->with('error', 'Sponsor not found.');
                
        } catch (Throwable $e) {
            Log::error("Error loading sponsor for editing", [
                'sponsor_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while loading the sponsor details.');
        }
    }

    public function update(Request $request, $id){
        $validator = $this->sponsorValidator($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $sponsor = Sponsor::findOrFail($id);

            $updateData = $this->prepareSponsorPayload($request, $sponsor);

            if (!$sponsor->connect_id) {
                $updateData['connect_id'] = $this->generateConnectId();
            }

            $sponsor->update($updateData);
            CacheHelper::forgetSponsorsData();
            DB::commit();

            Log::info('Sponsor updated successfully', [
                'sponsor_id' => $sponsor->id,
                'updated_by' => $updateData['last_updated_by'],
            ]);

            return redirect()->route('sponsors.sponsor-edit', $sponsor->id)->with('message','Sponsor updated successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sponsor: '.$e->getMessage(), [
                'sponsor_id' => $id,
                'data'       => $request->except('_token'),
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);
            return redirect()->back()->with('error','An error occurred while updating the sponsor. Please try again.')->withInput();
        }
    }

    public function storeOrUpdate(Request $request, $sponsorId){
        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string|max:255',
            'family_situation' => 'nullable|string|max:255',
            'issues_to_note' => 'nullable|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput(); 
        }
    
        try {
            $otherInformation = OtherInformation::firstOrNew(['sponsor_id' => $sponsorId]);
    
            $otherInformation->address = $request->input('address');  
            $otherInformation->family_situation = $request->input('family_situation');
            $otherInformation->issues_to_note = $request->input('issues_to_note');
            $otherInformation->save();
    
    
            return back()->with('message', 'Information saved successfully.');
    
        } catch (Throwable $e) {
            Log::error("Error saving other information for sponsor (ID: {$sponsorId}): " . $e->getMessage());
            return back()->with('error', 'An error occurred while saving the information. Please try again.');
        }
    }


    public function destroy($id){
        if (!is_numeric($id)) {
            return redirect()->back()->with('error','Invalid sponsor ID.');
        }

        $sponsor = Sponsor::find($id);

        if (!$sponsor) {
            return redirect()->back()->with('error','Sponsor not found.');
        }

        if ($sponsor->students()->exists()) {
            return redirect()->back()->with('error','Cannot delete sponsor because they have associated students. Delete the students first.');
        }

        try {
            DB::beginTransaction();
            $sponsor->delete();
            CacheHelper::forgetSponsorsData();
            DB::commit();

            return redirect()->back()->with('message', 'Sponsor deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sponsor: ' . $e->getMessage());
            return redirect()->back()->with('error','An error occurred while deleting the sponsor.');
        }
    }

    public function filterList(){
        try {
            $filters = CacheHelper::getSponsorFilterList();
            return view('sponsors.sponsors-settings', compact('filters'));
        } catch (\Exception $e) {
            Log::error('Error fetching filters: ' . $e->getMessage());
            return view('sponsors.sponsors-settings', [
                'filters' => collect(),
                'error' => 'Unable to fetch filters. Please try again later.'
            ]);
        }
    }

    public function storeFilter(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:4', 'max:255']
            ], [
                'name.required' => 'The filter name is required.',
                'name.min' => 'The filter name must be at least 4 characters.',
                'name.max' => 'The filter name cannot exceed 255 characters.'
            ]);
    
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
    
            if (SponsorFilter::where('name', $request->name)->exists()) {
                return back()->withInput()->withErrors(['name' => 'A filter with this name already exists.']);
            }
    
            SponsorFilter::create([
                'name' => trim($request->name)
            ]);

            CacheHelper::forgetSponsorFilterList();
            return back()->with('message', 'Filter added successfully!');
    
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
    
        } catch (\Exception $e) {
            Log::error('Error creating filter: ' . $e->getMessage());
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function updateFilter(Request $request, $id){
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:2', 'max:255']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $sponsorFilter = SponsorFilter::findOrFail($id);
            $exists = SponsorFilter::where('name', $request->name)
                ->where('id', '!=', $id)->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'errors'  => ['name' => ['A filter with this name already exists.']]
                ], 422);
            }

            $sponsorFilter->name = trim($request->name);
            $sponsorFilter->save();

            CacheHelper::forgetSponsorFilterList();
            return response()->json([
                'success' => true,
                'message' => 'Filter updated successfully',
                'data'    => $sponsorFilter
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating filter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function destroyFilter($id){
        try {
            if (!is_numeric($id)) {
                return back()->with('error', 'Invalid filter ID provided');
            }

            $filter = SponsorFilter::findOrFail($id);
            $filter->delete();
            
            CacheHelper::forgetSponsorFilterList();
            return back()->with('message', 'Filter deleted successfully');

        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Filter not found');

        } catch (\Exception $e) {
            Log::error('Error deleting filter: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred while deleting the filter');
        }
    }

    public function sponsorsAnalyisList(){
        try{
            $sponsors = Sponsor::where('status','Current')->get();
            $school_data = SchoolSetup::first();

            return view('sponsors.sponsors-analysis',['sponsors' => $sponsors,'school_data' => $school_data]);
        }catch(\Exception $e){
            redirect()->back()->with('message','Error occurred'. $e->getMessage());
        }
    }


    public function sponsorsAnalyisListExport(){
        try{
            $data = Sponsor::where('status','Current')->get();
            return Excel::download(new ParentsAnalysisListExport($data),'sponsors-analysis-list.xlsx');
        }catch(\Exception $e){
            redirect()->back()->with('message','Error occurred'. $e->getMessage());
        }
    }

    public function sponsorsContactsDetails(){
        $sponsors = CacheHelper::getSponsors();
        $school_data = SchoolSetup::first();
        return view('sponsors.sponsors-contact-details',['sponsors' => $sponsors,'school_data' => $school_data]);
    }

    public function sponsorsContactsDetailsExport(){
        try{
            $data = CacheHelper::getSponsors();
        return Excel::download(new ParentsAnalysisContactListExport($data),'parents-contacts-list.xlsx');
        }catch(\Exception $e){
            redirect()->back()->with('message','Error occurred'. $e->getMessage());
        }
    }

    public function sponsorsChildrenList(){
        try{
            $sponsors = Sponsor::withCount('students AS student_count')->where('status','Current')->get();
            $school_data = SchoolSetup::first();

            return view('sponsors.sponsors-children-analysis',['sponsors' => $sponsors,'school_data' => $school_data]);
        }catch(\Exception $e){
            redirect()->back()->with('message','Error occurred'. $e->getMessage());
        }
    }

    public function getSponsorsByStudentTermReport(Request $request){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $sponsors = Sponsor::whereHas('students', function ($query) use ($selectedTermId) {
            $query->whereHas('terms', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            });
        })->get();

        $reportData = $sponsors->map(function ($sponsor) {
            return [
                'connect_id'    => $sponsor->connect_id,
                'title'         => $sponsor->title,
                'first_name'    => $sponsor->first_name,
                'last_name'     => $sponsor->last_name,
                'email'         => $sponsor->email,
                'gender'        => $sponsor->gender,
                'date_of_birth' => $sponsor->date_of_birth,
                'nationality'   => $sponsor->nationality,
                'relation'      => $sponsor->relation,
                'status'        => $sponsor->status,
                'id_number'     => $sponsor->id_number,
                'phone'         => $sponsor->phone,
                'profession'    => $sponsor->profession,
                'work_place'    => $sponsor->work_place,
                'year'          => $sponsor->created_at->format('Y'),
            ];
        })->toArray();

        if ($request->query('export') === 'excel') {
            return Excel::download(new SponsorImportExportReport($reportData), 'sponsor_students_term_report.xlsx');
        }

        $school_data = SchoolSetup::first();
        return view('sponsors.sponsors-import-list', compact('reportData', 'school_data'));
    }
    
}
