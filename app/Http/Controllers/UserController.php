<?php

namespace App\Http\Controllers;

use App\Exports\StaffAnalysisListExport;
use App\Helpers\CacheHelper;
use App\Models\Department;
use App\Models\EarningBand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\SchoolSetup;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\pdf;
use Illuminate\Support\Str;
use App\Models\Qualification;
use App\Models\QualificationUser;
use App\Models\WorkHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Exports\StaffAnalysisByDepartmentExport;
use App\Exports\StaffAnalysisByQualificationsExport;
use App\Exports\StaffCustomReportExport;
use App\Helpers\TermHelper;
use App\Models\Grade;
use Illuminate\Http\UploadedFile;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\SMSApiSetting;
use App\Models\StaffProfileSetting;
use App\Models\Nationality;
use App\Models\RecipientChannelConsent;
use App\Models\Sponsor;
use App\Models\UserFilter;
use App\Models\WhatsappTemplate;
use App\Services\Messaging\CommunicationChannelService;
use App\Services\Messaging\RecipientChannelConsentService;
use App\Services\Messaging\WhatsAppMessagingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller{

    public function __construct(){
        $this->middleware('auth');
    }

    public function index(Request $request){
        $departments = CacheHelper::getDepartments();
        $statuses    = DB::table('users_status')->select('id', 'name')->get();
        $positions   = DB::table('user_positions')->select('id', 'name')->get();
        
        $this->ensureDefaultPositionsExist();
        
        if (
            !$request->has('status') &&
            !$request->has('gender') &&
            !$request->has('position') &&
            !$request->has('department')
        ) {
            $users = CacheHelper::getStaff();
        } else {
            $users = User::query()->select([
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
            ])->get();
        }
        
        if (!Gate::allows('view-system-admin')) {
            $currentUserEmail = auth()->user()->email;
            $restrictedDomains = ['imagelife.co', 'heritagepro.co'];
            $specialEmail = 'obscure852@gmail.com';
            
            $users = $users->filter(function ($user) use ($currentUserEmail, $restrictedDomains, $specialEmail) {
                $domain = substr(strrchr($user->email, '@'), 1);
                $isRestrictedUser = in_array($domain, $restrictedDomains);
                
                if ($user->email === $currentUserEmail) {
                    return true;
                }
                
                if ($currentUserEmail === $specialEmail) {
                    return true;
                }
                
                if ($isRestrictedUser) {
                    return false;
                }
            
                return true;
            });
        }
        
        $statusMap     = $statuses->pluck('name', 'id');
        $positionMap   = $positions->pluck('name', 'id');
        $departmentMap = $departments->pluck('name', 'id');

        // Name/email search filter
        if ($name = $request->input('name')) {
            $searchTerm = strtolower(trim($name));
            $users = $users->filter(function($user) use ($searchTerm) {
                $fullName = strtolower($user->full_name ?? ($user->firstname . ' ' . $user->lastname));
                $email = strtolower($user->email ?? '');
                return str_contains($fullName, $searchTerm) || str_contains($email, $searchTerm);
            });
        }

        if ($statusId = $request->input('status')) {
            $statusName = $statusMap[$statusId] ?? null;
            if ($statusName) {
                $users = $users->where('status', $statusName);
            }
        }
        if ($positionId = $request->input('position')) {
            $positionName = $positionMap[$positionId] ?? null;
            if ($positionName) {
                $users = $users->where('position', $positionName);
            }
        }
        if ($departmentId = $request->input('department')) {
            $departmentName = $departmentMap[$departmentId] ?? null;
            if ($departmentName) {
                $users = $users->where('department', $departmentName);
            }
        }
        
        $potentialDuplicates = $users->groupBy(function($user) {
            return strtolower($user->firstname) . '|' . strtolower($user->lastname);
        })->filter(function($group) {
            return $group->count() > 1;
        })->flatten();
        
        $users = $users->values();
        
        if ($request->ajax()) {
            return response()->json([
                'tableHtml' => view('staff.partials.users-list', compact('users'))->render(),
                'totalStaff' => $users->count(),
                'duplicateStaff' => $potentialDuplicates->count(),
                'duplicateTooltip' => $this->generateDuplicateTooltip($potentialDuplicates)
            ]);
        }
        
        return view('staff.index', compact('users', 'departments', 'statuses', 'positions', 'potentialDuplicates'));
    }

   private function generateDuplicateTooltip($duplicates){
        if ($duplicates->isEmpty()) {
            return '';
        }
        
        $content = '<strong>Possible Duplicates:</strong><br>';
        $duplicateNames = [];
        
        foreach ($duplicates as $staff) {
            $duplicateNames[] = $staff->full_name . ' (' . $staff->email . ')';
        }
        
        return $content . implode('<br>', array_unique($duplicateNames));
    }

    private function ensureDefaultPositionsExist(){
        $positions = [
            'Other', 'Security Officer', 'Gatekeeper', 'Driver', 
            'Kitchen Assistant', 'Cook', 'Messenger','Librarian',
            'Library Assistant'
        ];
        
        foreach ($positions as $position) {
            if (!DB::table('user_positions')->where('name', $position)->exists()) {
                DB::table('user_positions')->insert([
                    'name' => $position,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    public function organogram(){
        $schoolHead = User::all()->first(function ($user) {
            return $user->isSchoolHead();
        });

        if (!$schoolHead) {
            return redirect()->back()->with('error', 'School Head not found.');
        }

        $school_data = SchoolSetup::first();
        $organogram = $this->buildOrganogram($schoolHead);
        return view('staff.organogram', ['organogram' => $organogram, 'school_data' => $school_data]);
    }

    private function buildOrganogram(User $user, array &$processed = []){
        if (in_array($user->id, $processed)) {
            return null;
        }

        $processed[] = $user->id;
        $node = [
            'id' => $user->id,
            'name' => $user->fullName,
            'title' => $user->position,
            'children' => []
        ];

        $subordinates = User::where('reporting_to', $user->id)->get();
        foreach ($subordinates as $subordinate) {
            $childNode = $this->buildOrganogram($subordinate, $processed);
            if ($childNode !== null) {
                $node['children'][] = $childNode;
            }
        }
        return $node;
    }


    public function getUsersByAreaOfWork(){
        try {
            $users = User::where('status', 'current')->where('active', 1)->get();
            $groupedUsers = $users->groupBy('area_of_work');
            $pdf = PDF::loadView('staff.area-of-work-pdf', ['groupedUsers' => $groupedUsers]);

            $pdfPath = 'storage/pdf/area-of-work-file.pdf';
            $pdf->save(public_path($pdfPath));

            return $pdf->stream('area-of-work-file.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function create(){
        try {
            $nationalities = CacheHelper::getNationalities();
            $departments = CacheHelper::getDepartments();
            $areaOfWork = DB::table('area_of_work')->get();
            $positions = DB::table('user_positions')->get();
            $earningBands = $this->earningBands();
            $users = User::query()
                ->select('id', 'firstname', 'lastname', 'position', 'active', 'status')
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->get();

            if ($nationalities === null || $departments === null || $areaOfWork->isEmpty() || $positions->isEmpty()) {
                Log::warning(
                    "Some data could not be loaded in staff.user-new create: " .
                        ($nationalities === null ? "Nationalities null. " : "") .
                        ($departments === null ? "Departments null. " : "") .
                        ($areaOfWork->isEmpty() ? "Area of Work empty. " : "") .
                        ($positions->isEmpty() ? "Positions empty." : "")
                );

                return back()->with('error', 'Some required data could not be loaded.');
            }

            return view('staff.user-new', [
                'nationalities' => $nationalities,
                'departments' => $departments,
                'area_of_work' => $areaOfWork,
                'positions' => $positions,
                'earningBands' => $earningBands,
                'users' => $users,
            ]);
        } catch (Throwable $e) {
            Log::error('Error in staff.user-new create: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading the page.');
        }
    }

    public function store(Request $request){
        $request->merge([
            'department' => $this->resolveDepartmentName($request->input('department')),
            'position' => $this->resolvePositionName($request->input('position')),
            'reporting_to' => $this->normalizeNullableInteger($request->input('reporting_to')),
            'dpsm_personal_file_number' => $this->normalizeNullableString($request->input('dpsm_personal_file_number')),
            'earning_band' => $this->normalizeNullableString($request->input('earning_band')),
        ]);

        $earningBandRules = $this->earningBandValidationRules();

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'avatar' => 'nullable|image',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'gender' => 'required|in:M,F',
            'department' => 'required|string',
            'position' => 'required|string|max:255',
            'reporting_to' => 'nullable|integer|exists:users,id',
            'area_of_work' => 'required|string|max:255',
            'personal_payroll_number' => 'nullable|string|max:255',
            'dpsm_personal_file_number' => 'nullable|string|max:255',
            'date_of_appointment' => 'nullable|date_format:d/m/Y',
            'earning_band' => $earningBandRules,
            'nationality' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'id_number' => ['required', 'string', 'unique:users'],
            'status' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => 'nullable|string|min:8',
            'last_updated_by' => 'nullable|integer',
            'year' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Convert dd/mm/yyyy to Y-m-d for storage
        $request->merge([
            'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
            'date_of_appointment' => $this->normalizeOptionalDate($request->input('date_of_appointment')),
        ]);

        try {
            $randomPassword = $this->generateComplexPassword(8);
            $userData = $request->only(User::getFillableAttributes());
            
            $userData['firstname'] = ucfirst(strtolower($userData['firstname']));
            $userData['lastname'] = ucfirst(strtolower($userData['lastname']));
            $userData['email'] = strtolower($userData['email']);
            
            if (!empty($userData['middlename'])) {
                $userData['middlename'] = ucfirst(strtolower($userData['middlename']));
            }

            if ($request->hasFile('avatar')) {
                $userData['avatar'] = $this->processAvatarUpload($request->file('avatar'));
            }

            if (!$request->has('bypass_duplicate_check')) {
                $existingStaff = User::where('firstname', $request->firstname)
                    ->where('lastname', $request->lastname)
                    ->get();
    
                if ($existingStaff->isNotEmpty()) {
                    $message = "Staff member(s) with the same name already exists: ";
                    foreach ($existingStaff as $staff) {
                        $message .= "{$staff->fullName} ({$staff->email}, {$staff->position}). ";
                    }
                    $message .= "Please verify before adding a new staff with the same name.";
                    return back()->withInput()->with('error', $message);
                }
            }

            $userData['password'] = Hash::make($randomPassword);
            $user = User::create($userData);

            CacheHelper::forgetStaff();
            return redirect()->route('staff.staff-view', $user->id)->with('message', 'User created successfully!');
        } catch (Throwable $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while creating the user. Please try again.')->withInput();
        }
    }

    public function resetUserPassword($id){
        $user = User::findOrFail($id);
        $token = Password::createToken($user);
        try {
            $user->sendPasswordResetNotification($token);
            return redirect()->back()->with('message', 'Password reset link sent to ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset link to user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while sending the password reset link.');
        }
    }

    protected function generateComplexPassword($length = 8){
        $password = '';
        $password .= Str::random(2);
        $password .= chr(rand(65, 90));
        $password .= chr(rand(97, 122));
        $password .= rand(0, 9);
        while (strlen($password) < $length) {
            $password .= Str::random(1);
        }
        return str_shuffle($password);
    }


    public function usersByDepartment()
    {
        $school_data = SchoolSetup::first();
        $usersByDepartment = User::select('id', 'firstname', 'middlename', 'lastname', 'date_of_birth', 'gender', 'area_of_work', 'id_number', 'phone', 'department')->orderBy('department')->orderBy('firstname')->get()->groupBy('department');
        return view('staff.staff-analysis-department', ['usersByDepartment' => $usersByDepartment, 'school_data' => $school_data]);
    }

    public function usersByDepartmentExport()
    {
        $data = User::select('id', 'firstname', 'middlename', 'lastname', 'date_of_birth', 'gender', 'area_of_work', 'id_number', 'phone', 'department')->orderBy('department')->orderBy('firstname')->get()->groupBy('department');
        return Excel::download(new StaffAnalysisByDepartmentExport($data), 'staff-analysis-by-department.xlsx');
    }

    public function show($id){
        try {
            ini_set('memory_limit', '512M');
            $user = User::with([
                'qualifications' => function ($query) {
                    $query->select(
                        'qualifications.id',
                        'qualifications.qualification',
                        'qualifications.qualification_code',
                        'qualification_user.level',
                        'qualification_user.college',
                        'qualification_user.start_date',
                        'qualification_user.completion_date',
                        'qualification_user.user_id'
                    );
                },
                'logs' => function ($query) {
                    $query->select('id', 'user_id', 'location', 'ip_address', 'changes', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->take(100);
                },
                'workHistory' => function ($query) {
                    $query->select(
                        'id',
                        'user_id',
                        'role',
                        'workplace',
                        'type_of_work',
                        'start',
                        'end'
                    )
                        ->latest('start');
                },
                'roles:id,name,description'
            ])->select([
                'id',
                'firstname',
                'lastname',
                'middlename',
                'email',
                'date_of_birth',
                'gender',
                'department',
                'position',
                'avatar',
                'area_of_work',
                'personal_payroll_number',
                'dpsm_personal_file_number',
                'date_of_appointment',
                'earning_band',
                'nationality',
                'signature_path',
                'phone',
                'id_number',
                'status',
                'active',
                'reporting_to',
                'user_filter_id',
                'created_at',
                'updated_at'
            ])->findOrFail($id);

            $cacheTTL = now()->addHour();

            $users = Cache::remember('users_basic_info', $cacheTTL, function () {
                return User::where('active', 1)
                    ->whereNull('deleted_at')
                    ->select('id', 'firstname', 'lastname', 'position')
                    ->get();
            });

            if ($user->reporting_to && !$users->contains('id', $user->reporting_to)) {
                $reportingUser = User::select('id', 'firstname', 'lastname', 'position')->find($user->reporting_to);
                if ($reportingUser) {
                    $users->push($reportingUser);
                }
            }

            $filters = CacheHelper::getUserFilterList();

            if ($user->user_filter_id && !$filters->contains('id', $user->user_filter_id)) {
                $userFilter = UserFilter::select('id', 'name')->find($user->user_filter_id);
                if ($userFilter) {
                    $filters->push($userFilter);
                }
            }

            $user->logs->transform(function ($log) {
                $decodedChanges = is_array($log->changes) ? $log->changes : json_decode($log->changes ?? '[]', true);
                $log->changes = is_array($decodedChanges) ? $decodedChanges : [];
                return $log;
            });

            return view('staff.staff-view', [
                'user' => $user,
                'nationalities' => CacheHelper::getNationalities(),
                'departments' => CacheHelper::getDepartments(),
                'roles' => Role::whereNotIn('id', $user->roles->pluck('id'))
                    ->select('id', 'name', 'description')->get(),
                'qualifications' => $user->qualifications,
                'work_history' => $user->workHistory,
                'positions' => Cache::remember('user_positions', $cacheTTL, function () {
                    return DB::table('user_positions')
                        ->select('id', 'name')
                        ->get();
                }),
                'area_of_work' => Cache::remember('area_of_work', $cacheTTL, function () {
                    return DB::table('area_of_work')
                        ->select('id', 'name')
                        ->get();
                }),
                'states' => Cache::remember('user_states', $cacheTTL, function () {
                    return DB::table('users_status')
                        ->select('id', 'name')
                        ->get();
                }),
                'school_data' => Cache::remember('school_setup', $cacheTTL, function () {
                    return SchoolSetup::select('id', 'school_name')->first();
                }),
                'filters' => $filters,
                'users' => $users,
                'earningBands' => $this->earningBands($user->earning_band),
            ]);
        } catch (Throwable $e) {
            Log::error('Staff Profile Error', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true))
            ]);

            return back()->with('error', 'An error occurred while loading staff details.');
        }
    }


    private function formatBytes($bytes){
        $units = ['bytes', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return number_format($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    public function allocateRole(Request $request, $id){
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $user = auth()->user();
        try {
            $user = User::findOrFail($id);
            $request->validate([
                'role' => 'required|exists:roles,id',
            ]);

            if ($user->roles()->syncWithoutDetaching($request->input('role'))) {
                Cache::flush();
                return back()->with('message', 'Role allocated successfully!');
            } else {
                Log::info("Role allocation - role already attached to user (user ID: {$id}, role ID: {$request->input('role')})");
                Cache::flush();
                return back()->with('message', 'Role already allocated.');
            }
        } catch (Throwable $e) {
            Log::error("Error allocating role to user (ID: {$id}): " . $e->getMessage());
            return back()->with('error', 'An error occurred while allocating the role. Please try again.');
        }
    }

    public function update(Request $request, $id){
        $existingUser = User::query()->select('id', 'earning_band')->find($id);

        $request->merge([
            'department' => $this->resolveDepartmentName($request->input('department')),
            'position' => $this->resolvePositionName($request->input('position')),
            'reporting_to' => $this->normalizeNullableInteger($request->input('reporting_to')),
            'user_filter_id' => $this->normalizeNullableInteger($request->input('user_filter_id')),
            'dpsm_personal_file_number' => $this->normalizeNullableString($request->input('dpsm_personal_file_number')),
            'earning_band' => $this->normalizeNullableString($request->input('earning_band')),
        ]);

        $earningBandRules = $this->earningBandValidationRules($existingUser?->earning_band);

        $validator = Validator::make($request->all(), [
            'firstname'      => 'required|string|max:255',
            'middlename'     => 'nullable|string|max:255',
            'lastname'       => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('users')->ignore($id)],
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'date_of_birth'  => 'required|date_format:d/m/Y',
            'gender'         => 'required|in:M,F',
            'department'     => 'required|string',
            'position'       => 'required|string|max:255',
            'area_of_work'   => 'required|string|max:255',
            'personal_payroll_number' => 'nullable|string|max:255',
            'dpsm_personal_file_number' => 'nullable|string|max:255',
            'date_of_appointment' => 'nullable|date_format:d/m/Y',
            'earning_band' => $earningBandRules,
            'nationality'    => 'required|string|max:255',
            'phone'          => 'required|string|max:15',
            'id_number'      => ['required', 'string', 'max:50', Rule::unique('users')->ignore($id)],
            'city'           => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:500',
            'status'         => 'required|string|max:255',
            'user_filter_id' => 'nullable|integer',
            'reporting_to'   => 'nullable|integer|exists:users,id',
            'last_updated_by'=> 'nullable|integer',
            'year'           => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Convert dd/mm/yyyy to Y-m-d for storage
        $request->merge([
            'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
            'date_of_appointment' => $this->normalizeOptionalDate($request->input('date_of_appointment')),
        ]);

        try {
            $user = User::findOrFail($id);
            $data = $request->except(['username', 'password']);
            
            $data['firstname'] = ucfirst(strtolower($data['firstname']));
            $data['lastname'] = ucfirst(strtolower($data['lastname']));
            $data['email'] = strtolower($data['email']);
            
            if (!empty($data['middlename'])) {
                $data['middlename'] = ucfirst(strtolower($data['middlename']));
            }

            $oldAvatar = null;
            if ($request->hasFile('avatar')) {
                $oldAvatar = $user->avatar;
                $data['avatar'] = $this->processAvatarUpload($request->file('avatar'));
            }

            $data['active'] = !$request->has('disabled');
            $user->update($data);

            if ($oldAvatar) {
                $oldPath = str_replace('/storage/', '', $oldAvatar);
                Storage::disk('public')->delete($oldPath);
            }

            CacheHelper::forgetStaff();

            return redirect()->back()->with('message', 'User updated successfully.');
        } catch (Throwable $e) {
            Log::error("Error updating user (ID: {$id}): " . $e->getMessage());
            return back()->with('error', 'An error occurred while updating the user. Please try again.')->withInput();
        }
    }

    public function updateProfile(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name'   => 'required|max:255',
            'email'  => ['required', 'email', Rule::unique('users')->ignore($id)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['isSuccess' => false, 'errors' => $validator->errors()], 422);
        }
    
        try {
            $user = User::findOrFail($id);
    
            $oldAvatar = null;
            if ($request->hasFile('avatar')) {
                $oldAvatar = $user->avatar;
                $user->avatar = $this->processAvatarUpload($request->file('avatar'));
            }

            $user->username = ucfirst(strtolower($request->input('name')));
            $user->email    = strtolower($request->input('email'));
            $user->save();

            if ($oldAvatar) {
                $oldPath = str_replace('/storage/', '', $oldAvatar);
                Storage::disk('public')->delete($oldPath);
            }

            CacheHelper::forgetStaff();
    
            return response()->json([
                'isSuccess' => true,
                'message'   => 'Profile updated successfully!',
            ]);
        } catch (Throwable $e) {
            Log::error("Error updating user profile (ID: {$id}): " . $e->getMessage());
            return response()->json(['isSuccess' => false, 'message' => 'An error occurred while updating your profile.'], 500);
        }
    }

    private function resolveDepartmentName($departmentValue)
    {
        if ($departmentValue === null || $departmentValue === '') {
            return $departmentValue;
        }

        if (is_numeric($departmentValue)) {
            return Department::whereKey((int) $departmentValue)->value('name');
        }

        return trim($departmentValue);
    }

    private function resolvePositionName($positionValue)
    {
        if ($positionValue === null || $positionValue === '') {
            return $positionValue;
        }

        if (is_numeric($positionValue)) {
            return DB::table('user_positions')
                ->where('id', (int) $positionValue)
                ->value('name');
        }

        return trim($positionValue);
    }

    private function normalizeNullableInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }


    public function openProfile(){
        try {
            $nationalities = CacheHelper::getNationalities();
            $departments = CacheHelper::getDepartments();

            $user = User::with([
                'qualifications' => function ($query) {
                    $query->select(
                        'qualifications.id',
                        'qualifications.qualification',
                        'qualifications.qualification_code',
                        'qualification_user.level',
                        'qualification_user.college',
                        'qualification_user.start_date',
                        'qualification_user.completion_date',
                        'qualification_user.user_id'
                    );
                },
                'workHistory' => function ($query) {
                    $query->select('id', 'user_id', 'role', 'workplace', 'type_of_work', 'start', 'end')
                        ->latest('start');
                },
                'logs' => function ($query) {
                    $query->select('id', 'user_id', 'location', 'ip_address', 'changes', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->take(100);
                },
                'receivedEmails' => function ($query) {
                    $query->select('id', 'user_id', 'sender_id', 'subject', 'body', 'status', 'created_at')
                        ->with('sender:id,firstname,lastname')
                        ->orderBy('created_at', 'desc')
                        ->take(100);
                },
                'messages' => function ($query) {
                    $query->select('id', 'user_id', 'body', 'sms_count', 'status', 'delivery_status', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->take(100);
                },
            ])->findOrFail(Auth::id());

            $user->logs->transform(function ($log) {
                $decodedChanges = is_array($log->changes) ? $log->changes : json_decode($log->changes ?? '[]', true);
                $log->changes = is_array($decodedChanges) ? $decodedChanges : [];
                return $log;
            });

            $positions = DB::table('user_positions')->get();
            $qualifications = Qualification::select('id', 'qualification', 'qualification_code')->get();
            $school_data = SchoolSetup::first();
            $logsCount = $user->logs->count();

            $userQualifications = QualificationUser::where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->with('qualification:id,qualification,qualification_code')
                ->get();

            return view('profile.user-profile', [
                'nationalities' => $nationalities,
                'user' => $user,
                'departments' => $departments,
                'positions' => $positions,
                'earningBands' => $this->earningBands($user->earning_band),
                'qualifications' => $qualifications,
                'userQualifications' => $userQualifications,
                'school_data' => $school_data,
                'logsCount' => $logsCount,
            ]);
        } catch (Throwable $e) {
            Log::error('Error in profile.user-profile: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading your profile.');
        }
    }

    public function updateProfileDetails(Request $request, User $user){
        $request->merge([
            'personal_payroll_number' => $this->normalizeNullableString($request->input('personal_payroll_number')),
            'dpsm_personal_file_number' => $this->normalizeNullableString($request->input('dpsm_personal_file_number')),
            'earning_band' => $this->normalizeNullableString($request->input('earning_band')),
        ]);

        $earningBandRules = $this->earningBandValidationRules($user->earning_band);

        $validated = $request->validate([
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'id_number' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:191',
            'nationality' => 'required|string|max:191',
            'address' => 'nullable|string|max:191',
            'personal_payroll_number' => 'nullable|string|max:255',
            'dpsm_personal_file_number' => 'nullable|string|max:255',
            'date_of_appointment' => 'nullable|date',
            'earning_band' => $earningBandRules,
        ]);

        // Convert dd/mm/yyyy to Y-m-d for storage
        $validated['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['date_of_birth'])->format('Y-m-d');

        try {
            DB::beginTransaction();
            if (isset($validated['phone'])) {
                if (preg_match('/^7\d{7}$/', $validated['phone'])) {
                    $validated['phone'] = '00267' . $validated['phone'];
                }
            }

            $validated['last_updated_by'] = auth()->user()->full_name;
            $user->update($validated);

            DB::commit();
            return redirect()->back()->with('message', 'Profile updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Profile update failed: ' . $e->getMessage());
        }
    }

    public function updateProfileAvatar(Request $request){
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $user = User::findOrFail(Auth::id());

            $oldAvatar = $user->avatar;
            $user->avatar = $this->processAvatarUpload($request->file('avatar'));
            $user->save();

            if ($oldAvatar) {
                $oldPath = str_replace('/storage/', '', $oldAvatar);
                Storage::disk('public')->delete($oldPath);
            }

            CacheHelper::forgetStaff();

            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully!',
                'avatar_url' => asset('storage/' . $user->avatar),
            ]);
        } catch (Throwable $e) {
            Log::error('Error updating profile avatar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your avatar.',
            ], 500);
        }
    }

    public function storeProfileQualification(Request $request){
        $validated = $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
            'level' => 'required|string|max:191',
            'college' => 'required|string|max:191',
            'start_date' => 'required|date',
            'completion_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            QualificationUser::create([
                'user_id' => Auth::id(),
                'qualification_id' => $validated['qualification_id'],
                'level' => $validated['level'],
                'college' => $validated['college'],
                'start_date' => $validated['start_date'],
                'completion_date' => $validated['completion_date'],
            ]);

            return redirect()->back()->with('message', 'Qualification added successfully.');
        } catch (Throwable $e) {
            Log::error('Error storing profile qualification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while adding the qualification.');
        }
    }

    public function updateProfileQualification(Request $request, $id){
        $validated = $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
            'level' => 'required|string|max:191',
            'college' => 'required|string|max:191',
            'start_date' => 'required|date',
            'completion_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $qualification = QualificationUser::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $qualification->update($validated);

            return redirect()->back()->with('message', 'Qualification updated successfully.');
        } catch (Throwable $e) {
            Log::error('Error updating profile qualification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the qualification.');
        }
    }

    public function destroyProfileQualification($id){
        try {
            $qualification = QualificationUser::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $qualification->delete();

            return redirect()->back()->with('message', 'Qualification removed successfully.');
        } catch (Throwable $e) {
            Log::error('Error deleting profile qualification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while removing the qualification.');
        }
    }

    public function storeProfileWorkHistory(Request $request){
        $validated = $request->validate([
            'workplace' => 'required|string|max:191',
            'type_of_work' => 'required|string|max:191',
            'role' => 'required|string|max:191',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        try {
            WorkHistory::create([
                'user_id' => Auth::id(),
                'workplace' => $validated['workplace'],
                'type_of_work' => $validated['type_of_work'],
                'role' => $validated['role'],
                'start' => $validated['start'],
                'end' => $validated['end'] ?? null,
            ]);

            return redirect()->back()->with('message', 'Work history added successfully.');
        } catch (Throwable $e) {
            Log::error('Error storing profile work history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while adding work history.');
        }
    }

    public function updateProfileWorkHistory(Request $request, $id){
        $validated = $request->validate([
            'workplace' => 'required|string|max:191',
            'type_of_work' => 'required|string|max:191',
            'role' => 'required|string|max:191',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        try {
            $workHistory = WorkHistory::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $workHistory->update($validated);

            return redirect()->back()->with('message', 'Work history updated successfully.');
        } catch (Throwable $e) {
            Log::error('Error updating profile work history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating work history.');
        }
    }

    public function destroyProfileWorkHistory($id){
        try {
            $workHistory = WorkHistory::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $workHistory->delete();

            return redirect()->back()->with('message', 'Work history removed successfully.');
        } catch (Throwable $e) {
            Log::error('Error deleting profile work history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while removing work history.');
        }
    }

    public function uploadSignature(Request $request, $id){
        $request->validate([
            'signature' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],
        ]);

        try {
            $user = User::findOrFail($id);
            if ($request->hasFile('signature')) {
                $image = $request->file('signature');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/users/signatures', $imageName);
                $user->signature_path = Storage::url($path);
                $user->save();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Signature uploaded successfully!',
                        'signature_path' => $user->signature_path,
                    ]);
                }

                return back()->with('message', 'Signature uploaded successfully!');
            } else {
                Log::warning("Signature upload attempted without a file (user ID: {$id})");
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No signature file selected.',
                    ], 422);
                }
                return back()->with('error', 'No signature file selected.');
            }
        } catch (Throwable $e) {
            Log::error("Error uploading signature (user ID: {$id}): " . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while uploading your signature. Please try again.',
                ], 500);
            }
            return back()->with('error', 'An error occurred while uploading your signature.  Please try again.');
        }
    }


    public function smsSignature(Request $request, $id){
        $request->validate([
            'sms_signature' => ['required', 'string', 'max:255'],
        ]);

        try {
            $user = User::findOrFail($id);
            $user->sms_signature = $request->input('sms_signature');
            $user->save();

            return back()->with('message', 'SMS signature updated successfully!');
        } catch (Throwable $e) {
            Log::error("Error updating SMS signature (user ID: {$id}): " . $e->getMessage());
            return back()->with('error', 'An error occurred while updating your SMS signature. Please try again.');
        }
    }

    public function staffCustomAnalysis()
    {
        $school_data = SchoolSetup::first();
        $areas_of_work = DB::table('area_of_work')->get();
        return view('staff.staff-custom-analysis', ['school_data' => $school_data, 'areas_of_work' => $areas_of_work]);
    }

    public function getUserFields()
    {
        $fields = [
            'firstname' => 'First Name',
            'middlename' => 'Middle Name',
            'lastname' => 'Last Name',
            'email' => 'Email',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'position' => 'Position',
            'department' => 'Department',
            'area_of_work' => 'Area of Work',
            'nationality' => 'Nationality',
            'phone' => 'Phone',
            'id_number' => 'ID Number',
            'city' => 'City',
            'address' => 'Address',
            'active' => 'Active',
            'status' => 'Status',
            'year' => 'Year',
            'roles' => 'Roles',
            'klasses' => 'Classes',
            'qualifications' => 'Qualifications',
            'filter' => 'Filter'
        ];
        return response()->json($fields);
    }


    public function generateUserReport(Request $request)
    {
        try {
            $areaOfWork = $request->area_of_work;
            $fields = $request->fields;
            $school_data = SchoolSetup::first();

            $field_headers = [
                'firstname' => 'First Name',
                'middlename' => 'Middle Name',
                'lastname' => 'Last Name',
                'email' => 'Email',
                'date_of_birth' => 'Date of Birth',
                'gender' => 'Gender',
                'position' => 'Position',
                'department' => 'Department',
                'area_of_work' => 'Area of Work',
                'nationality' => 'Nationality',
                'phone' => 'Phone',
                'id_number' => 'ID Number',
                'city' => 'City',
                'address' => 'Address',
                'active' => 'Active',
                'status' => 'Status',
                'year' => 'Year',
                'roles' => 'Roles',
                'klasses' => 'Classes',
                'qualifications' => 'Qualifications',
                'filter' => 'Filters'
            ];

            $userFields = array_filter($fields, function ($field) {
                return in_array($field, [
                    'firstname',
                    'middlename',
                    'lastname',
                    'email',
                    'date_of_birth',
                    'gender',
                    'position',
                    'department',
                    'area_of_work',
                    'nationality',
                    'phone',
                    'id_number',
                    'city',
                    'address',
                    'filter',
                    'active',
                    'status',
                    'year'
                ]);
            });

            $userFields[] = 'id';
            $usersQuery = User::with([
                'roles' => function ($query) {
                    $query->select('roles.id', 'roles.name');
                },
                'klasses' => function ($query) {
                    $query->whereHas('klass', function ($query) {
                        $query->select('klasses.name');
                    });
                },
                'qualifications' => function ($query) {
                    $query->select('qualification_user.id', 'qualification_user.qualification_id', 'qualification_user.college', 'qualification_user.level');
                }
            ])->where('area_of_work', $areaOfWork)->select($userFields);

            $users = $usersQuery->get();

            if ($request->has('export_excel')) {
                return Excel::download(new StaffCustomReportExport($users, $fields, $field_headers), 'staff-custom-report-export.xlsx');
            }

            return view('staff.staff-custom-report', compact('users', 'fields', 'field_headers', 'school_data'));
        } catch (\Exception $e) {
            Log::error('Error generating user report:', ['message' => $e->getMessage()]);
            return redirect()->back()->with('message', 'Error occurred: ' . $e->getMessage());
        }
    }

    function analysisReport()
    {
        try {
            $users = User::where('active', true)->where('status', 'Current')->get();
            $school_data = SchoolSetup::first();
            return view('staff.staff-analysis-list', ['users' => $users, 'school_data' => $school_data]);
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function staffByFilters()
    {
        $school_data = SchoolSetup::first();
        $users = User::with('filter')
            ->select('id', 'firstname', 'lastname', 'date_of_birth', 'gender', 'email', 'position', 'phone', 'nationality', 'user_filter_id')
            ->orderBy('lastname')->get();

        $usersGroupedByFilter = $users->groupBy(function ($user) {
            return $user->user_filter_id ? UserFilter::findOrFail($user->user_filter_id)->name : 'No Filter';
        });

        return view('staff.staff-by-filters-list', [
            'usersGroupedByFilter' => $usersGroupedByFilter,
            'school_data' => $school_data
        ]);
    }

    function analysisReportExport()
    {
        try {
            $data = User::where('active', true)->where('status', 'Current')->get();
            return Excel::download(new StaffAnalysisListExport($data), 'staff-analysis-list.xlsx');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function getUsersGroupedByAreaOfWork()
    {
        try {
            $school_data = SchoolSetup::first();
            $users = User::select('id', 'firstname', 'lastname', 'gender', 'position', 'phone', 'area_of_work', 'nationality')->orderBy('area_of_work')
                ->get()
                ->groupBy('area_of_work');
            return view('staff.staff-analysis-work-list', ['usersGrouped' => $users, 'school_data' => $school_data]);
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function getUsersGroupedByRoles()
    {
        try {
            $school_data = SchoolSetup::first();
            $users = User::with('roles')->get()->groupBy(function ($user) {
                return $user->roles->isEmpty() ? 'No Role' : $user->roles[0]->name;
            });
            return view('staff.staff-analysis-roles', ['usersGrouped' => $users, 'school_data' => $school_data]);
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }


    public function userQualificationsReport()
    {
        try {
            $qualifications = Qualification::with('users')->get();
            $school_data = SchoolSetup::first();
            return view('staff.staff-analysis-qualifications', ['qualifications' => $qualifications, 'school_data' => $school_data]);
        } catch (\Exception $e) {
            redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function userQualificationsExport()
    {
        try {
            $data = Qualification::with('users')->get();
            return Excel::download(new StaffAnalysisByQualificationsExport($data), 'Staff-analysis-by-qualifications.xlsx');
        } catch (\Exception $e) {
            redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function userNationalityReport()
    {
        $school_data = SchoolSetup::first();
        $maleUsersByNationality = DB::table('users')
            ->select('nationality', DB::raw('count(*) as total'))
            ->where('gender', 'M')
            ->groupBy('nationality')
            ->pluck('total', 'nationality');

        $femaleUsersByNationality = DB::table('users')
            ->select('nationality', DB::raw('count(*) as total'))
            ->where('gender', 'F')
            ->groupBy('nationality')
            ->pluck('total', 'nationality');

        $usersByNationality = DB::table('users')
            ->select('nationality')
            ->groupBy('nationality')
            ->get()
            ->map(function ($item) use ($maleUsersByNationality, $femaleUsersByNationality) {
                return [
                    'nationality' => $item->nationality,
                    'male' => $maleUsersByNationality[$item->nationality] ?? 0,
                    'female' => $femaleUsersByNationality[$item->nationality] ?? 0
                ];
            });
        return view('staff.staff-nationality', ['usersByNationality' => $usersByNationality, 'school_data' => $school_data]);
    }

    public function qualificationsReport()
    {
        try {
            $school_data = SchoolSetup::first();
            $report = Qualification::withCount('users')->get()
                ->map(function ($qualification) {
                    return [
                        'qualification' => $qualification->qualification,
                        'qualification_code' => $qualification->qualification_code,
                        'user_count' => $qualification->users_count
                    ];
                });
            return view('staff.staff-statistics-qualifications', ['report' => $report, 'school_data' => $school_data]);
        } catch (\Exception $e) {
            redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function staffSettings(){
        $departments = Department::all();
        $filters = CacheHelper::getUserFilterList();
        $qualifications = Qualification::all();
        $earningBands = $this->earningBands();

        $forceUpdateEnabled = StaffProfileSetting::isForceUpdateEnabled();
        $forceUpdateSections = StaffProfileSetting::getRequiredSections();

        return view('staff.staff-settings', [
            'departments' => $departments,
            'filters' => $filters,
            'qualifications' => $qualifications,
            'earningBands' => $earningBands,
            'forceUpdateEnabled' => $forceUpdateEnabled,
            'forceUpdateSections' => $forceUpdateSections,
            'profileSectionOptions' => StaffProfileSetting::SECTIONS,
        ]);
    }

    public function updateForceProfileSetting(Request $request){
        $validated = $request->validate([
            'force_update_enabled' => 'required|boolean',
            'required_sections' => 'nullable|array',
            'required_sections.*' => 'in:basic_info,employment_details,qualifications,work_history',
        ]);

        $enabled = (bool) $validated['force_update_enabled'];
        $sections = $validated['required_sections'] ?? ['basic_info'];

        if ($enabled && empty($sections)) {
            $sections = ['basic_info'];
        }

        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, $enabled, Auth::id());
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, $sections, Auth::id());

        Cache::forget('force_profile_update_enabled');
        Cache::forget('force_profile_required_sections');

        return redirect()->back()->with('message', 'Force profile update settings saved successfully.');
    }

    public function showCompleteProfile(){
        $user = User::with([
            'qualifications' => function ($query) {
                $query->select(
                    'qualifications.id',
                    'qualifications.qualification',
                    'qualifications.qualification_code',
                    'qualification_user.level',
                    'qualification_user.college',
                    'qualification_user.start_date',
                    'qualification_user.completion_date',
                    'qualification_user.user_id'
                );
            },
            'workHistory' => function ($query) {
                $query->select('id', 'user_id', 'role', 'workplace', 'type_of_work', 'start', 'end')
                    ->latest('start');
            },
        ])->findOrFail(Auth::id());

        $incomplete = StaffProfileSetting::getIncompleteItems($user);
        $requiredSections = StaffProfileSetting::getRequiredSections();
        $nationalities = CacheHelper::getNationalities();
        $earningBands = $this->earningBands($user->earning_band);
        $qualifications = Qualification::select('id', 'qualification', 'qualification_code')->get();

        return view('profile.complete-profile', [
            'user' => $user,
            'incomplete' => $incomplete,
            'requiredSections' => $requiredSections,
            'sectionDefinitions' => StaffProfileSetting::SECTIONS,
            'nationalities' => $nationalities,
            'earningBands' => $earningBands,
            'qualifications' => $qualifications,
        ]);
    }

    public function saveCompleteProfile(Request $request){
        $user = User::findOrFail(Auth::id());
        $requiredSections = StaffProfileSetting::getRequiredSections();

        $rules = [];
        if (in_array('basic_info', $requiredSections)) {
            $rules['firstname'] = 'sometimes|required|string|max:191';
            $rules['lastname'] = 'sometimes|required|string|max:191';
            $rules['date_of_birth'] = 'sometimes|required|date_format:d/m/Y';
            $rules['id_number'] = 'sometimes|required|string|max:20';
            $rules['email'] = ['sometimes', 'required', 'email', 'max:191', Rule::unique('users')->ignore($user->id)];
            $rules['nationality'] = 'sometimes|required|string|max:191';
        }
        if (in_array('employment_details', $requiredSections)) {
            $rules['personal_payroll_number'] = 'sometimes|required|string|max:255';
            $rules['dpsm_personal_file_number'] = 'sometimes|required|string|max:255';
            $rules['date_of_appointment'] = 'sometimes|required|date';
            $rules['earning_band'] = 'sometimes|required|string|max:255';
        }

        $validated = $request->validate($rules);

        // Convert dd/mm/yyyy to Y-m-d for storage
        if (isset($validated['date_of_birth'])) {
            $validated['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['date_of_birth'])->format('Y-m-d');
        }

        try {
            DB::beginTransaction();

            $user->fill($validated);
            $user->last_updated_by = auth()->user()->full_name;
            $user->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
            }

            return redirect()->route('profile.complete')->with('message', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving complete profile: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while updating your profile. Please try again.'], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while updating your profile. Please try again.');
        }
    }

    public function checkProfileCompleteness(): JsonResponse {
        $user = User::findOrFail(Auth::id());
        $incomplete = StaffProfileSetting::getIncompleteItems($user);
        $isComplete = empty($incomplete['missing_fields']) && empty($incomplete['missing_sections']);

        if ($isComplete) {
            session()->forget('profile_completion_required');
        }

        return response()->json([
            'complete' => $isComplete,
            'missing_fields' => $incomplete['missing_fields'] ?? [],
            'missing_sections' => $incomplete['missing_sections'] ?? [],
        ]);
    }

    public function storeCompleteProfileQualification(Request $request){
        $validated = $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
            'level' => 'required|string|max:191',
            'college' => 'required|string|max:191',
            'start_date' => 'required|date',
            'completion_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $qual = QualificationUser::create([
                'user_id' => Auth::id(),
                'qualification_id' => $validated['qualification_id'],
                'level' => $validated['level'],
                'college' => $validated['college'],
                'start_date' => $validated['start_date'],
                'completion_date' => $validated['completion_date'],
            ]);

            $qualification = Qualification::find($validated['qualification_id']);

            return response()->json([
                'success' => true,
                'message' => 'Qualification added successfully.',
                'qualification' => [
                    'id' => $qual->id,
                    'name' => $qualification->qualification ?? '',
                    'code' => $qualification->qualification_code ?? '',
                    'level' => $validated['level'],
                    'college' => $validated['college'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error storing complete profile qualification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the qualification.',
            ], 500);
        }
    }

    public function storeCompleteProfileWorkHistory(Request $request){
        $validated = $request->validate([
            'workplace' => 'required|string|max:191',
            'type_of_work' => 'required|string|max:191',
            'role' => 'required|string|max:191',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        try {
            $workHistory = WorkHistory::create([
                'user_id' => Auth::id(),
                'workplace' => $validated['workplace'],
                'type_of_work' => $validated['type_of_work'],
                'role' => $validated['role'],
                'start' => $validated['start'],
                'end' => $validated['end'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work history added successfully.',
                'work_history' => [
                    'id' => $workHistory->id,
                    'workplace' => $validated['workplace'],
                    'role' => $validated['role'],
                    'type_of_work' => $validated['type_of_work'],
                    'start' => $validated['start'],
                    'end' => $validated['end'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error storing complete profile work history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding work history.',
            ], 500);
        }
    }

    public function roleAllocations(){
        $user = User::findOrFail(auth()->user()->id);
        $roles = Role::all();
        
        $excludeExactEmail = ['obscure852@gmail.com'];
        $excludeDomains = ['heritagepro.co'];
        $excludePartialDomains = ['imagelife.co'];
        
        $baseQuery = User::where('status', 'Current')
            ->where(function($query) use ($excludeExactEmail, $excludeDomains, $excludePartialDomains) {
                if (!empty($excludeExactEmail)) {
                    $query->whereNotIn('email', $excludeExactEmail);
                }
                
                foreach ($excludeDomains as $domain) {
                    $query->where('email', 'not like', '%@' . $domain);
                }
                
                foreach ($excludePartialDomains as $partialDomain) {
                    $query->where('email', 'not like', '%' . $partialDomain . '%');
                }
            });
        
        $teachingStaff = (clone $baseQuery)
            ->where('area_of_work', 'Teaching')
            ->with('roles')
            ->get();

        $classTeachers = (clone $baseQuery)
            ->whereHas('klass', function($query) {
                $query->where('active', 1);
            })
            ->with(['roles', 'klass'])
            ->get();
        
        $allStaff = (clone $baseQuery)
            ->with('roles')
            ->get();
                    
        return view('staff.roles-allocations', [
            'roles' => $roles, 
            'teachingStaff' => $teachingStaff, 
            'classTeachers' => $classTeachers, 
            'allStaff' => $allStaff, 
            'user' => $user
        ]);
    }
    
    public function processBulkRoleAllocation(Request $request){
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'selected_users' => 'required|array',
            'selected_users.*' => 'exists:users,id',
        ]);
        
        $roleId = $request->role_id;
        $selectedUsers = $request->selected_users;
        
        $role = Role::findOrFail($roleId);        
        DB::beginTransaction();
        
        try {
            $affectedCount = 0;
            $alreadyAssignedCount = 0;
            
            foreach ($selectedUsers as $userId) {
                $user = User::findOrFail($userId);
                
                if (!$user->roles()->where('roles.id', $roleId)->exists()) {
                    $user->roles()->attach($roleId, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $affectedCount++;
                } else {
                    $alreadyAssignedCount++;
                }
            }
            
            DB::commit();
            if ($affectedCount > 0 && $alreadyAssignedCount > 0) {
                return redirect()->back()->with('message', "Role '{$role->name}' has been allocated to {$affectedCount} staff members successfully. ({$alreadyAssignedCount} already had this role)");
            } elseif ($affectedCount > 0) {
                return redirect()->back()->with('message', "Role '{$role->name}' has been allocated to {$affectedCount} staff members successfully.");
            } else {
                return redirect()->back()->with('info', "All selected staff members already have the '{$role->name}' role assigned.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function allocateTeachersRoles(){
        $teacherRole = Role::where('name', 'Teacher')->first();
        
        if (!$teacherRole) {
            return redirect()->back()->with('error', 'Teacher role not found.');
        }
        
        $teachingStaff = User::where('area_of_work', 'Teaching')
                            ->where('status', 'Current')
                            ->get();
        
        if ($teachingStaff->isEmpty()) {
            return redirect()->back()->with('info', 'No teaching staff found in the system.');
        }
        
        $eligibleStaff = $teachingStaff->filter(function($staff) use ($teacherRole) {
            return !$staff->roles()->where('roles.id', $teacherRole->id)->exists();
        });
        
        if ($eligibleStaff->isEmpty()) {
            return redirect()->back()->with('info', 'All teaching staff already have the Teacher role assigned.');
        }
        
        $affectedCount = 0;
        DB::beginTransaction();
        
        try {
            foreach ($eligibleStaff as $staff) {
                $staff->roles()->attach($teacherRole->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $affectedCount++;
            }
            
            DB::commit();
            return redirect()->back()->with('message', "Teacher role has been allocated to {$affectedCount} teaching staff members successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function allocateClassTeachersRoles(){
        $teacherRole = Role::where('name', 'Teacher')->first();
        
        if (!$teacherRole) {
            return redirect()->back()->with('error', 'Teacher role not found.');
        }
        
        $classTeachers = User::whereHas('klass', function($query) {
                                $query->where('active', 1);
                            })
                            ->where('area_of_work', 'Teaching')
                            ->where('status', 'Current')
                            ->get();
        
        if ($classTeachers->isEmpty()) {
            return redirect()->back()->with('info', 'No class teachers found in the system.');
        }
        
        $eligibleTeachers = $classTeachers->filter(function($teacher) use ($teacherRole) {
            return !$teacher->roles()->where('roles.id', $teacherRole->id)->exists();
        });
        
        if ($eligibleTeachers->isEmpty()) {
            return redirect()->back()->with('info', 'All class teachers already have the Teacher role assigned.');
        }
        
        $affectedCount = 0;
        DB::beginTransaction();
        
        try {
            foreach ($eligibleTeachers as $teacher) {
                $teacher->roles()->attach($teacherRole->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $affectedCount++;
            }
            
            DB::commit();
            
            return redirect()->back()->with('message', "Teacher role has been allocated to {$affectedCount} class teachers successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function showDepartment(){
        $users = CacheHelper::getUsers();
        return view('settings.add-new-department', ['users' => $users]);
    }

    public function addDepartment(Request $request){
        try {
            $messages = [
                'name.required' => 'The department name is required.',
                'name.string' => 'The department name must be a valid string.',
                'name.max' => 'The department name must not exceed 255 characters.',
                'department_head.required' => 'The department head ID is required.',
                'department_head.integer' => 'The department head ID must be a valid integer.',
                'department_head.exists' => 'The selected department head does not exist in the database.',
                'department_assistant.integer' => 'The department assistant ID must be a valid integer.',
                'department_assistant.exists' => 'The selected department assistant does not exist in the database.',
            ];

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'department_head' => 'required|integer|exists:users,id',
                'department_assistant' => 'nullable|integer|exists:users,id',
            ], $messages);

            Department::create($validatedData);
            CacheHelper::forgetDepartments();
            return redirect()->back()->with('message', 'Department added successfully!');

        } catch (ValidationException $e) {
            Log::error('Validation error while adding department', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while adding department', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Could not add the department. Please try again later.');
        }
    }


    public function editDepartment($departmentId){
        $users = CacheHelper::getUsers();
        $department = Department::find($departmentId);
        return view('settings.edit-department', ['users' => $users, 'department' => $department]);
    }


    public function updateDepartment(Request $request, $id){
        try {
            $messages = [
                'name.required' => 'The department name is required.',
                'name.string' => 'The department name must be a valid string.',
                'name.max' => 'The department name must not exceed 255 characters.',
                'department_head.required' => 'The department head ID is required.',
                'department_head.integer' => 'The department head ID must be a valid integer.',
                'department_head.exists' => 'The selected department head does not exist in the database.',
                'assistant.integer' => 'The assistant ID must be a valid integer.',
                'assistant.exists' => 'The selected assistant does not exist in the database.',
            ];
    
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'department_head' => 'required|integer|exists:users,id',
                'assistant' => 'nullable|integer|exists:users,id',
            ], $messages);
    
            $department = Department::findOrFail($id);
            $department->update($validatedData);
    
            CacheHelper::forgetDepartments();
            return redirect()->back()->with('message', 'Department updated successfully!');
    
        } catch (ValidationException $e) {
            Log::error('Validation error while updating department', [
                'errors' => $e->errors(),
                'department_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
    
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while updating department', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'department_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Could not update the department. Please try again later.');
        }
    }
    
    public function deleteDepartment($departmentId){
        try {
            $department = Department::findOrFail($departmentId);
            if ($department->gradeSubjects()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete department with associated subjects. Please reassign subjects to another department first.');
            }
            $department->delete();
            CacheHelper::forgetDepartments();
            return redirect()->back()->with('message', 'Department deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete department', [
                'department_id' => $departmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while deleting the department.');
        }
    }

    // Qualification Management Methods
    public function showQualification(){
        return view('settings.add-new-qualification');
    }

    public function addQualification(Request $request){
        try {
            $messages = [
                'qualification.required' => 'The qualification name is required.',
                'qualification.string' => 'The qualification name must be a valid string.',
                'qualification.max' => 'The qualification name must not exceed 255 characters.',
                'qualification_code.required' => 'The qualification code is required.',
                'qualification_code.string' => 'The qualification code must be a valid string.',
                'qualification_code.max' => 'The qualification code must not exceed 50 characters.',
                'qualification_code.unique' => 'The qualification code already exists.',
            ];

            $validatedData = $request->validate([
                'qualification' => 'required|string|max:255',
                'qualification_code' => 'required|string|max:50|unique:qualifications,qualification_code',
            ], $messages);

            Qualification::create($validatedData);
            return redirect()->route('staff.staff-settings')->with('message', 'Qualification added successfully!');

        } catch (ValidationException $e) {
            Log::error('Validation error while adding qualification', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while adding qualification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Could not add the qualification. Please try again later.');
        }
    }

    public function editQualification($qualificationId){
        $qualification = Qualification::find($qualificationId);
        return view('settings.edit-qualification', ['qualification' => $qualification]);
    }

    public function updateQualification(Request $request, $id){
        try {
            $messages = [
                'qualification.required' => 'The qualification name is required.',
                'qualification.string' => 'The qualification name must be a valid string.',
                'qualification.max' => 'The qualification name must not exceed 255 characters.',
                'qualification_code.required' => 'The qualification code is required.',
                'qualification_code.string' => 'The qualification code must be a valid string.',
                'qualification_code.max' => 'The qualification code must not exceed 50 characters.',
                'qualification_code.unique' => 'The qualification code already exists.',
            ];
    
            $validatedData = $request->validate([
                'qualification' => 'required|string|max:255',
                'qualification_code' => 'required|string|max:50|unique:qualifications,qualification_code,' . $id,
            ], $messages);
    
            $qualification = Qualification::findOrFail($id);
            $qualification->update($validatedData);
    
            return redirect()->route('staff.staff-settings')->with('message', 'Qualification updated successfully!');
    
        } catch (ValidationException $e) {
            Log::error('Validation error while updating qualification', [
                'errors' => $e->errors(),
                'qualification_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
    
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while updating qualification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'qualification_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Could not update the qualification. Please try again later.');
        }
    }
    
    public function deleteQualification($qualificationId){
        try {
            $qualification = Qualification::findOrFail($qualificationId);
            if ($qualification->users()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete qualification with associated users. Please reassign users to another qualification first.');
            }
            $qualification->forceDelete();
            return redirect()->back()->with('message', 'Qualification deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete qualification', [
                'qualification_id' => $qualificationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while deleting the qualification.');
        }
    }

    public function  sendDirectSms(Request $request, $recipientType, $id){
        if (!app(CommunicationChannelService::class)->smsEnabled()) {
            return response()->json(['success' => false, 'message' => 'SMS is disabled in Communications Setup.'], 403);
        }

        $selectedApi = SMSApiSetting::where('key', 'sms_api')->first()?->value ?? 'mascom';
        $message = $request->input('message');

        if ($recipientType === 'user') {
            $recipient = User::find($id);
        } elseif ($recipientType === 'sponsor') {
            $recipient = Sponsor::find($id);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid recipient type.']);
        }

        if (!$recipient) {
            return response()->json(['success' => false, 'message' => 'Recipient not found.']);
        }

        try {
            if ($recipientType === 'user') {
                NotificationController::sendMessage($message, $recipient->phone, $recipient->id, 'user', 'direct', 1, $selectedApi);
            } elseif ($recipientType === 'sponsor') {
                Log::info('Sponsor');
                NotificationController::sendMessage($message, $recipient->phone, $recipient->id, 'sponsor', 'direct', 1, $selectedApi);
            }
            return response()->json(['success' => true, 'message' => 'SMS sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function sendDirectMessage(Request $request, $recipientType, $id)
    {
        $channel = strtolower((string) $request->input('channel'));

        if (!in_array($channel, [CommunicationChannelService::CHANNEL_SMS, CommunicationChannelService::CHANNEL_WHATSAPP], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid communication channel selected.'], 422);
        }

        if ($recipientType === 'user') {
            $recipient = User::find($id);
        } elseif ($recipientType === 'sponsor') {
            $recipient = Sponsor::find($id);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid recipient type.'], 422);
        }

        if (!$recipient) {
            return response()->json(['success' => false, 'message' => 'Recipient not found.'], 404);
        }

        if ($channel === CommunicationChannelService::CHANNEL_SMS) {
            return $this->sendDirectSms($request, $recipientType, $id);
        }

        if ($recipientType !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp direct messaging is currently supported for staff only.',
            ], 422);
        }

        $validated = $request->validate([
            'template_id' => ['required', 'integer', 'exists:whatsapp_templates,id'],
            'template_variables' => ['required', 'array'],
            'record_consent' => ['nullable', 'boolean'],
            'consent_source' => ['nullable', 'string', 'max:255'],
            'consent_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $template = WhatsappTemplate::approved()->findOrFail($validated['template_id']);

            app(WhatsAppMessagingService::class)->sendDirectMessage(
                $recipient,
                auth()->user(),
                $template,
                $validated['template_variables'],
                [
                    'record_consent' => (bool) ($validated['record_consent'] ?? false),
                    'consent_source' => $validated['consent_source'] ?? 'staff_admin',
                    'consent_notes' => $validated['consent_notes'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message queued successfully.',
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function updateCommunicationConsent(Request $request, User $user)
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:whatsapp'],
            'status' => ['required', 'string', 'in:opted_in,opted_out,revoked'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        app(RecipientChannelConsentService::class)->recordStatus(
            $user,
            $validated['channel'],
            $validated['status'],
            auth()->id(),
            $validated['source'] ?? 'staff_admin',
            $validated['notes'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Communication consent updated successfully.',
            ]);
        }

        return redirect()->back()->with('message', 'Communication consent updated successfully.');
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

            if (UserFilter::where('name', $request->name)->exists()) {
                return back()->withInput()->withErrors(['name' => 'A filter with this name already exists.']);
            }

            UserFilter::create([
                'name' => trim($request->name)
            ]);

            CacheHelper::forgetUserFilterList();
            return back()->with('message', 'Filter added successfully!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating filter: ' . $e->getMessage());
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function updateFilter(Request $request, $id)
    {
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

            $userFilter = UserFilter::findOrFail($id);
            $exists = UserFilter::where('name', $request->name)->where('id', '!=', $id)->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'errors'  => ['name' => ['A filter with this name already exists.']]
                ], 422);
            }

            $userFilter->name = trim($request->name);
            $userFilter->save();

            CacheHelper::forgetUserFilterList();
            return response()->json([
                'success' => true,
                'message' => 'Filter updated successfully',
                'data'    => $userFilter
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating filter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }


    public function destroyFilter($id)
    {
        try {
            if (!is_numeric($id)) {
                return back()->with('error', 'Invalid filter ID provided');
            }

            $filter = UserFilter::findOrFail($id);
            $filter->delete();

            CacheHelper::forgetUserFilterList();
            return back()->with('message', 'Filter deleted successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Filter not found');
        } catch (\Exception $e) {
            Log::error('Error deleting filter: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred while deleting the filter');
        }
    }

    public function storeEarningBand(Request $request)
    {
        try {
            if (!Schema::hasTable('earning_bands')) {
                return back()->with('error', 'The earning band table is not available yet. Please run the latest migrations first.');
            }

            $request->merge([
                'band_name' => strtoupper((string) $this->normalizeNullableString($request->input('band_name'))),
            ]);

            $validatedData = $request->validate([
                'band_name' => ['required', 'string', 'max:50', Rule::unique('earning_bands', 'name')],
                'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            ]);

            EarningBand::create([
                'name' => $validatedData['band_name'],
                'sort_order' => $validatedData['sort_order'] ?? ($this->nextEarningBandSortOrder()),
            ]);

            return back()->with('message', 'Earning band added successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            Log::error('Error creating earning band: ' . $e->getMessage());
            return back()->withInput()->with('error', 'An unexpected error occurred while adding the earning band.');
        }
    }

    public function updateEarningBand(Request $request, $id)
    {
        try {
            if (!Schema::hasTable('earning_bands')) {
                return back()->with('error', 'The earning band table is not available yet. Please run the latest migrations first.');
            }

            $earningBand = EarningBand::findOrFail($id);
            $request->merge([
                'band_name' => strtoupper((string) $this->normalizeNullableString($request->input('band_name'))),
            ]);

            $validatedData = $request->validate([
                'band_name' => ['required', 'string', 'max:50', Rule::unique('earning_bands', 'name')->ignore($earningBand->id)],
                'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            ]);

            $newName = $validatedData['band_name'];
            $oldName = $earningBand->name;

            DB::transaction(function () use ($earningBand, $validatedData, $newName, $oldName): void {
                $earningBand->update([
                    'name' => $newName,
                    'sort_order' => $validatedData['sort_order'] ?? $earningBand->sort_order,
                ]);

                if ($oldName !== $newName && Schema::hasTable('users') && Schema::hasColumn('users', 'earning_band')) {
                    DB::table('users')
                        ->where('earning_band', $oldName)
                        ->update([
                            'earning_band' => $newName,
                            'updated_at' => now(),
                        ]);
                }
            });

            return back()->with('message', 'Earning band updated successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            Log::error('Error updating earning band: ' . $e->getMessage());
            return back()->withInput()->with('error', 'An unexpected error occurred while updating the earning band.');
        }
    }

    public function destroyEarningBand($id)
    {
        try {
            if (!Schema::hasTable('earning_bands')) {
                return back()->with('error', 'The earning band table is not available yet. Please run the latest migrations first.');
            }

            $earningBand = EarningBand::findOrFail($id);

            if (Schema::hasTable('users') && Schema::hasColumn('users', 'earning_band')) {
                $assignedCount = User::query()->where('earning_band', $earningBand->name)->count();

                if ($assignedCount > 0) {
                    return back()->with('error', 'This earning band is already assigned to staff records and cannot be deleted.');
                }
            }

            $earningBand->delete();

            return back()->with('message', 'Earning band deleted successfully.');
        } catch (Throwable $e) {
            Log::error('Error deleting earning band: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred while deleting the earning band.');
        }
    }

    private function earningBands(?string $currentValue = null)
    {
        if (!Schema::hasTable('earning_bands')) {
            return collect();
        }

        $bandQuery = EarningBand::query();

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'earning_band')) {
            $bandQuery->withCount('users');
        }

        $bands = $bandQuery->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($currentValue !== null && $currentValue !== '' && !$bands->contains('name', $currentValue)) {
            $bands->push(new EarningBand([
                'name' => $currentValue,
                'sort_order' => ($bands->max('sort_order') ?? 0) + 1,
            ]));
        }

        return $bands->sortBy(static function ($band): string {
            return sprintf('%05d-%s', (int) ($band->sort_order ?? 0), $band->name ?? '');
        })->values();
    }

    private function earningBandValidationRules(?string $currentValue = null): array
    {
        $rules = ['nullable', 'string', 'max:255'];
        $allowedBands = $this->earningBands($currentValue)->pluck('name')->filter()->unique()->values()->all();

        if ($allowedBands !== []) {
            $rules[] = Rule::in($allowedBands);
        }

        return $rules;
    }

    private function nextEarningBandSortOrder(): int
    {
        if (!Schema::hasTable('earning_bands')) {
            return 1;
        }

        return ((int) EarningBand::query()->max('sort_order')) + 1;
    }

    public function academicData(Request $request, $id) {
        $user = User::findOrFail($id);
        $currentTerm = TermHelper::getCurrentTerm();
        $termId = $request->query('term_id', $currentTerm->id);
        $term = \App\Models\Term::findOrFail($termId);
        $selectableTerms = TermHelper::getSelectableTerms($currentTerm);

        $terms = $selectableTerms->map(function ($t) {
            return ['id' => $t->id, 'label' => "Term {$t->term}, {$t->year}"];
        })->values();

        $termLabel = "Term {$term->term}, {$term->year}";

        $gradeScales = [
            'Junior' => [
                'grades' => ['A','B','C','D','E','U'],
                'percentages' => [
                    'AB%' => ['A','B'],
                    'ABC%' => ['A','B','C'],
                    'ABCD%' => ['A','B','C','D'],
                    'DEU%' => ['D','E','U'],
                ],
            ],
            'Senior' => [
                'grades' => ['A*','A','B','C','D','E','F','G','U'],
                'percentages' => [
                    'AB%' => ['A*','A','B'],
                    'ABC%' => ['A*','A','B','C'],
                    'ABCD%' => ['A*','A','B','C','D'],
                    'DEFGU%' => ['D','E','F','G','U'],
                ],
            ],
            'Primary' => [
                'grades' => ['A','B','C','D','E'],
                'percentages' => [
                    'AB%' => ['A','B'],
                    'ABC%' => ['A','B','C'],
                    'DE%' => ['D','E'],
                ],
            ],
        ];

        // Collect all assignments (core + optional)
        $assignments = collect();

        // Core subject assignments
        $klassSubjects = KlassSubject::where('user_id', $user->id)
            ->where('term_id', $termId)
            ->with(['klass', 'gradeSubject.subject', 'grade'])
            ->get();

        foreach ($klassSubjects as $ks) {
            if (!$ks->gradeSubject || !$ks->klass || !$ks->grade) continue;
            $assignments->push([
                'type' => 'core',
                'class_name' => $ks->klass->name,
                'subject_name' => $ks->gradeSubject->subject->name ?? 'Unknown',
                'grade_subject_id' => $ks->grade_subject_id,
                'klass_id' => $ks->klass_id,
                'grade_id' => $ks->grade_id,
                'level' => $ks->grade->level,
                'is_double' => $ks->gradeSubject->subject->is_double ?? false,
                'klass' => $ks->klass,
            ]);
        }

        // Optional subject assignments
        $optionalSubjects = OptionalSubject::where('user_id', $user->id)
            ->where('term_id', $termId)
            ->with(['gradeSubject.subject', 'grade', 'students'])
            ->get();

        foreach ($optionalSubjects as $os) {
            if (!$os->gradeSubject || !$os->grade) continue;
            // Get distinct classes from student enrollments
            $klassIds = $os->students->pluck('pivot.klass_id')->unique();
            $klasses = \App\Models\Klass::whereIn('id', $klassIds)->get();
            $klassName = $klasses->pluck('name')->sort()->implode('/');
            if (empty($klassName)) $klassName = $os->name ?? 'Optional';

            $assignments->push([
                'type' => 'optional',
                'class_name' => $klassName,
                'subject_name' => $os->gradeSubject->subject->name ?? ($os->name ?? 'Unknown'),
                'grade_subject_id' => $os->grade_subject_id,
                'klass_id' => null,
                'grade_id' => $os->grade_id,
                'level' => $os->grade->level,
                'is_double' => $os->gradeSubject->subject->is_double ?? false,
                'optional_subject' => $os,
            ]);
        }

        if ($assignments->isEmpty()) {
            return response()->json([
                'termLabel' => $termLabel,
                'terms' => $terms,
                'subjects' => [],
                'totals' => [],
                'levels' => [],
            ]);
        }

        // Determine the dominant level
        $levelCounts = $assignments->groupBy('level')->map->count();
        $dominantLevel = $levelCounts->sortDesc()->keys()->first() ?? 'Junior';
        $scale = $gradeScales[$dominantLevel] ?? $gradeScales['Junior'];

        // --- BATCH 1: Load all Exam tests in one query, keyed by grade_subject_id ---
        $allGradeSubjectIds = $assignments->pluck('grade_subject_id')->unique()->values();
        $examTests = Test::whereIn('grade_subject_id', $allGradeSubjectIds)
            ->where('term_id', $termId)
            ->where('type', 'Exam')
            ->get()
            ->keyBy('grade_subject_id');

        // --- BATCH 2: Load all enrolled students in bulk ---
        // Core: single query on klass_student pivot
        $coreKlassIds = $assignments->where('type', 'core')->pluck('klass_id')->unique()->values();
        $coreStudentRows = collect();
        if ($coreKlassIds->isNotEmpty()) {
            $coreStudentRows = DB::table('klass_student')
                ->join('students', 'students.id', '=', 'klass_student.student_id')
                ->where('klass_student.term_id', $termId)
                ->where('klass_student.year', $term->year)
                ->whereIn('klass_student.klass_id', $coreKlassIds)
                ->whereNull('students.deleted_at')
                ->select('klass_student.klass_id', 'students.id as student_id', 'students.gender')
                ->get()
                ->groupBy('klass_id');
        }

        // Optional: single query on student_optional_subjects pivot
        $optionalIds = $assignments->where('type', 'optional')
            ->map(fn($a) => $a['optional_subject']->id ?? null)
            ->filter()->unique()->values();
        $optStudentRows = collect();
        if ($optionalIds->isNotEmpty()) {
            $optStudentRows = DB::table('student_optional_subjects')
                ->join('students', 'students.id', '=', 'student_optional_subjects.student_id')
                ->where('student_optional_subjects.term_id', $termId)
                ->whereIn('student_optional_subjects.optional_subject_id', $optionalIds)
                ->whereNull('students.deleted_at')
                ->select('student_optional_subjects.optional_subject_id', 'students.id as student_id', 'students.gender')
                ->get()
                ->groupBy('optional_subject_id');
        }

        // --- BATCH 3: Load ALL StudentTest grades in one query ---
        $examTestIds = $examTests->pluck('id')->values();
        $allStudentGrades = collect();
        if ($examTestIds->isNotEmpty()) {
            $allStudentGrades = DB::table('student_tests')
                ->whereIn('test_id', $examTestIds)
                ->whereNull('deleted_at')
                ->select('test_id', 'student_id', 'grade')
                ->get()
                ->groupBy('test_id')
                ->map(fn($rows) => $rows->keyBy('student_id'));
        }

        // --- Process assignments using pre-loaded data (no more N+1) ---
        $subjects = [];
        $totals = [];

        foreach ($assignments as $assignment) {
            $level = $assignment['level'];
            $assignmentScale = $gradeScales[$level] ?? $gradeScales['Junior'];
            $gradeList = $assignmentScale['grades'];
            $isDouble = $assignment['is_double'];

            $examTest = $examTests->get($assignment['grade_subject_id']);

            // Get students from pre-loaded batch
            if ($assignment['type'] === 'core') {
                $students = $coreStudentRows->get($assignment['klass_id'], collect());
            } else {
                $osId = $assignment['optional_subject']->id ?? null;
                $students = $optStudentRows->get($osId, collect());
            }

            // Get grade lookup for this test from pre-loaded batch
            $testGrades = ($examTest && $allStudentGrades->has($examTest->id))
                ? $allStudentGrades->get($examTest->id)
                : collect();

            // Initialize grade counts
            $gradeCounts = [];
            foreach ($gradeList as $g) {
                $gradeCounts[$g] = ['M' => 0, 'F' => 0];
            }
            $gradeCounts['NS'] = ['M' => 0, 'F' => 0];

            $totalMale = 0;
            $totalFemale = 0;
            $enrolledMale = 0;
            $enrolledFemale = 0;

            foreach ($students as $student) {
                $isMale = in_array(strtolower($student->gender ?? ''), ['male', 'm']);
                $gender = $isMale ? 'M' : 'F';

                if ($isMale) $enrolledMale++;
                else $enrolledFemale++;

                if (!$examTest) {
                    $gradeCounts['NS'][$gender]++;
                    continue;
                }

                $stRecord = $testGrades->get($student->student_id);
                if (!$stRecord || empty($stRecord->grade)) {
                    $gradeCounts['NS'][$gender]++;
                    continue;
                }

                $rawGrade = $stRecord->grade;

                if ($isDouble && $level === 'Senior' && is_string($rawGrade) && strlen($rawGrade) === 2) {
                    foreach (str_split($rawGrade) as $char) {
                        $mapped = in_array($char, $gradeList, true) ? $char : 'U';
                        $gradeCounts[$mapped][$gender]++;
                    }
                    if ($isMale) $totalMale += 2;
                    else $totalFemale += 2;
                } else {
                    $mapped = in_array($rawGrade, $gradeList, true) ? $rawGrade : null;
                    if ($mapped) {
                        $gradeCounts[$mapped][$gender]++;
                        if ($isMale) $totalMale++;
                        else $totalFemale++;
                    } else {
                        $gradeCounts['NS'][$gender]++;
                    }
                }
            }

            // Calculate percentages
            $percentages = [];
            foreach ($assignmentScale['percentages'] as $label => $gradeGroup) {
                $mSum = 0;
                $fSum = 0;
                foreach ($gradeGroup as $g) {
                    $mSum += $gradeCounts[$g]['M'] ?? 0;
                    $fSum += $gradeCounts[$g]['F'] ?? 0;
                }
                $percentages[$label] = [
                    'M' => $totalMale > 0 ? round($mSum / $totalMale * 100, 2) : 0,
                    'F' => $totalFemale > 0 ? round($fSum / $totalFemale * 100, 2) : 0,
                ];
            }

            $subjectName = $assignment['subject_name'];
            $row = [
                'class_name' => $assignment['class_name'],
                'subject_name' => $subjectName,
                'grades' => $gradeCounts,
                'totalMale' => $totalMale,
                'totalFemale' => $totalFemale,
                'totalEnrolled' => ['M' => $enrolledMale, 'F' => $enrolledFemale],
            ];
            foreach ($percentages as $label => $vals) {
                $row[$label] = $vals;
            }

            $subjects[$subjectName][] = $row;

            // Accumulate totals
            if (!isset($totals[$subjectName])) {
                $totals[$subjectName] = [
                    'grades' => [],
                    'totalMale' => 0,
                    'totalFemale' => 0,
                    'totalEnrolled' => ['M' => 0, 'F' => 0],
                ];
                foreach ($gradeList as $g) {
                    $totals[$subjectName]['grades'][$g] = ['M' => 0, 'F' => 0];
                }
                $totals[$subjectName]['grades']['NS'] = ['M' => 0, 'F' => 0];
            }

            foreach ($gradeCounts as $g => $counts) {
                $totals[$subjectName]['grades'][$g]['M'] = ($totals[$subjectName]['grades'][$g]['M'] ?? 0) + $counts['M'];
                $totals[$subjectName]['grades'][$g]['F'] = ($totals[$subjectName]['grades'][$g]['F'] ?? 0) + $counts['F'];
            }
            $totals[$subjectName]['totalMale'] += $totalMale;
            $totals[$subjectName]['totalFemale'] += $totalFemale;
            $totals[$subjectName]['totalEnrolled']['M'] += $enrolledMale;
            $totals[$subjectName]['totalEnrolled']['F'] += $enrolledFemale;
        }

        // Compute total percentages
        foreach ($totals as $subjectName => &$total) {
            $level = $assignments->firstWhere('subject_name', $subjectName)['level'] ?? $dominantLevel;
            $assignmentScale = $gradeScales[$level] ?? $gradeScales['Junior'];

            foreach ($assignmentScale['percentages'] as $label => $gradeGroup) {
                $mSum = 0;
                $fSum = 0;
                foreach ($gradeGroup as $g) {
                    $mSum += $total['grades'][$g]['M'] ?? 0;
                    $fSum += $total['grades'][$g]['F'] ?? 0;
                }
                $total[$label] = [
                    'M' => $total['totalMale'] > 0 ? round($mSum / $total['totalMale'] * 100, 2) : 0,
                    'F' => $total['totalFemale'] > 0 ? round($fSum / $total['totalFemale'] * 100, 2) : 0,
                ];
            }
        }
        unset($total);

        // Build levels info per subject
        $levelsMap = [];
        foreach ($subjects as $subjectName => $rows) {
            $level = $assignments->firstWhere('subject_name', $subjectName)['level'] ?? $dominantLevel;
            $levelsMap[$subjectName] = $level;
        }

        return response()->json([
            'termLabel' => $termLabel,
            'terms' => $terms,
            'gradeColumns' => $scale['grades'],
            'percentageColumns' => array_keys($scale['percentages']),
            'subjects' => $subjects,
            'totals' => $totals,
            'levels' => $levelsMap,
            'gradeScales' => $gradeScales,
        ]);
    }

    private function processAvatarUpload(UploadedFile $file): string {
        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new \RuntimeException('Could not read uploaded file.');
        }

        $image = imagecreatefromstring($contents);
        if ($image === false) {
            throw new \RuntimeException('Uploaded file is not a valid image.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $size = min($width, $height);
        $cropped = imagecrop($image, [
            'x' => (int)(($width - $size) / 2),
            'y' => (int)(($height - $size) / 2),
            'width' => $size,
            'height' => $size,
        ]);

        $resized = imagescale($cropped ?: $image, 300, 300);
        if ($resized === false) {
            imagedestroy($image);
            if ($cropped) {
                imagedestroy($cropped);
            }
            throw new \RuntimeException('Failed to resize image.');
        }

        $dir = storage_path('app/public/avatars');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'avatars/' . uniqid('avatar_') . '.jpg';
        $saved = imagejpeg($resized, storage_path('app/public/' . $filename), 90);

        imagedestroy($image);
        if ($cropped) {
            imagedestroy($cropped);
        }
        imagedestroy($resized);

        if (!$saved) {
            throw new \RuntimeException('Failed to save image to disk.');
        }

        return $filename;
    }

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeOptionalDate($value): ?string
    {
        $value = $this->normalizeNullableString($value);

        if ($value === null) {
            return null;
        }

        return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    }
}
