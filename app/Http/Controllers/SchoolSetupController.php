<?php

namespace App\Http\Controllers;

use App\Exceptions\RolloverException;
use App\Helpers\CacheHelper;
use App\Models\SchoolSetup;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;
use App\Imports\SponsorsImport;
use App\Imports\StudentsImport;
use App\Exports\ImportTemplateExport;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\User;
use App\Models\Term;
use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Helpers\TermHelper;
use App\Imports\AdmissionsImport;
use App\Models\AccountBalance;
use App\Models\Admission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use App\Models\Holiday;
use App\Models\Attendance;
use App\Models\KlassSubject;
use App\Models\Klass;
use App\Models\Qualification;
use App\Models\StudentBehaviour;
use App\Models\StudentMedicalInformation;
use App\Models\StudentTest;
use App\Models\SubjectComment;
use App\Models\StudentTerm;
use App\Models\WorkHistory;
use App\Models\Username;
use App\Models\Logging;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Grade;
use GuzzleHttp\Client;
use App\Models\BackupLog;
use App\Models\License;
use App\Models\RolloverHistory;
use App\Models\SMSApiSetting;
use App\Models\SmsTemplate;
use App\Models\TermRolloverHistory;
use App\Services\TermRolloverReverseService;
use App\Services\TermRolloverService;
use App\Services\YearRolloverReverseService;
use App\Services\YearRolloverService;
use App\Services\SettingsService;
use Exception;
use Illuminate\Support\Facades\Config;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SchoolSetupController extends Controller{

    protected static $client;
    protected static $apiKey;
    protected $termRolloverService;
    protected $termRolloverReverseService;

    protected $yearRolloverService;
    protected $yearRolloverReverseService;

    public function __construct(TermRolloverService $termRolloverService, YearRolloverService $yearRolloverService, YearRolloverReverseService $yearRolloverReverseService, TermRolloverReverseService $termRolloverReverseService){
        $this->middleware('auth');
        $this->termRolloverService = $termRolloverService;
        $this->termRolloverReverseService = $termRolloverReverseService;

        $this->yearRolloverService = $yearRolloverService;
        $this->yearRolloverReverseService = $yearRolloverReverseService;
    }

    protected static function initialize(){
        if (!self::$client) {
            self::$client = new Client();
            self::$apiKey = config('services.ipinfo.api_key');
        }
    }

    public function index(){
        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        $lastTermOfPreviousYear = Term::where('year', $previousYear)->orderBy('end_date', 'desc')->first();
        if ($lastTermOfPreviousYear && !$lastTermOfPreviousYear->closed) {
            $terms = Term::where('year', $previousYear)->get();
        } else {
            $terms = Term::where('year', $currentYear)->get();
        }

        $allTermsClosed = $terms->every(function ($term) {
            return $term->closed == 1;
        });

        if ($allTermsClosed) {
            $currentYear += 1;
            $terms = Term::where('year', $currentYear)->get();
        }

        $schoolSetup = SchoolSetup::first();
        $regional_offices = DB::table('regional_offices')->get();

        $openTerms = Term::where('closed', 0)->take(4)->get();
        $nextYearTerms = Term::where('year', $currentYear + 1)->get();

        $yearRolloverHistories = RolloverHistory::with(['fromTerm', 'toTerm', 'performer'])->orderBy('created_at', 'desc')->limit(7)->get();
        $yearRolloverLatestHistory = $yearRolloverHistories->first();

        $termRolloverHistories = TermRolloverHistory::with(['fromTerm', 'toTerm'])->orderBy('created_at', 'desc')->limit(7)->get();
        $termRolloverLatestHistory = $termRolloverHistories->first();
        $latestLicense = License::orderBy('created_at', 'desc')->first();

        $licenseData = $this->calculateLicenseData($latestLicense);

        $currentTerm = TermHelper::getCurrentTerm();
        $allTermsForToggle = Term::orderBy('year', 'desc')->orderBy('term', 'asc')->take(9)->get();
        $availableYears = Term::select('year')->distinct()->orderBy('year')->pluck('year');
        $nextTermStartDate = $this->getNextTermStartDate($currentTerm);
        
        $currentTermIndex = $allTermsForToggle->search(function($term) use ($currentTerm) {
            return $term->id === $currentTerm->id;
        });
       

        return view('settings.school-setup', [
            'schoolSetup' => $schoolSetup,
            'openTerms' => $openTerms,
            'terms' => $terms,
            'regional_offices' => $regional_offices,
            'latestLicense' => $latestLicense,
            'nextYearTerms' => $nextYearTerms,
            'histories' => $yearRolloverHistories,
            'latestHistory' => $yearRolloverLatestHistory,
            'termRolloverhistories' => $termRolloverHistories,
            'termRolloverLatestHistory' => $termRolloverLatestHistory,
            'currentTerm' => $currentTerm,
            'allTermsForToggle' => $allTermsForToggle,
            'currentTermIndex' => $currentTermIndex,
            'nextTermStartDate' => $nextTermStartDate,
            'licenseData' => $licenseData,
            'availableYears' => $availableYears
        ]);
    }

    private function calculateLicenseData($license){
        if (!$license) {
            return [
                'license' => null,
                'valid' => false,
                'in_grace_period' => false,
                'grace_ends' => null,
                'is_expiring_soon' => false,
                'days_remaining' => 0,
                'warning_threshold' => config('license.warning_days', 30)
            ];
        }

        $inGracePeriod = Cache::get('license_in_grace_period', false);
        $graceEnds = Cache::get('license_grace_ends');
        $today = Carbon::now();
        
        $warningDays = config('license.warning_days', 30);
        $isExpiringSoon = false;
        $daysRemaining = 0;
        
        if ($license && !$inGracePeriod) {
            $daysRemaining = $today->diffInDays($license->end_date);
            $isExpiringSoon = $daysRemaining <= $warningDays && $today->lessThan($license->end_date);
        }
        
        return [
            'license' => $license,
            'valid' => License::checkSystemHealth(),
            'in_grace_period' => $inGracePeriod,
            'grace_ends' => $graceEnds ? Carbon::parse($graceEnds) : null,
            'is_expiring_soon' => $isExpiringSoon,
            'days_remaining' => $daysRemaining,
            'warning_threshold' => $warningDays
        ];
    }

    public static function closeTerms(int $year){
        Term::where('year', $year)->whereBetween('id', [1, 3])->update(['closed' => 1]);
    }

    private function getNextTermStartDate($currentTerm){
        $nextTerm = null;
        
        if ($currentTerm->term < 3) {
            $nextTerm = Term::where('year', $currentTerm->year)
                            ->where('term', $currentTerm->term + 1)
                            ->first();
        } else {
            $nextTerm = Term::where('year', $currentTerm->year + 1)
                            ->where('term', 1)
                            ->first();
        }
        
        return $nextTerm ? $nextTerm->start_date : null;
    }

    public function settingsIndex(Request $request, SettingsService $settingsService){
        // Get Email settings from database instead of .env
        $settings = [
            'MAILER' => $settingsService->get('email.mailer', env('MAIL_MAILER', 'smtp')),
            'HOST' => $settingsService->get('email.host', env('MAIL_HOST', '')),
            'PORT' => $settingsService->get('email.port', env('MAIL_PORT', '465')),
            'USERNAME' => $settingsService->get('email.username', env('MAIL_USERNAME', '')),
            'PASSWORD' => $settingsService->get('email.password', env('MAIL_PASSWORD', '')),
            'ENCRYPTION' => $settingsService->get('email.encryption', env('MAIL_ENCRYPTION', 'ssl')),
            'FROM_ADDRESS' => $settingsService->get('email.from_address', env('MAIL_FROM_ADDRESS', '')),
            'FROM_NAME' => $settingsService->get('email.from_name', env('MAIL_FROM_NAME', config('app.name'))),
        ];

        // Get Link SMS credentials from database instead of .env
        $settingsLink = [
            'API_KEY' => $settingsService->get('api.link_api_key', ''),
            'SENDER_ID' => $settingsService->get('api.link_sender_id', '')
        ];

        $smsPackages = [
            'Basic' => '5,000 SMS Credits',
            'Standard' => '10,000 SMS Credits',
            'Premium' => '20,000 SMS Credits'
        ];

        $smsApi = SMSApiSetting::where('key', 'sms_api')->value('value');

        $accountBalance = AccountBalance::first();
        $currentPackage = $accountBalance ? $accountBalance->sms_credits_package : '';
        $currentAmount = $accountBalance ? $accountBalance->package_amount : '';

        // Get SMS package rates from database
        $smsRates = [
            'basic' => SMSApiSetting::where('key', 'sms_rate_basic')->value('value') ?? '0.35',
            'standard' => SMSApiSetting::where('key', 'sms_rate_standard')->value('value') ?? '0.30',
            'premium' => SMSApiSetting::where('key', 'sms_rate_premium')->value('value') ?? '0.25',
        ];

        // Get notification settings grouped by category
        $notificationSettings = $settingsService->allGrouped();

        $smsTemplateQuery = SmsTemplate::with('creator')
            ->orderBy('category')
            ->orderBy('name');

        if ($request->filled('sms_template_category')) {
            $smsTemplateQuery->where('category', $request->input('sms_template_category'));
        }

        if ($request->filled('sms_template_search')) {
            $search = $request->input('sms_template_search');
            $smsTemplateQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $smsTemplates = $smsTemplateQuery->paginate(12, ['*'], 'sms_templates_page');
        $smsTemplates->withPath(route('setup.communications-setup'));
        $smsTemplates->appends($request->query());
        $smsTemplates->fragment('sms-templates-settings');

        return view('settings.communications-setup', [
            'settings' => $settings,
            'settingsLink' => $settingsLink,
            'smsPackages' => $smsPackages,
            'smsApi' => $smsApi,
            'packageAmount' => $currentAmount,
            'currentPackage' => $currentPackage,
            'notificationSettings' => $notificationSettings,
            'smsRates' => $smsRates,
            'smsTemplates' => $smsTemplates,
            'smsTemplateCategories' => SmsTemplate::CATEGORIES,
            'smsTemplatePlaceholders' => SmsTemplate::AVAILABLE_PLACEHOLDERS,
            'smsTemplateActiveCount' => SmsTemplate::where('is_active', true)->count(),
        ]);
    }

    public function linkSmsUpdate(Request $request, SettingsService $settingsService){
        try {
            // Update API credentials in database - use updateOrCreate directly on SMSApiSetting
            // to avoid SettingsService validation errors when settings don't exist
            if ($request->filled('LINK_API_KEY')) {
                SMSApiSetting::updateOrCreate(
                    ['key' => 'api.link_api_key'],
                    [
                        'value' => $request->input('LINK_API_KEY'),
                        'category' => 'api',
                        'type' => 'string',
                        'description' => 'Link SMS API Key',
                        'display_name' => 'Link API Key',
                        'is_editable' => true,
                    ]
                );
            }

            if ($request->filled('LINK_SENDER_ID')) {
                SMSApiSetting::updateOrCreate(
                    ['key' => 'api.link_sender_id'],
                    [
                        'value' => $request->input('LINK_SENDER_ID'),
                        'category' => 'api',
                        'type' => 'string',
                        'description' => 'Link SMS Sender ID',
                        'display_name' => 'Link Sender ID',
                        'is_editable' => true,
                    ]
                );
            }

            // Clear settings cache to ensure new values are used immediately
            $settingsService->refresh();

            // Update SMS package rates if provided
            if ($request->filled('sms_rate_basic')) {
                SMSApiSetting::updateOrCreate(
                    ['key' => 'sms_rate_basic'],
                    [
                        'value' => $request->input('sms_rate_basic'),
                        'category' => 'pricing',
                        'type' => 'decimal',
                        'description' => 'Cost per SMS unit for Basic package (BWP)',
                        'display_name' => 'Basic Package Rate',
                        'is_editable' => true,
                    ]
                );
            }

            if ($request->filled('sms_rate_standard')) {
                SMSApiSetting::updateOrCreate(
                    ['key' => 'sms_rate_standard'],
                    [
                        'value' => $request->input('sms_rate_standard'),
                        'category' => 'pricing',
                        'type' => 'decimal',
                        'description' => 'Cost per SMS unit for Standard package (BWP)',
                        'display_name' => 'Standard Package Rate',
                        'is_editable' => true,
                    ]
                );
            }

            if ($request->filled('sms_rate_premium')) {
                SMSApiSetting::updateOrCreate(
                    ['key' => 'sms_rate_premium'],
                    [
                        'value' => $request->input('sms_rate_premium'),
                        'category' => 'pricing',
                        'type' => 'decimal',
                        'description' => 'Cost per SMS unit for Premium package (BWP)',
                        'display_name' => 'Premium Package Rate',
                        'is_editable' => true,
                    ]
                );
            }

            // Clear SMS rates cache
            app(\App\Services\Messaging\SmsCostCalculator::class)->clearRatesCache();

            // Handle SMS package and account balance - only if package fields are provided
            if ($request->filled('package_amount') && $request->filled('sms_credits_package')) {
                $accountBalance = AccountBalance::firstOrNew(['id' => 1]);

                if ($request->has('upgrade_package') || !$accountBalance->exists) {
                    $newBalance = $accountBalance->exists ?
                        $accountBalance->balance_bwp + $request->package_amount :
                        $request->package_amount;

                    $accountBalance->fill([
                        'sms_credits_package' => $request->sms_credits_package,
                        'package_amount' => $request->package_amount,
                        'balance_bwp' => $newBalance
                    ])->save();
                }
            }

            return redirect()->back()->with('message', 'SMS settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating SMS settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while updating SMS settings. Please try again.'])
                ->withInput();
        }
    }

    public function updateSettings(Request $request, SettingsService $settingsService){
        try {
            $userId = auth()->id();
            $fieldMapping = [
                'MAILER' => 'email.mailer',
                'HOST' => 'email.host',
                'PORT' => 'email.port',
                'USERNAME' => 'email.username',
                'PASSWORD' => 'email.password',
                'ENCRYPTION' => 'email.encryption',
                'FROM_ADDRESS' => 'email.from_address',
                'FROM_NAME' => 'email.from_name',
            ];

            foreach ($request->except('_token') as $key => $value) {
                if (isset($fieldMapping[$key])) {
                    $settingsService->set($fieldMapping[$key], $value, $userId);
                }
            }

            $env = File::get(base_path('.env'));
            $newEnv = $env;

            foreach ($request->except('_token') as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $escapedValue = $value;
                if ($key === 'PASSWORD' || $key === 'FROM_NAME') {
                    $cleanValue = trim($value, '"\'');
                    $escapedValue = "'" . str_replace("'", "\\'", $cleanValue) . "'";
                } elseif (strpos($value, ' ') !== false) {
                    $escapedValue = '"' . str_replace('"', '\\"', $value) . '"';
                }

                $pattern = "/^MAIL_{$key}=.*$/m";
                $replacement = "MAIL_{$key}={$escapedValue}";

                $newEnv = preg_replace($pattern, $replacement, $newEnv);
                if ($newEnv === null) {
                    Log::error("Regex error replacing MAIL_{$key}");
                    throw new \Exception("Failed to update MAIL_{$key} in .env file");
                }
            }

            File::put(base_path('.env'), $newEnv);
            $settingsService->refresh();

            return redirect()->back()->with('message', 'Email settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating email settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while updating email settings. Please try again.'])
                ->withInput();
        }
    }

    public function updateNotificationSettings(Request $request, SettingsService $settingsService){
        try {
            $settings = $request->input('settings', []);
            $userId = auth()->id();
            $result = $settingsService->bulkUpdate($settings, $userId);

            if (!empty($result['failed'])) {
                $errorMessages = [];
                foreach ($result['failed'] as $key => $errors) {
                    $errorMessages[] = "$key: " . implode(', ', array_map(function($error) {
                        return is_array($error) ? implode(', ', $error) : $error;
                    }, $errors));
                }

                return redirect()
                    ->back()
                    ->withErrors($errorMessages)
                    ->withInput();
            }

            $settingsService->refresh();
            $message = $result['success'] > 0
                ? "Successfully updated {$result['success']} setting(s)."
                : "No settings were updated.";

            return redirect()->back()->with('message', $message);

        } catch (\Exception $e) {
            Log::error('Error updating notification settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while updating settings. Please try again.'])
                ->withInput();
        }
    }

    public function store(Request $request){
        Log::info('Store method called', ['request_data' => $request->all()]);
        try {
            $validatedData = $request->validate([
                'school_name' => 'required|string|max:100',
                'slogan' => 'nullable|string|max:200',
                'telephone' => 'nullable|string|max:20',
                'fax' => 'nullable|string|max:20',
                'email_address' => 'required|string|max:255',
                'physical_address' => 'nullable|string|max:100',
                'postal_address' => 'nullable|string|max:200',
                'website' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:255',
                'logo_path' => 'nullable|string|max:255',
                'letterhead_path' => 'nullable|string|max:255',
                'school_sms_signature' => 'nullable|string|max:255',
                'school_email_signature' => 'nullable|string|max:255',
                'ownership' => 'nullable|string|max:255',
                'boarding' => 'nullable|in:0,1',
            ]);

            $validatedData['boarding'] = $request->input('boarding') === '1';
            $schoolSetup = SchoolSetup::first();
            if ($schoolSetup) {
                $schoolSetup->update($validatedData);
            } else {
                SchoolSetup::create($validatedData);
            }

            return redirect()->back()->with('message', 'School Information updated successfully!');
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors(), 'request_data' => $request->all()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Store method error', ['error' => $e->getMessage(), 'request_data' => $request->all()]);
            return redirect()->back()->with('message', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function regenerateSchoolId(){
        try {
            $schoolSetup = SchoolSetup::first();
            if (!$schoolSetup) {
                return redirect()->back()->with('error', 'No school setup found.');
            }

            $schoolSetup->school_id = null;
            $schoolSetup->save();

            return redirect()->back()->with('message', 'School ID regenerated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while regenerating School ID: ' . $e->getMessage());
        }
    }

    public function closeTerm($termId){
        $term = Term::findOrFail($termId);
        $term->closed = 1;
        $term->save();
        return redirect()->back()->with('message', 'Term closed successfully!');
    }

    function dataImporting(){
        $terms = Term::all();
        $school_data = SchoolSetup::first();
        return view('settings.data-importing', ['terms' => $terms, 'school_data' => $school_data]);
    }

    public function importStaff(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($request->has('deleteStaff')) {
            $success = $this->clearUserRecords();
            
            if (!$success) {
                return redirect()->back()->with('error', 'An error occurred while clearing user data.');
            }
            
            $nonAdminUsersCount = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Administrator');
            })->count();
            
            if ($nonAdminUsersCount > 0) {
                return redirect()->back()->with('error', 'Data cleanup failed: ' . $nonAdminUsersCount . ' non-administrator users still exist in the database.');
            }
            
            $adminCount = User::whereHas('roles', function ($query) {
                $query->where('name', 'Administrator');
            })->count();
            
            if ($adminCount === 0) {
                return redirect()->back()->with('error', 'Data cleanup issue: No administrator users found in the database!');
            }
        }

        $import = new UserImport();
        try {
            $fileType = $this->getFileType($request->file('file')->getClientOriginalName());
            Excel::import($import, $request->file('file'), null, $fileType);

            $rowCount = $import->rowsCount;
            $successfulImports = $import->successfulImports;
            $skippedRows = $import->skippedRows;

            if ($import->failures()->isNotEmpty()) {
                $failureMessages = [];
                foreach ($import->failures() as $failure) {
                    $errorMessages = implode(', ', $failure->errors());
                    $failureMessages[] = "Row {$failure->row()}: {$errorMessages}";
                }
                return redirect()->back()->withErrors($failureMessages);
            }

            return redirect()->back()->with('message', "Users imported successfully. Total staff processed: {$rowCount}, Successful imports: {$successfulImports}, Rows Skipped: {$skippedRows}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing users: ' . $e->getMessage());
        }
    }

    public function importSponsors(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);
        
        if ($request->has('deleteSponsors')) {
            $this->clearSponsorRecords();

            if (Sponsor::count() > 0 || Sponsor::withTrashed()->count() > 0) {
                return redirect()->back()->with('error', 'Sponsors data was not completely cleared! There are still records in the table or trash.');
            }
        }

        $file = $request->file('file');
        try {
            $import = new SponsorsImport();
            DB::transaction(function () use ($file, $import) {
                Excel::import($import, $file);
            });

            $successfulImports = $import->successfulImports;
            return redirect()->back()->with('message',
                "Sponsors imported successfully. Total records imported: {$successfulImports}."
            );
        } catch (Exception $e) {
            Log::error("Import failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error importing sponsors: ' . $e->getMessage());
        }
    }

    public function importStudents(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);
        set_time_limit(300);

        if ($request->has('deleteStudents')) {
            $this->clearAcademicRecords();
            if (Student::count() > 0 || Student::withTrashed()->count() > 0) {
                return redirect()->back()->with('error', 'Students table was not cleared!');
            }
        }

        $termId = $request->input('term_id');
        $import = new StudentsImport($termId);

        try {
            $fileType = $this->getFileType($request->file('file')->getClientOriginalName());
            Excel::import($import, $request->file('file'), null, $fileType);

            $rowCount = $import->rowsCount;
            if ($import->failures()->isNotEmpty()) {
                $failureMessages = [];
                foreach ($import->failures() as $failure) {
                    $errorMessages = implode(', ', $failure->errors());
                    $failureMessages[] = "Row {$failure->row()}: {$errorMessages}";
                }
                return redirect()->back()->withErrors($failureMessages);
            }

            return redirect()->back()->with('message', "Students imported successfully. Total students processed: {$rowCount}");
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error importing Students: ' . $e->getMessage());
        }
    }


    public function importAdmissions(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $termId = TermHelper::getCurrentTerm()->id;
        if ($request->has('deleteAdmissions')) {
            $clearResult = $this->clearAdmissionsRecords();
            if (!$clearResult['success']) {
                return redirect()->back()->with('error', $clearResult['message']);
            }
        }

        $import = new AdmissionsImport($termId);

        try {
            $fileType = $this->getFileType($request->file('file')->getClientOriginalName());
            Excel::import($import, $request->file('file'), null, $fileType);

            if ($import->failures()->isNotEmpty()) {
                $failureMessages = [];
                foreach ($import->failures() as $failure) {
                    $errorMessages = implode(', ', $failure->errors());
                    $failureMessages[] = "Row {$failure->row()}: {$errorMessages}";
                }
                return redirect()->back()->withErrors($failureMessages);
            }

            $rowCount = $import->rowsCount;
            $successfulImports = $import->successfulImports;
            $skippedRows = $import->skippedRows;

            return redirect()->back()->with('message', "Admissions imported successfully. Total admissions processed: {$rowCount}, Successful imports: {$successfulImports}, Rows Skipped: {$skippedRows}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing Admissions: ' . $e->getMessage());
        }
    }

    private function clearAcademicRecords(){
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('klass_student')->delete();
            DB::table('student_term')->delete();
    
            $models = [
                Comment::class,
                Holiday::class,
                Attendance::class,
                KlassSubject::class,
                Klass::class,
                StudentBehaviour::class,
                StudentMedicalInformation::class,
                StudentTest::class,
                SubjectComment::class,
                Student::class,
            ];
    
            foreach ($models as $model) {
                $model::withTrashed()->chunkById(100, function ($records) {
                    foreach ($records as $record) {
                        $record->forceDelete();
                    }
                });
            }
    
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            CacheHelper::forgetStudentsData();
            CacheHelper::forgetStudentsTermData();
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::rollBack();
            return redirect()->back()->with('message', 'Error occurred cleaning academic records: ' . $e->getMessage());
        }
    }
    
    private function clearSponsorRecords(){
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Sponsor::withTrashed()->get()->each(function($sponsor) {
                $sponsor->forceDelete();
            });
    
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();
            CacheHelper::forgetSponsors();
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::rollBack();
            return redirect()->back()->with('message', 'Error occurred cleaning sponsor records: ' . $e->getMessage());
        }
    }
    
    private function clearAdmissionsRecords(){
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Admission::withTrashed()->get()->each(function($admission) {
                $admission->forceDelete();
            });
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();
            CacheHelper::forgetAdmissions();
            return ['success' => true, 'message' => 'Admissions records cleared successfully.'];
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::rollBack();
            return ['success' => false, 'message' => 'Error occurred clearing admissions records: ' . $e->getMessage()];
        }
    }

    private function clearUserRecords(){
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Qualification::truncate();
            WorkHistory::truncate();
            Logging::truncate();
            DB::table('password_resets')->truncate();
            
            $nonAdminUsers = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Administrator');
            })->get();
            
            foreach ($nonAdminUsers as $user) {
                $user->roles()->detach();
                $user->forceDelete();
            }
            
            CacheHelper::forgetStaff();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();
            
            Log::info('User-related tables cleanup completed, preserving users with administrator roles.');
            return true;
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error('Error occurred cleaning user records: ' . $e->getMessage());
            return false;
        }
    }
    
    public function clearLogs(){
        DB::table('loggings')->truncate();
        return redirect()->back()->with('message', 'Logs cleared successfully!');
    }

    protected function getFileType($filename){
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'xlsx':
                return \Maatwebsite\Excel\Excel::XLSX;
            case 'xls':
                return \Maatwebsite\Excel\Excel::XLS;
            case 'csv':
                return \Maatwebsite\Excel\Excel::CSV;
            default:
                throw new \Exception("Invalid file type: {$extension}");
        }
    }

    public static function getLocationByIp($ip){
        self::initialize();
        if (self::isPrivateIp($ip)) {
            return 'local';
        }

        try {
            $response = self::$client->get("http://ipinfo.io/{$ip}/json", [
                'query' => ['token' => self::$apiKey]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['city'] ?? 'unknown';
        } catch (\Exception $e) {
            Log::error("Failed to get location for IP {$ip}: " . $e->getMessage());
            return 'unknown';
        }
    }

    protected static function isPrivateIp($ip){
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    function downloadImportFile(Request $request, $filename) {
        $requestedSchoolType = $request->query('school_type');
        $schoolType = SchoolSetup::normalizeType($requestedSchoolType ?: SchoolSetup::schoolType()) ?? SchoolSetup::TYPE_JUNIOR;

        if ($requestedSchoolType !== null && !in_array($schoolType, SchoolSetup::validTypes(), true)) {
            abort(404);
        }

        $templates = [
            'import-staff.xlsx' => fn() => ImportTemplateExport::staff(),
            'import-sponsors.xlsx' => fn() => ImportTemplateExport::sponsors(),
            'import-students.xlsx' => fn() => ImportTemplateExport::students($schoolType),
            'import-admissions.xlsx' => fn() => ImportTemplateExport::admissions(),
        ];

        if (!isset($templates[$filename])) {
            abort(404);
        }

        return Excel::download($templates[$filename](), $filename);
    }


    public function removeRole($userId, $roleId){
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $user = auth()->user();
        if (!is_numeric($userId) || !is_numeric($roleId)) {
            return redirect()->back()->withErrors('Invalid user or role ID.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->back()->withErrors('User not found.');
        }

        $role = Role::find($roleId);
        if (!$role) {
            return redirect()->back()->withErrors('Role not found.');
        }

        if (!$user->roles()->where('role_id', $roleId)->exists()) {
            return redirect()->back()->withErrors('The user does not have this role.');
        }

        try {
            $user->roles()->detach($roleId);
        } catch (\Exception $e) {
            Log::error('Error detaching role: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while removing the role.');
        }

        CacheHelper::forgetKlassesForTerm($currentTermId,$user);
        return redirect()->back()->with('message', 'Role removed successfully.');
    }

    public function updateTermDates(Request $request){
        $request->validate([
            'term1_start_date'     => 'nullable|date',
            'term1_end_date'       => 'nullable|date|after:term1_start_date',
            'term1_extension_days' => 'nullable|integer|min:0',

            'term2_start_date'     => 'nullable|date',
            'term2_end_date'       => 'nullable|date|after:term2_start_date',
            'term2_extension_days' => 'nullable|integer|min:0',

            'term3_start_date'     => 'nullable|date',
            'term3_end_date'       => 'nullable|date|after:term3_start_date',
            'term3_extension_days' => 'nullable|integer|min:0',

            'term_year'            => 'nullable|integer',
        ]);

        // Use the submitted year if provided, otherwise fall back to existing logic
        if ($request->filled('term_year')) {
            $targetYear = $request->input('term_year');
        } else {
            $currentYear = Carbon::now()->year;
            $previousYear = $currentYear - 1;

            $lastTermOfPreviousYear = Term::where('year', $previousYear)->orderBy('end_date', 'desc')->first();

            if ($lastTermOfPreviousYear && !$lastTermOfPreviousYear->closed) {
                $targetYear = $previousYear;
            } else {
                $targetYear = $currentYear;
            }
        }
    
        $submittedTermData = [];
    
        for ($termNumber = 1; $termNumber <= 3; $termNumber++) {
            $startDateKey = "term{$termNumber}_start_date";
            $endDateKey   = "term{$termNumber}_end_date";
            $extensionKey = "term{$termNumber}_extension_days";
    
            if ($request->filled($startDateKey)) {
                $submittedTermData[$termNumber] = [
                    'start_date'     => $request->input($startDateKey),
                    'end_date'       => $request->input($endDateKey),
                    'extension_days' => $request->input($extensionKey, 0),
                ];
            }
        }
    
        try {
            foreach ($submittedTermData as $termNumber => $termData) {
                $term = Term::where('term', $termNumber)
                            ->where('year', $targetYear)
                            ->where('closed', 0)
                            ->first();
    
                if ($term) {
                    $term->update($termData);
                }
            }
    
            return redirect()->back()->with('message', 'Term dates updated successfully.');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()
                    ->withErrors(['error' => 'Cannot create duplicate terms. A term with this number already exists for the academic year.'])
                    ->withInput();
            }
            throw $e;
        }
    }

    public function getTermsByYear($year)
    {
        $terms = Term::where('year', $year)->orderBy('term')->get();
        $availableYears = Term::select('year')->distinct()->orderBy('year')->pluck('year');

        return response()->json([
            'terms' => $terms,
            'availableYears' => $availableYears
        ]);
    }

    function uploadLogo(Request $request){
        $request->validate([
            'logo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
                Rule::dimensions()->width(500)->height(500),
            ],
        ], [
            'logo.dimensions' => 'The logo must be exactly 500x500 pixels.',
            'logo.max' => 'The logo must not exceed 2MB.',
            'logo.mimes' => 'Only PNG, JPG and GIF images are allowed.',
        ]);

        try {
            $schoolSetup = SchoolSetup::first();

            if (!$schoolSetup) {
                $message = 'School setup not found. Please configure school details first.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $message], 404)
                    : redirect()->back()->with('error', $message);
            }

            if ($schoolSetup->logo_path) {
                $oldPath = str_replace('/storage/', 'public/', $schoolSetup->logo_path);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $image = $request->file('logo');
            $imageName = time() . '.' . $image->extension();
            $path = $image->storeAs('public/branding', $imageName);

            // Update only the logo_path field, preserving all other fields including school_id
            $schoolSetup->logo_path = Storage::url($path);
            $schoolSetup->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logo uploaded successfully!',
                    'logo_path' => $schoolSetup->logo_path,
                ]);
            }

            return redirect()->back()->with('message', 'Logo uploaded successfully!');
        } catch (\Throwable $e) {
            Log::error('Error uploading school logo: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $message = 'An error occurred while uploading the logo. Please try again.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }
    }

    public function uploadLoginImage(Request $request) {
        $request->validate([
            'login_image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120',
                Rule::dimensions()->width(1000)->height(600),
            ],
        ], [
            'login_image.dimensions' => 'The image must be exactly 1000x600 pixels.',
            'login_image.max' => 'The image must not exceed 5MB.',
            'login_image.mimes' => 'Only JPEG and PNG images are allowed.',
        ]);

        try {
            $schoolSetup = SchoolSetup::first();

            if (!$schoolSetup) {
                $message = 'School setup not found. Please configure school details first.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $message], 404)
                    : redirect()->back()->with('error', $message);
            }

            // Delete previous custom login image if one exists
            if ($schoolSetup->login_image_path) {
                $oldPath = str_replace('/storage/', 'public/', $schoolSetup->login_image_path);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $image = $request->file('login_image');
            $imageName = 'login_' . time() . '.' . $image->extension();
            $path = $image->storeAs('public/branding/login', $imageName);

            $schoolSetup->login_image_path = Storage::url($path);
            $schoolSetup->use_custom_login_image = true;
            $schoolSetup->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login page image uploaded successfully!',
                    'login_image_path' => $schoolSetup->login_image_path,
                ]);
            }

            return redirect()->back()->with('message', 'Login page image uploaded successfully!');
        } catch (\Throwable $e) {
            Log::error('Error uploading school login image: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $message = 'An error occurred while uploading the login image. Please try again.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }
    }

    public function toggleLoginImage(Request $request) {
        $schoolSetup = SchoolSetup::first();

        if (!$schoolSetup) {
            return redirect()->back()->with('error', 'School setup not found.');
        }

        // Checkbox sends "1" when checked, nothing when unchecked
        $wantsEnabled = $request->has('enable');

        if ($wantsEnabled && !$schoolSetup->login_image_path) {
            return redirect()->back()->with('error', 'Please upload a custom login image first.');
        }

        $schoolSetup->use_custom_login_image = $wantsEnabled;
        $schoolSetup->save();

        $status = $wantsEnabled ? 'enabled' : 'disabled';
        return redirect()->back()->with('message', "Custom login image {$status}.");
    }

    public function termRollover(Request $request){
        $validator = Validator::make($request->all(), [
            'fromTermId' => 'required|integer|exists:terms,id',
            'toTermId'   => 'required|integer|exists:terms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input data.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $fromTermId = $request->input('fromTermId');
        $toTermId   = $request->input('toTermId');

        try {
            $fromTerm = Term::findOrFail($fromTermId);
            $toTerm   = Term::findOrFail($toTermId);

            if ($toTerm->start_date <= $fromTerm->start_date) {
                return response()->json([
                    'message' => 'The "Rollover To" term must start after the "Rollover From" term.'
                ], 400);
            }
            Log::info('Starting term rollover from term ID ' . $fromTerm->id . ' to term ID ' . $toTerm->id);

            $result = $this->termRolloverService->rollover($fromTerm, $toTerm);
            Log::info('Term rollover completed successfully.');

            $autoCreated = is_array($result) ? ($result['autoCreatedGradeSubjects'] ?? 0) : 0;
            $message = 'Term rollover completed successfully.';
            if ($autoCreated > 0) {
                $message .= " {$autoCreated} missing grade-subject record(s) were auto-created.";
            }

            return response()->json([
                'message' => $message,
                'details' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error during term rollover: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred during the rollover process.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function previewYearRollover(Request $request) {
        try {
            $request->validate([
                'fromTermId' => 'required|integer|exists:terms,id',
                'toTermId' => 'required|integer|exists:terms,id',
            ]);

            $preview = $this->yearRolloverService->previewYearRollover(
                $request->input('fromTermId'),
                $request->input('toTermId')
            );

            return response()->json(['success' => true, 'preview' => $preview]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Preview year rollover error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate preview.'], 500);
        }
    }

    public function previewTermRollover(Request $request) {
        try {
            $request->validate([
                'fromTermId' => 'required|integer|exists:terms,id',
                'toTermId' => 'required|integer|exists:terms,id',
            ]);

            $preview = $this->termRolloverService->previewTermRollover(
                $request->input('fromTermId'),
                $request->input('toTermId')
            );

            return response()->json(['success' => true, 'preview' => $preview]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Preview term rollover error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate preview.'], 500);
        }
    }

    public function yearRollover(Request $request) {
        try {
            $request->validate([
                'fromTermId' => 'required|integer|exists:terms,id',
                'toTermId' => 'required|integer|exists:terms,id',
            ]);

            $fromTerm = Term::findOrFail($request->input('fromTermId'));
            $toTerm = Term::findOrFail($request->input('toTermId'));

            if ($toTerm->start_date <= $fromTerm->start_date) {
                return response()->json([
                    'canRollover' => false,
                    'message' => 'The destination term must start after the source term.'
                ], 400);
            }

            if ($fromTerm->closed) {
                return response()->json([
                    'canRollover' => false,
                    'message' => 'The source term is already closed and cannot be rolled over.'
                ], 400);
            }

            $result = $this->yearRolloverService->yearRollOver($fromTerm->id, $toTerm->id);

            $autoCreated = is_array($result) ? ($result['autoCreatedGradeSubjects'] ?? 0) : 0;
            $message = 'Year rollover completed successfully.';
            if ($autoCreated > 0) {
                $message .= " {$autoCreated} missing grade-subject record(s) were auto-created.";
            }

            return response()->json([
                'canRollover' => true,
                'message' => $message,
                'details' => $result
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in yearRollover: ' . $e->getMessage(), [
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'canRollover' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (RolloverException $e) {
            Log::error('Rollover Exception: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'context' => $e->getContextData(),
            ]);
            return response()->json([
                'canRollover' => false,
                'message' => 'An error occurred during the rollover process: ' . $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during year rollover: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'canRollover' => false,
                'message' => 'An unexpected error occurred during the rollover process.'
            ], 500);
        }
    }

    public function reverseYearRollover($historyId){
        try {
            $history = RolloverHistory::findOrFail($historyId);
            Log::info($history->status);

            if ($history->status !== 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only reverse completed rollovers'
                ], 422);
            }

            $result = $this->yearRolloverReverseService->reverseRollover($history->id);
            return response()->json([
                'status' => 'success',
                'message' => 'Year rollover successfully reversed',
                'details' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reverse term rollover: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reverseTermRollover($historyId){
        try {
            $history = TermRolloverHistory::findOrFail($historyId);
            if ($history->status !== 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only reverse completed rollovers'
                ], 422);
            }

            $result = $this->termRolloverReverseService->reverseTermRollover($history->id);
            return response()->json([
                'status' => 'success',
                'message' => 'Term rollover successfully reversed',
                'details' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reverse term rollover: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkGrades(Request $request){
        try {
            $request->validate([
                'fromTermId' => 'required|exists:terms,id',
                'toTermId' => 'required|exists:terms,id',
            ]);

            $fromTerm = Term::findOrFail($request->fromTermId);
            $toTerm = Term::findOrFail($request->toTermId);

            if ($toTerm->start_date <= $fromTerm->start_date) {
                return response()->json([
                    'canRollover' => false,
                    'message' => 'The destination term must start after the source term.'
                ]);
            }

            $gradesExist = Grade::where('term_id', $fromTerm->id)->exists();
            if (!$gradesExist) {
                return response()->json([
                    'canRollover' => false,
                    'message' => 'The selected source term does not have any grades.'
                ]);
            }

            $toTermHasGrades = Grade::where('term_id', $toTerm->id)->exists();
            if ($toTermHasGrades) {
                return response()->json([
                    'canRollover' => false,
                    'message' => 'The destination term already has grades. Please choose a different term.'
                ]);
            }

            return response()->json([
                'canRollover' => true,
                'message' => 'Ready to perform rollover.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in checkGrades: ' . $e->getMessage());
            return response()->json([
                'canRollover' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error checking grades: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'canRollover' => false,
                'message' => 'An error occurred while checking grades.'
            ], 500);
        }
    }


    public function rolloverErrorPage(Request $request)
    {
        $errorMessage = $request->session()->get('error_message', 'An error occurred during the year rollover process.');
        $errorCode = $request->session()->get('error_code', 'YR-' . time());

        return view('errors.rollover', [
            'errorMessage' => $errorMessage,
            'errorCode' => $errorCode
        ]);
    }

    public function gradesSetup(){
        $currentTerm = TermHelper::getCurrentTerm();
        $grades = Grade::where('term_id', $currentTerm->id)->get();
        $currentTerm = TermHelper::getCurrentTerm();
        return view('settings.grades-setup', ['grades' => $grades, 'term' => $currentTerm]);
    }

    public function gradesView($gradeId){
        $previousTermGrades = Grade::where('active', 0)
            ->where('year', now()->year - 1)
            ->get();
        $grade = Grade::findOrFail($gradeId);
        return view('settings.grades-setup-view', ['grade' => $grade, 'previousTerm' => $previousTermGrades]);
    }

    public function updateGrade(Request $request, $gradeId){
        $validatedData = $request->validate([
            'sequence' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'promotion' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|string|max:255',
            'active' => 'required|boolean'
        ]);
        $grade = Grade::findOrFail($gradeId);
        $grade->update([
            'sequence' => $validatedData['sequence'],
            'name' => $validatedData['name'],
            'promotion' => $validatedData['promotion'] ?? $grade->promotion,
            'description' => $validatedData['description'] ?? $grade->description,
            'level' => $validatedData['level'],
            'active' => $validatedData['active'],
        ]);
        return redirect()->back()->with('message', 'Grade updated successfully.');
    }


    public function createStorageSymlink()
    {
        $status = Artisan::call('storage:link');
        return redirect()->back()->with('message', $status === 0 ? 'Success' : 'Error');
    }

    public function clearConfigCache(){
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('cache:clear');
        return redirect()->back()->with('message','Config cache cleared successfully');
    }

    public function clearCaches(){
        $results = [];
        try {
            $results['cache_clear'] = Artisan::call('cache:clear');
            $results['cache_clear_output'] = Artisan::output();

            $allSuccessful = true;
            foreach ($results as $key => $value) {
                if (strpos($key, '_output') === false && $value !== 0) {
                    $allSuccessful = false;
                    break;
                }
            }

            $message = $allSuccessful ? 'Cache cleared successfully' : 'Error clearing cache';
            return redirect()->back()->with('results', $message);
        } catch (Exception $e) {
            Log::error('Error clearing cache: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while clearing cache');
        }
    }

    public function createBackup(){
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '1024M');
            ini_set('mysql.connect_timeout', 300);
            ini_set('default_socket_timeout', 300);

            Config::set('backup.backup.mysql.dump.dump_binary_path', '/usr/bin/');
            Config::set('backup.backup.database_dump.mysql.dump.extraOptions', [
                '--quick',
                '--compress',
                '--single-transaction',
                '--lock-tables=false',
                '--max_allowed_packet=1G',
                '--net_buffer_length=16384',
            ]);

            Artisan::call('backup:run', [
                '--only-db' => true,
                '--timeout' => 1800
            ]);
            $backupDestinations = BackupDestinationFactory::createFromArray(config('backup.backup'));
            $latestBackup = null;
            $latestBackupDate = null;

            foreach ($backupDestinations as $backupDestination) {
                $newestBackup = $backupDestination->newestBackup();

                if ($newestBackup) {
                    $newestBackupDate = $newestBackup->date();

                    if (!$latestBackupDate || $newestBackupDate->gt($latestBackupDate)) {
                        $latestBackup = $newestBackup;
                        $latestBackupDate = $newestBackupDate;
                    }
                }
            }

            if ($latestBackup) {
                $filePath = $latestBackup->path();
                $fileSize = $latestBackup->sizeInBytes();

                BackupLog::create([
                    'database_name' => config('database.connections.mysql.database'),
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'backup_time' => $latestBackup->date(),
                    'status' => 'success',
                ]);
            } else {
                BackupLog::create([
                    'database_name' => config('database.connections.mysql.database'),
                    'file_path' => null,
                    'file_size' => 0,
                    'backup_time' => now(),
                    'status' => 'failed',
                ]);
                Log::warning('No backup file found after running backup command.');
            }

            return redirect()->back()->with('message', 'Backup created successfully!');
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            BackupLog::create([
                'database_name' => config('database.connections.mysql.database'),
                'file_path' => null,
                'file_size' => 0,
                'backup_time' => now(),
                'status' => 'failed',
            ]);

            return redirect()->back()->withErrors(['message' => 'Failed to create backup. Please try again later.']);
        }
    }


    public function downloadBackup($filename){
        if (!$this->isValidFilename($filename)) {
            abort(404, 'Invalid file name.');
        }

        $diskName = config('backup.backup.destination.disks')[0];
        $backupFolder = config('backup.backup.name') ?? 'Laravel';
        $storageDisk = Storage::disk($diskName);

        $filePath = $backupFolder . '/' . $filename;

        if (!$storageDisk->exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        $stream = $storageDisk->readStream($filePath);
        if (!$stream) {
            abort(500, 'Could not read the backup file.');
        }

        $fileSize = $storageDisk->size($filePath);

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        if ($fileSize !== false) {
            $headers['Content-Length'] = $fileSize;
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, $headers);
    }

    private function isValidFilename($filename)
    {
        return !Str::contains($filename, ['..', '/', '\\']);
    }
}
