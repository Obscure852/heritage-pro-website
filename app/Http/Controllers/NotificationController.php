<?php

namespace App\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Helpers\SMSHelper;
use App\Helpers\TermHelper;
use App\Jobs\SendBulkEmailJob;
use App\Jobs\SendBulkWithLinkSMS;
use App\Mail\BulkEmail;
use App\Models\AccountBalance;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationAttachment;
use App\Models\NotificationComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Models\Email;
use App\Models\Grade;
use App\Models\Message;
use App\Models\SchoolSetup;
use App\Models\SMSApiSetting;
use App\Models\SmsJobTracking;
use App\Models\Sponsor;
use App\Models\WhatsappTemplate;
use App\Services\EmailService;
use App\Services\Messaging\CommunicationChannelService;
use App\Services\Messaging\SmsBalanceService;
use App\Services\Messaging\SmsCostCalculator;
use App\Services\Messaging\SmsJobService;
use App\Services\Messaging\SmsPlaceholderService;
use App\Services\Messaging\WhatsAppMessagingService;
use Cache;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $terms = StudentController::terms();
        $currentTerm = TermHelper::getCurrentTerm();
        $notifications = CacheHelper::getNotifications();
        return view('communications.index', ['notifications' => $notifications, 'terms' => $terms, 'currentTerm' => $currentTerm]);
    }


    public function bulkMessaging(){
        $channelService = app(CommunicationChannelService::class);
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $grades = Grade::where('active', 1)->where('term_id', $selectedTermId)->get();

        $departments = Department::select(['id', 'name'])->get();
        $positions = User::pluck('position')->unique();
        $area_of_work = DB::table('area_of_work')->select(['id', 'name'])->get();

        $messages = Message::with(['user', 'sponsor', 'author'])
            ->where('term_id', $selectedTermId)->get();

        $bulkMessages = $messages->where('term_id', $selectedTermId)->where('type', 'bulk')->groupBy('body');
        $nonBulkMessages = $messages->where('term_id', $selectedTermId)->where('type', '!=', 'bulk');

        $totalBulkCost = 0;
        $costCalculator = app(SmsCostCalculator::class);
        foreach ($bulkMessages as $group) {
            foreach ($group as $message) {
                $totalBulkCost += $costCalculator->calculateTotalCost($message->num_recipients, $message->sms_count);
            }
        }

        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();

        $school_data = SchoolSetup::first();
        $account_balance = AccountBalance::first();

        $userfilters = CacheHelper::getUserFilterList();
        $sponsorfilters = CacheHelper::getSponsorFilterList();

        return view('communications.bulk-sms-index', [
            'grades' => $grades,
            'messages' => $messages,
            'bulkMessages' => $bulkMessages,
            'nonBulkMessages' => $nonBulkMessages,
            'departments' => $departments,
            'positions' => $positions,
            'area_of_work' => $area_of_work,
            'school_data' => $school_data,
            'balance' => $account_balance,
            'totalBulkCost' => $totalBulkCost,
            'user_filters' => $userfilters,
            'sponsor_filters' => $sponsorfilters,
            'currentTerm' => $currentTerm,
            'terms' => $terms,
            'whatsappTemplates' => WhatsappTemplate::approved()->orderBy('name')->get(),
            'smsEnabled' => $channelService->smsEnabled(),
            'whatsappEnabled' => $channelService->whatsappEnabled(),
        ]);
    }


    public function bulkMailing(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $grades = Grade::where('active', 1)->where('term_id', $selectedTermId)->get();

        $departments = Department::select(['id', 'name'])->get();
        $positions = User::pluck('position')->unique();
        $area_of_work = DB::table('area_of_work')->select(['id', 'name'])->get();

        $emails = Email::with(['user', 'sponsor', 'sender'])
            ->where('term_id', $selectedTermId)->get();

        $bulkEmails = $emails->where('term_id', $selectedTermId)->where('type', 'bulk')->groupBy('body');
        $nonBulkEmails = $emails->where('term_id', $selectedTermId)->where('type', 'direct');

        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();

        $school_data = SchoolSetup::first();
        $user_filters = CacheHelper::getUserFilterList();
        $sponsor_filters = CacheHelper::getSponsorFilterList();

        return view('communications.bulk-mail-index', [
            'grades' => $grades,
            'departments' => $departments,
            'positions' => $positions,
            'area_of_work' => $area_of_work,
            'school_data' => $school_data,
            'user_filters' => $user_filters,
            'sponsor_filters' => $sponsor_filters,
            'currentTerm' => $currentTerm,
            'bulkEmails' => $bulkEmails,
            'nonBulkEmails' => $nonBulkEmails,
            'terms' => $terms,
            'emails' => $emails,
        ]);
    }


    public function notificationDetails($id)
    {
        $notification = Notification::findOrFail($id);
        return view('communications.notification-details', ['notification' => $notification]);
    }


    public function togglePin($id) {
        $notification = Notification::findOrFail($id);
        $notification->is_pinned = !$notification->is_pinned;
        $notification->save();

        CacheHelper::forgetNotifications();
        CacheHelper::forgetDashboardNotifications();

        $status = $notification->is_pinned ? 'pinned' : 'unpinned';
        return redirect()->back()->with('message', "Notification {$status} successfully.");
    }

    public function deleteComment($id){
        $notificationComment = NotificationComment::findOrFail($id);
        $notificationComment->delete();
        CacheHelper::forgetDashboardNotifications();
        return redirect()->back()->with('message','Comment deleted sucessfully!');
    }

    public function deleteNotification($id){
        $notification = Notification::findOrFail($id);
        $notification->delete();
        CacheHelper::forgetDashboardNotifications();
        return redirect()->back()->with('message','Comment deleted sucessfully!');
    }

    public function download($id){
        $attachment = NotificationAttachment::find($id);

        if (!$attachment) {
            return abort(404);
        }

        $fileName = pathinfo($attachment->file_path, PATHINFO_FILENAME);
        $fileExtension = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
        return $this->prepareDownloadResponse('document', $fileExtension, $attachment);
    }

    private function prepareDownloadResponse(string $fileName, string $fileExtension, NotificationAttachment $attachment)
    {
        $file = Storage::disk('public')->get($attachment->file_path);
        $headers = [
            'Content-Type' => $attachment->file_type,
            'Content-Disposition' => 'attachment;filename="' . $fileName . '.' . $fileExtension . '"',
        ];
        return response($file, 200, $headers);
    }

    public function notificationComment(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id',
            'body' => 'required|string|max:500',
        ]);

        $cleanBody = $request->input('body');
        NotificationComment::create([
            'notification_id' => $request->input('notification_id'),
            'user_id' => Auth::id(),
            'body' => $cleanBody,
        ]);
        return redirect()->back();
    }

    public function staffNotification()
    {
        $departments = CacheHelper::getDepartments();
        $areaOfWork = DB::table('area_of_work')->get();
        return view('communications.create-staff-notification', ['departments' => $departments, 'areaOfWork' => $areaOfWork]);
    }

    public function sponsorNotification(){
        $filters = CacheHelper::getSponsorFilterList();
        return view('communications.create-sponsor-notification', ['filters' => $filters]);
    }

    public function store(Request $request){
        try {
            $messages = [
                'notification_title.required' => 'The notification title is required.',
                'notification_title.string' => 'The notification title must be a valid string.',
                'notification_title.max' => 'The notification title must not exceed 255 characters.',
                'notification_body.required' => 'The notification body is required.',
                'notification_body.string' => 'The notification body must be a valid string.',
                'is_general.required' => 'The notification type is required.',
                'is_general.boolean' => 'The notification type must be either true or false.',
                'department_id.exists' => 'The selected department does not exist in the database.',
                'area_of_work.string' => 'The area of work must be a valid string.',
                'allow_comments.boolean' => 'The allow comments field must be either true or false.',
                'start_date.required' => 'The start date is required.',
                'start_date.date' => 'The start date must be a valid date.',
                'end_date.required' => 'The end date is required.',
                'end_date.date' => 'The end date must be a valid date.',
                'end_date.after' => 'The end date must be after the start date.',
                'attachment.file' => 'The attachment must be a valid file.',
                'attachment.mimes' => 'The attachment must be a file of type: jpg, jpeg, png, gif, doc, docx, pdf, xls, xlsx.',
                'attachment.max' => 'The attachment must not exceed 2MB.',
            ];

            $validatedData = $request->validate([
                'notification_title' => 'required|string|max:255',
                'notification_body' => 'required|string',
                'is_general' => 'required|boolean',
                'department_id' => 'nullable|exists:departments,id',
                'area_of_work' => 'nullable|string',
                'allow_comments' => 'nullable|boolean',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,doc,docx,pdf,xls,xlsx|max:2048',
            ], $messages);

            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return redirect()->back()->with('error', 'No current term is active. Please configure the current term first.');
            }

            DB::beginTransaction();

            $notification = Notification::create([
                'term_id' =>  $currentTerm->id,
                'user_id' => Auth::id(),
                'title' => $validatedData['notification_title'],
                'body' => $validatedData['notification_body'],
                'is_general' => $validatedData['is_general'],
                'allow_comments' => $request->boolean('allow_comments'),
                'department_id' => $validatedData['department_id'],
                'area_of_work' => $validatedData['area_of_work'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
            ]);

            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $originalName = $attachment->getClientOriginalName();

                $fileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . Str::uuid() . '.' . $attachment->getClientOriginalExtension();
                $path = $attachment->storeAs('attachments', $fileName, 'public');
                $fileType = $attachment->getClientMimeType();

                NotificationAttachment::create([
                    'notification_id' => $notification->id,
                    'original_name' => $originalName,
                    'file_path' => $path,
                    'file_type' => $fileType,
                ]);
            }

            $recipients = [];
            if ($validatedData['is_general']) {
                $recipients = CacheHelper::getUsers();
            } else {
                $departmentId = $validatedData['department_id'];
                $areaOfWork = $validatedData['area_of_work'];

                $recipients = User::query()
                    ->when($departmentId && $areaOfWork, function ($query) use ($departmentId, $areaOfWork) {
                        return $query->where(function ($q) use ($departmentId, $areaOfWork) {
                            $department = Department::findOrFail($departmentId);
                            $q->where('department', $department->name)
                                ->orWhere('area_of_work', $areaOfWork);
                        });
                    })
                    ->when($departmentId && !$areaOfWork, function ($query) use ($departmentId) {
                        $department = Department::findOrFail($departmentId);
                        return $query->where('department', $department->name);
                    })
                    ->when(!$departmentId && $areaOfWork, function ($query) use ($areaOfWork) {
                        return $query->where('area_of_work', $areaOfWork);
                    })
                    ->get();
            }

            $notification->recipients()->sync($recipients->pluck('id'));
            DB::commit();

            CacheHelper::forgetNotifications();
            CacheHelper::forgetDashboardNotifications();
            return redirect()->route('notifications.index')->with('message', 'Notification created successfully.');

        } catch (ValidationException $e) {
            Log::error('Validation error while creating notification', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notification creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'Failed to create notification. Please try again.');
        }
    }


    public function getMessages(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $dashboardData = SMSHelper::getMessagesDashboardData($selectedTermId);
        return view('communications.messages-term', $dashboardData);
    }

    private function fetchEmails($user, $termId)
    {
        $query = Email::with(['user', 'sponsor', 'sender'])
            ->where('term_id', $termId);

        if (!$user->hasAnyRoles(['Administrator', 'Communications Admin', 'Communications Edit'])) {
            $query->where('sender_id', $user->id);
        }
        return $query->get();
    }

    public function getEmails(){
        $selectedTermId = session()->get('selected_term_id', TermHelper::getCurrentTerm()->id);
        $user = auth()->user();

        $emails = $this->fetchEmails($user, $selectedTermId);
        $bulkMessages = $emails->where('type', 'Bulk')->groupBy('subject');
        $nonBulkMessages = $emails->where('type', '!=', 'Bulk');

        return view('communications.emails-term', [
            'messages' => $emails,
            'bulkEmails' => $bulkMessages,
            'nonBulkEmails' => $nonBulkMessages,
        ]);
    }


    public function getNotifications(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $notifications = Notification::with(['recipients', 'notificationComments', 'attachments', 'sponsorRecipients'])
            ->where('term_id', $selectedTermId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('communications.notifications-term', [
            'notifications' => $notifications,
        ]);
    }

    public function update(Request $request, $id){
        Log::info('Update method hit', [
            'id' => $id,
            'method' => $request->method(),
            'path' => $request->path()
        ]);

        try {
            $messages = [
                'notification_title.required' => 'The notification title is required.',
                'notification_title.string' => 'The notification title must be a valid string.',
                'notification_title.max' => 'The notification title must not exceed 255 characters.',
                'notification_body.required' => 'The notification body is required.',
                'notification_body.string' => 'The notification body must be a valid string.',
                'is_general.boolean' => 'The notification type must be either true or false.',
                'department_id.exists' => 'The selected department does not exist in the database.',
                'area_of_work.string' => 'The area of work must be a valid string.',
                'allow_comments.boolean' => 'The allow comments field must be either true or false.',
                'start_date.date' => 'The start date must be a valid date.',
                'end_date.date' => 'The end date must be a valid date.',
                'end_date.after' => 'The end date must be after the start date.',
                'attachment.file' => 'The attachment must be a valid file.',
                'attachment.mimes' => 'The attachment must be a file of type: jpg, jpeg, png, gif, doc, docx, pdf, xls, xlsx.',
                'attachment.max' => 'The attachment must not exceed 2MB.',
                'department_id.required_without' => 'The department field is required if area of work is not provided.',
                'area_of_work.required_without' => 'The area of work field is required if department is not provided.',
            ];

            $validatedData = $request->validate([
                'notification_title' => 'required|string|max:255',
                'notification_body' => 'required|string',
                'is_general' => 'nullable|boolean',
                'department_id' => 'nullable|exists:departments,id',
                'area_of_work' => 'nullable|string',
                'allow_comments' => 'nullable|boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,doc,docx,pdf,xls,xlsx|max:2048',
            ], $messages);

            $notification = Notification::findOrFail($id);
            $isGeneral = $request->boolean('is_general');
            if (!$isGeneral) {
                $request->validate([
                    'department_id' => 'required_without:area_of_work|exists:departments,id',
                    'area_of_work' => 'required_without:department_id|string',
                ], $messages);
            }

            DB::transaction(function () use ($request, $notification, $isGeneral, $validatedData) {
                $notification->update([
                    'term_id' => TermHelper::getCurrentTerm()->id,
                    'title' => $validatedData['notification_title'],
                    'body' => $validatedData['notification_body'],
                    'is_general' => $isGeneral,
                    'allow_comments' => $request->boolean('allow_comments'),
                    'department_id' => $validatedData['department_id'],
                    'area_of_work' => $validatedData['area_of_work'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                ]);

                if ($request->hasFile('attachment')) {
                    foreach ($notification->attachments as $attachment) {
                        if (Storage::disk('public')->exists($attachment->file_path)) {
                            Storage::disk('public')->delete($attachment->file_path);
                        }
                        $attachment->delete();
                    }

                    $attachmentFile = $request->file('attachment');
                    $originalName = $attachmentFile->getClientOriginalName();
                    $fileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . Str::uuid() . '.' . $attachmentFile->getClientOriginalExtension();
                    $path = $attachmentFile->storeAs('attachments', $fileName, 'public');
                    $fileType = $attachmentFile->getClientMimeType();

                    NotificationAttachment::create([
                        'notification_id' => $notification->id,
                        'original_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $fileType,
                    ]);
                }

                if ($isGeneral) {
                    $recipients = User::all();
                } else {
                    $departmentId = $request->input('department_id');
                    $areaOfWork = $request->input('area_of_work');

                    $recipients = User::query()
                        ->when($departmentId && $areaOfWork, function ($query) use ($departmentId, $areaOfWork) {
                            return $query->where(function ($q) use ($departmentId, $areaOfWork) {
                                $department = Department::findOrFail($departmentId);
                                $q->where('department', $department->name)
                                    ->orWhere('area_of_work', $areaOfWork);
                            });
                        })
                        ->when($departmentId && !$areaOfWork, function ($query) use ($departmentId) {
                            $department = Department::findOrFail($departmentId);
                            return $query->where('department', $department->name);
                        })
                        ->when(!$departmentId && $areaOfWork, function ($query) use ($areaOfWork) {
                            return $query->where('area_of_work', $areaOfWork);
                        })
                        ->get();
                }
                $notification->recipients()->sync($recipients->pluck('id'));
            });

            CacheHelper::forgetNotifications();
            CacheHelper::forgetDashboardNotifications();
            return redirect()->route('notifications.index')->with('message', 'Notification updated successfully.');

        } catch (ValidationException $e) {
            Log::error('Validation error while updating notification', [
                'errors' => $e->errors(),
                'notification_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Notification update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'notification_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withInput()->with('error', 'Failed to update notification. Please try again.');
        }
    }

    public function editNotification($id){
        $notification = Notification::findOrFail($id);
        $departments = Department::select(['id', 'name'])->get();
        $area_of_work = DB::table('area_of_work')->select(['id', 'name'])->get();

        return view('communications.edit-staff-notification', ['notification' => $notification, 'departments' => $departments, 'areaOfWork' => $area_of_work]);
    }


    public function editSponsorNotification($id){
        $notification = Notification::findOrFail($id);
        $filters = CacheHelper::getSponsorFilterList();
        return view('communications.edit-sponsor-notification', ['notification' => $notification,'filters' => $filters]);
    }

    public function destroyAttachment($notificationId, $attachmentId){
        $notification = Notification::findOrFail($notificationId);
        $attachment = $notification->attachments()->where('id', $attachmentId)->first();

        if (!$attachment) {
            return redirect()->back()->with('error', 'Attachment not found or does not belong to this notification.');
        }

        try {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->delete();
            CacheHelper::forgetNotifications();
            CacheHelper::forgetDashboardNotifications();

            return redirect()->back()->with('message', 'Attachment deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to delete attachment ID {$attachmentId}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete attachment. Please try again.');
        }
    }

    public function storeSponsorNotification(Request $request){
        $validatedData = $request->validate([
            'notification_title' => 'required|string|max:255',
            'notification_body'  => 'required|string',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'is_general'         => 'nullable',
            'allow_comments'     => 'nullable',
            'filter'             => 'nullable|exists:sponsor_filters,id',
            'attachment'         => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        DB::beginTransaction();
        $currentTerm = TermHelper::getCurrentTerm();

        try {
            $notification = new Notification();
            $notification->user_id = auth()->user()->id;
            $notification->term_id = $currentTerm->id;
            $notification->title         = $validatedData['notification_title'];
            $notification->body          = $validatedData['notification_body'];
            $notification->start_date    = $validatedData['start_date'];
            $notification->end_date      = $validatedData['end_date'];
            $notification->is_general    = isset($validatedData['is_general']) ? 1 : 0;
            $notification->allow_comments = isset($validatedData['allow_comments']) ? 1 : 0;

            if (!$notification->is_general && !empty($validatedData['filter'])) {
                $notification->filter_id = $validatedData['filter'];
            } else {
                $notification->filter_id = null;
            }

            $notification->save();
            if ($request->hasFile('attachment')) {
                $attachment   = $request->file('attachment');
                $originalName = $attachment->getClientOriginalName();
                $fileName     = pathinfo($originalName, PATHINFO_FILENAME)
                                . '_' . Str::uuid()
                                . '.' . $attachment->getClientOriginalExtension();
                $path         = $attachment->storeAs('attachments', $fileName, 'public');
                $fileType     = $attachment->getClientMimeType();

                NotificationAttachment::create([
                    'notification_id' => $notification->id,
                    'original_name'   => $originalName,
                    'file_path'       => $path,
                    'file_type'       => $fileType,
                ]);
            }

            if ($notification->is_general) {
                $sponsorIds = Sponsor::pluck('id')->toArray();
            } else {
                if (!empty($validatedData['filter'])) {
                    $sponsorIds = Sponsor::where('sponsor_filter_id', $validatedData['filter'])->pluck('id')->toArray();
                } else {
                    $sponsorIds = [];
                }

                if (empty($sponsorIds)) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'No sponsors found for the selected filter.');
                }
            }

            $notification->sponsorRecipients()->attach($sponsorIds);
            CacheHelper::forgetSponsorFilterList();
            DB::commit();

            return redirect()->route('notifications.index')->with('message', 'Notification created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sponsor notification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while creating the notification.');
        }
    }

    public function updateSponsorNotification(Request $request, $id){
        $validatedData = $request->validate([
            'notification_title' => 'required|string|max:255',
            'notification_body'  => 'required|string',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'is_general'         => 'nullable',
            'allow_comments'     => 'nullable',
            'filter'             => 'nullable|exists:sponsor_filters,id',
            'attachment'         => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        DB::beginTransaction();
        $currentTerm = TermHelper::getCurrentTerm();

        try {
            $notification = Notification::findOrFail($id);
            $notification->user_id = auth()->user()->id;
            $notification->term_id = $currentTerm->id;
            
            $notification->title          = $validatedData['notification_title'];
            $notification->body           = $validatedData['notification_body'];
            $notification->start_date     = $validatedData['start_date'];
            $notification->end_date       = $validatedData['end_date'];
            $notification->is_general     = isset($validatedData['is_general']) ? 1 : 0;
            $notification->allow_comments = isset($validatedData['allow_comments']) ? 1 : 0;
            
            if (!$notification->is_general && !empty($validatedData['filter'])) {
                $notification->filter_id = $validatedData['filter'];
            } else {
                $notification->filter_id = null;
            }
            
            $notification->save();

            if ($request->hasFile('attachment')) {
                $attachmentFile = $request->file('attachment');
                $originalName   = $attachmentFile->getClientOriginalName();
                $fileName       = pathinfo($originalName, PATHINFO_FILENAME)
                                  . '_' . Str::uuid() 
                                  . '.' . $attachmentFile->getClientOriginalExtension();
                $path           = $attachmentFile->storeAs('attachments', $fileName, 'public');
                $fileType       = $attachmentFile->getClientMimeType();

                $notificationAttachment = $notification->attachments()->first();
                if ($notificationAttachment) {
                    Storage::disk('public')->delete($notificationAttachment->file_path);
                    
                    $notificationAttachment->update([
                        'original_name' => $originalName,
                        'file_path'     => $path,
                        'file_type'     => $fileType,
                    ]);
                } else {
                    NotificationAttachment::create([
                        'notification_id' => $notification->id,
                        'original_name'   => $originalName,
                        'file_path'       => $path,
                        'file_type'       => $fileType,
                    ]);
                }
            }

            if ($notification->is_general) {
                $sponsorIds = Sponsor::pluck('id')->toArray();
            } else {
                if (!empty($validatedData['filter'])) {
                    $sponsorIds = Sponsor::where('sponsor_filter_id', $validatedData['filter'])
                        ->pluck('id')
                        ->toArray();
                } else {
                    $sponsorIds = [];
                }

                if (empty($sponsorIds)) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'No sponsors found for the selected filter.');
                }
            }

            $notification->sponsorRecipients()->sync($sponsorIds);
            CacheHelper::forgetSponsorFilterList();
            DB::commit();

            return redirect()->route('notifications.index')->with('message', 'Notification updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sponsor notification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the notification.');
        }
    }

    public function comment(Request $request, $notificationId){
        $request->validate([
            'body' => 'required|string',
        ]);

        $notification = Notification::findOrFail($notificationId);

        if (!$notification->allow_comments) {
            return redirect()->back()->withErrors(['error' => 'Comments are not allowed for this notification.']);
        }

        NotificationComment::create([
            'notification_id' => $notificationId,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);
        return redirect()->back()->with('message', 'Comment added successfully.');
    }

    public function sendEmail(Request $request)
    {
        $maxSizeInBytes = 10 * 1024 * 1024;

        $validated = $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => ['nullable', 'file', 'max:' . ($maxSizeInBytes / 1024)],
            'receiver_id' => 'required|integer',
            'receiver_type' => 'required|string|in:user,sponsor'
        ]);

        // Log mail configuration for debugging
        Log::info('=== EMAIL SEND ATTEMPT ===');
        Log::info('Mail Configuration:', [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ]);
        Log::info('Recipient: ' . $validated['recipient_email']);
        Log::info('Subject: ' . $validated['subject']);

        $term = TermHelper::getCurrentTerm();
        $termId = $term ? $term->id : null;
        $school_data = SchoolSetup::first();
        $attachmentPath = $attachmentName = $attachmentMime = null;

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            if ($attachment->getSize() > $maxSizeInBytes) {
                return redirect()->back()->with('error', 'Attachment size exceeds 10MB limit.');
            }
            try {
                $attachmentPath = $attachment->store('attachments');
                $attachmentName = $attachment->getClientOriginalName();
                $attachmentMime = $attachment->getMimeType();
                Log::info('Attachment uploaded: ' . $attachmentName);
            } catch (\Exception $e) {
                Log::error('File upload failed: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to upload attachment. Please try again.');
            }
        }

        $defaults = config('notifications.email.defaults', [
            'school_name' => 'Heritage Pro',
            'address' => 'Gaborone, Botswana',
            'support_email' => 'support@heritagepro.co',
            'logo_url' => 'https://heritagepro.s3.us-east-1.amazonaws.com/logo.png'
        ]);

        $details = [
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'schoolName' => $school_data->school_name ?? $defaults['school_name'],
            'address' => $school_data->physical_address ?? $defaults['address'],
            'supportEmail' => $school_data->email_address ?? $defaults['support_email'],
            'heritageLogo' => $defaults['logo_url'],
            'schoolLogo' => $school_data->logo_path ?? null
        ];

        Log::info('Email details prepared:', $details);

        try {
            $email = new BulkEmail($details, $attachmentPath, $attachmentName, $attachmentMime);
            Log::info('BulkEmail object created, attempting to send...');

            Mail::to($validated['recipient_email'])->send($email);

            $status = 'sent';
            Log::info('Email sent successfully to: ' . $validated['recipient_email']);
        } catch (\Exception $e) {
            $status = 'failed';
            Log::error('=== EMAIL SEND FAILED ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }

        $emailData = [
            'term_id' => $termId,
            'sender_id' => auth()->id(),
            'receiver_type' => $request->receiver_type,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'attachment_path' => $attachmentPath,
            'status' => $status,
            'num_of_recipients' => 1,
            'type' => 'Direct',
        ];

        if ($request->receiver_type == 'user') {
            $emailData['user_id'] = $request->receiver_id;
        } else if ($request->receiver_type == 'sponsor') {
            $emailData['sponsor_id'] = $request->receiver_id;
        }

        Email::create($emailData);
        return redirect()->back()->with('message', 'Email sent successfully');
    }


    public function sendBulkEmail(Request $request)
    {

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:50000',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'recipient_type' => 'required|string|in:sponsor,user',
            'grade' => 'nullable|integer',
            'sponsorFilter' => 'nullable|string',
            'department' => 'nullable|string',
            'area_of_work' => 'nullable|string',
            'position' => 'nullable|string',
            'filter' => 'nullable|string'
        ]);

        Log::info('Bulk email request received', ['recipient_type' => $validated['recipient_type']]);
        $term = TermHelper::getCurrentTerm();
        $termId = $term ? $term->id : null;

        $attachmentPath = $attachmentName = $attachmentMime = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $attachmentPath = $attachment->store('attachments');
            $attachmentName = $attachment->getClientOriginalName();
            $attachmentMime = $attachment->getMimeType();

            Log::info('Attachment Details: ', [
                'path' => $attachmentPath,
                'name' => $attachmentName,
                'mime' => $attachmentMime
            ]);
        }

        // Get school data for email template
        $school_data = SchoolSetup::first();
        $defaults = config('notifications.email.defaults', [
            'school_name' => 'Heritage Pro',
            'address' => 'Gaborone, Botswana',
            'support_email' => 'support@heritagepro.co',
            'logo_url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/heritage-pro-logo.jpg',
        ]);

        $details = [
            'subject' => $validated['subject'],
            'body' => $validated['message'],
            'schoolName' => $school_data->school_name ?? ($defaults['school_name'] ?? 'Heritage Pro'),
            'address' => $school_data->physical_address ?? ($defaults['address'] ?? 'Gaborone, Botswana'),
            'supportEmail' => $school_data->email_address ?? ($defaults['support_email'] ?? 'support@heritagepro.co'),
            'heritageLogo' => $defaults['logo_url'] ?? 'https://bw-syllabus.s3.us-east-1.amazonaws.com/heritage-pro-logo.jpg',
            'schoolLogo' => $school_data->logo_path ?? null
        ];

        // Store filters for audit trail
        $filters = array_filter([
            'grade' => $validated['grade'] ?? null,
            'sponsorFilter' => $validated['sponsorFilter'] ?? null,
            'department' => $validated['department'] ?? null,
            'area_of_work' => $validated['area_of_work'] ?? null,
            'position' => $validated['position'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        // Get recipient query (not executed yet)
        $recipientQuery = $this->getRecipientQuery($validated);
        $recipientCount = $recipientQuery->count();

        if ($recipientCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found matching your criteria'
            ], 400);
        }

        // Always queue emails to prevent timeouts - use chunking to prevent memory exhaustion
        $senderId = auth()->id();
        $recipientType = $validated['recipient_type'];

        $recipientQuery->chunk(100, function ($recipients) use ($details, $attachmentPath, $attachmentName, $attachmentMime, $termId, $senderId, $recipientType) {
            foreach ($recipients as $recipient) {
                SendBulkEmailJob::dispatch(
                    $recipient,
                    $details,
                    $attachmentPath,
                    $attachmentName,
                    $attachmentMime,
                    $termId,
                    $senderId
                )->onQueue('emails');
            }
        });

        // Log bulk email summary
        $this->logBulkEmailData($termId, $validated, $recipientCount, $attachmentPath, $filters);

        return response()->json([
            'success' => true,
            'message' => "Bulk emails queued successfully. {$recipientCount} emails will be sent."
        ]);
    }

    /**
     * Get recipient query builder (for chunking - memory efficient)
     */
    protected function getRecipientQuery($validated)
    {
        if ($validated['recipient_type'] === 'sponsor') {
            return $this->getSponsorRecipientsQuery($validated);
        } elseif ($validated['recipient_type'] === 'user') {
            return $this->getUserRecipientsQuery($validated);
        } else {
            throw new \InvalidArgumentException('Invalid recipient type');
        }
    }

    /**
     * Get recipients collection (legacy - for backwards compatibility)
     */
    protected function getRecipients($validated)
    {
        return $this->getRecipientQuery($validated)->get();
    }

    protected function getSponsorRecipientsQuery($validated)
    {
        $query = Sponsor::query();
        if (!empty($validated['grade'])) {
            $query->whereHas('students.currentClassRelation', function ($query) use ($validated) {
                $query->where('klasses.grade_id', $validated['grade']);
            });
        }
        if (!empty($validated['sponsorFilter'])) {
            $query->where('sponsor_filter_id', $validated['sponsorFilter']);
        }
        return $query->whereNotNull('email');
    }

    protected function getUserRecipientsQuery($validated)
    {
        $query = User::query();
        if (!empty($validated['department'])) {
            $query->where('department', $validated['department']);
        }
        if (!empty($validated['area_of_work'])) {
            $query->where('area_of_work', $validated['area_of_work']);
        }
        if (!empty($validated['position'])) {
            $query->where('position', $validated['position']);
        }
        if (!empty($validated['filter'])) {
            $query->where('user_filter_id', $validated['filter']);
        }
        return $query->whereNotNull('email');
    }

    /**
     * @deprecated Use getSponsorRecipientsQuery instead
     */
    protected function getSponsorRecipients($validated)
    {
        return $this->getSponsorRecipientsQuery($validated)->get();
    }

    /**
     * @deprecated Use getUserRecipientsQuery instead
     */
    protected function getUserRecipients($validated)
    {
        return $this->getUserRecipientsQuery($validated)->get();
    }

    protected function sendEmailToRecipient($recipient, $details, $attachmentPath, $attachmentName, $attachmentMime)
    {
        try {
            $email = new BulkEmail($details, $attachmentPath, $attachmentName, $attachmentMime);
            Mail::to($recipient->email)->send($email);
            return 'sent';
        } catch (\Exception $e) {
            Log::error('The email failed: ' . $e->getMessage());
            return 'failed';
        }
    }

    protected function logEmailData($termId, $validated, $recipient, $status, $attachmentPath, $numOfRecipients)
    {
        $emailData = [
            'term_id' => $termId,
            'sender_id' => auth()->id(),
            'receiver_type' => $validated['recipient_type'],
            'subject' => $validated['subject'],
            'body' => $validated['message'],
            'attachment_path' => $attachmentPath,
            'status' => $status,
            'num_of_recipients' => $numOfRecipients,
            'type' => 'Bulk',
        ];

        Log::error('Recipient: ', $recipient->toArray());

        if ($validated['recipient_type'] == 'sponsor') {
            $emailData['sponsor_id'] = $recipient->id;
        } else if ($validated['recipient_type'] == 'user') {
            $emailData['user_id'] = $recipient->id;
        }

        Log::error('Email Data: ', $emailData);
        Log::error('Error fetching ID:' . $recipient->id);
        Email::create($emailData);
    }

    protected function logBulkEmailData($termId, $validated, $numOfRecipients, $attachmentPath, $filters = [])
    {
        $emailData = [
            'term_id' => $termId,
            'sender_id' => auth()->id(),
            'receiver_type' => $validated['recipient_type'],
            'subject' => $validated['subject'],
            'body' => $validated['message'],
            'attachment_path' => $attachmentPath,
            'status' => 'Sent',
            'num_of_recipients' => $numOfRecipients,
            'type' => 'Bulk',
            'filters' => $filters,
        ];
        Email::create($emailData);
    }

    public function sendBulkSmsWithDatabase(Request $request){
        if (!app(CommunicationChannelService::class)->smsEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'SMS is disabled in Communications Setup.',
            ], 403);
        }

        $validated = $this->validateBulkSmsPayload($request);
        $request->merge($validated);

        $message = $request->input('message');
        $recipientType = $request->input('recipientType');
        $recipients = [];

        $jobId = 'sms_job_' . uniqid() . '_' . time();
        
        if ($recipientType === 'sponsors') {
            $grade = $request->input('grade');
            $sponsorFilter = $request->input('sponsorFilter');
            
            $sponsorsQuery = Sponsor::query();
            
            if ($grade) {
                $sponsorsQuery->whereHas('students.currentClassRelation', function ($query) use ($grade) {
                    $query->where('klasses.grade_id', $grade);
                });
            }
            
            if ($sponsorFilter) {
                $sponsorsQuery->where('sponsor_filter_id', $sponsorFilter);
            }
            
            $recipients = $sponsorsQuery->whereNotNull('phone')->with(['students.grade'])->get()->map(function ($sponsor) {
                $student = $sponsor->students->first();
                return [
                    'phone' => $sponsor->phone,
                    'id' => $sponsor->id,
                    'senderType' => 'sponsor',
                    'type' => 'bulk',
                    'name' => $sponsor->name ?? '',
                    'student_name' => $student ? trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? '')) : '',
                    'student_first_name' => $student ? ($student->firstname ?? '') : '',
                    'student_last_name' => $student ? ($student->lastname ?? '') : '',
                    'class_name' => ($student && $student->grade) ? $student->grade->name : '',
                ];
            })->toArray();
            
        } elseif ($recipientType === 'users') {
            $department = $request->input('department');
            $areaOfWork = $request->input('area_of_work');
            $position = $request->input('position');
            $filter = $request->input('filter');
            
            $usersQuery = User::query();
            
            if ($department) {
                $usersQuery->where('department', $department);
            }
            if ($areaOfWork) {
                $usersQuery->where('area_of_work', $areaOfWork);
            }
            if ($position) {
                $usersQuery->where('position', $position);
            }
            if ($filter) {
                $usersQuery->where('user_filter_id', $filter);
            }
            
            $recipients = $usersQuery->whereNotNull('phone')->get()->map(function ($user) {
                return [
                    'phone' => $user->phone,
                    'id' => $user->id,
                    'senderType' => 'user',
                    'type' => 'bulk',
                    'name' => $user->name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')),
                    'first_name' => $user->firstname ?? '',
                    'last_name' => $user->lastname ?? '',
                ];
            })->toArray();
        }
        
        if (empty($recipients)) {
            return response()->json([
                'success' => false, 
                'message' => 'No recipients found with valid phone numbers for the selected criteria.'
            ], 400);
        }
        
        $totalRecipients = count($recipients);
        $smsCount = ceil(strlen($message) / 160);
        $totalSmsUnits = $smsCount * $totalRecipients;
        $costCalculator = app(SmsCostCalculator::class);
        $totalCost = $costCalculator->calculateTotalCost($totalRecipients, $smsCount);

        // Reserve balance before processing to prevent overselling
        $balanceService = app(SmsBalanceService::class);
        if (!$balanceService->reserveBalance($totalCost)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient SMS balance. Required: BWP ' . number_format($totalCost, 2) . '. Please top up your account.'
            ], 400);
        }

        try {
            $jobTracking = SmsJobTracking::create([
                'job_id' => $jobId,
                'user_id' => auth()->id(),
                'term_id' => TermHelper::getCurrentTerm()->id,
                'status' => 'pending',
                'recipient_type' => $recipientType === 'sponsors' ? 'sponsor' : 'user',
                'message' => $message,
                'filters' => $request->only(['grade', 'sponsorFilter', 'department', 'area_of_work', 'position', 'filter']),
                'total_recipients' => $totalRecipients,
                'sms_units_used' => $totalSmsUnits,
                'total_cost' => $totalCost,
                'status_message' => 'Initializing SMS sending...',
                'started_at' => now()
            ]);
            
            Cache::put($jobId, [
                'status' => 'processing',
                'total' => $jobTracking->total_recipients,
                'sent' => 0,
                'failed' => 0,
                'percentage' => 0,
                'message' => 'Initializing SMS sending...',
                'started_at' => now()->toISOString(),
                'db_id' => $jobTracking->id,
                'errors' => []
            ], now()->addHours(2));
            
            $jobTracking->update([
                'status' => 'processing',
                'status_message' => 'Starting to send messages...'
            ]);
            
            $this->sendBulkMessagesWithDatabaseTracking($message, $recipients, $jobId, $jobTracking, 50, $totalCost);
            return response()->json([
                'success' => true,
                'message' => 'Bulk SMS job initiated successfully.',
                'jobId' => $jobId,
                'totalRecipients' => $totalRecipients,
                'estimatedCost' => $totalCost,
                'smsUnits' => $totalSmsUnits
            ]);
            
        } catch (\Exception $e) {
            // Release the reserved balance since the job failed to start
            $balanceService->releaseReservation($totalCost);

            Log::error('Failed to initiate bulk SMS job: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'recipient_type' => $recipientType,
                'total_recipients' => $totalRecipients,
                'error' => $e->getMessage()
            ]);

            if (isset($jobTracking)) {
                $jobTracking->markAsFailed($e->getMessage());
            }

            Cache::put($jobId, [
                'status' => 'failed',
                'total' => $totalRecipients,
                'sent' => 0,
                'failed' => $totalRecipients,
                'percentage' => 0,
                'message' => 'Failed to initiate SMS sending: ' . $e->getMessage(),
                'completed_at' => now()->toISOString(),
                'errors' => [$e->getMessage()]
            ], now()->addHours(2));

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate bulk SMS: ' . $e->getMessage(),
                'jobId' => $jobId
            ], 500);
        }
    }

    public function sendBulkMessage(Request $request)
    {
        $channel = strtolower((string) $request->input('channel'));

        if ($channel === CommunicationChannelService::CHANNEL_SMS) {
            return $this->sendBulkSmsWithDatabase($request);
        }

        if ($channel !== CommunicationChannelService::CHANNEL_WHATSAPP) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid communication channel selected.',
            ], 422);
        }

        if (!app(CommunicationChannelService::class)->whatsappEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp is disabled in Communications Setup.',
            ], 403);
        }

        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:whatsapp'],
            'recipientType' => ['required', 'string', 'in:users'],
            'department' => ['nullable', 'string', 'max:255'],
            'area_of_work' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'integer', 'exists:user_filters,id'],
            'template_id' => ['required', 'integer', 'exists:whatsapp_templates,id'],
            'template_variables' => ['required', 'array'],
        ]);

        $template = WhatsappTemplate::approved()->findOrFail($validated['template_id']);
        $users = $this->buildUserRecipientQueryFromRequest($request)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No staff recipients found for the selected criteria.',
            ], 400);
        }

        $result = app(WhatsAppMessagingService::class)->sendBroadcast(
            $users,
            auth()->user(),
            $template,
            $validated['template_variables']
        );

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp broadcast processed.',
            'summary' => $result,
            'totalRecipients' => $users->count(),
        ]);
    }

    public function sendBulkMessagesWithDatabaseTracking($message, $recipients, $jobId, $jobTracking, $batchSize = 50, $reservedCost = 0){
        $selectedApi = SMSApiSetting::where('key', 'sms_api')->first()?->value ?? 'mascom';
        $recipients_count = count($recipients);
        $batches = array_chunk($recipients, $batchSize);
        $delay = 1;
        $processedCount = 0;
        $failedCount = 0;
        
        Log::info("Starting bulk SMS send for job: {$jobId}", [
            'total_recipients' => $recipients_count,
            'batch_count' => count($batches),
            'api' => $selectedApi
        ]);
        
        foreach ($batches as $batchIndex => $batch) {
            $currentProgress = Cache::get($jobId);
            if ($currentProgress && $currentProgress['status'] === 'cancelled') {
                Log::info("Job {$jobId} was cancelled. Stopping execution.");
                $jobTracking->cancel();
                break;
            }
            
            // Use queue for large recipient counts, send synchronously for small batches
            if ($recipients_count > $batchSize) {
                $messageData = array_map(function ($recipient) use ($message, $recipients_count, $jobId) {
                    return [
                        'messageBody' => $message,
                        'formattedPhoneNumber' => self::verifyAndFormatPhoneNumber($recipient['phone']),
                        'senderId' => $recipient['id'],
                        'senderType' => $recipient['senderType'],
                        'type' => $recipient['type'],
                        'num_recipients' => $recipients_count,
                        'jobId' => $jobId,
                        // Include recipient data for placeholder replacement
                        'recipientContext' => [
                            'sponsor_name' => $recipient['name'] ?? '',
                            'student_name' => $recipient['student_name'] ?? '',
                            'student_first_name' => $recipient['student_first_name'] ?? $recipient['first_name'] ?? '',
                            'student_last_name' => $recipient['student_last_name'] ?? $recipient['last_name'] ?? '',
                            'class_name' => $recipient['class_name'] ?? '',
                        ],
                    ];
                }, $batch);

                if ($selectedApi === 'mascom') {
                    // Calculate reserved cost for this batch (proportional to batch size)
                    $batchReservedCost = $reservedCost > 0
                        ? ($reservedCost / $recipients_count) * count($batch)
                        : 0;

                    SendBulkWithLinkSMS::dispatch($messageData, $jobId, $batchReservedCost)
                        ->delay(now()->addSeconds($delay * $batchIndex));
                }

                $processedCount += count($batch);
            } else {
                $placeholderService = app(SmsPlaceholderService::class);

                foreach ($batch as $recipient) {
                    try {
                        // Build context for placeholder replacement
                        $context = $this->buildPlaceholderContext($recipient);
                        $personalizedMessage = $placeholderService->replacePlaceholders($message, $context);

                        self::sendMessage(
                            $personalizedMessage,
                            $recipient['phone'],
                            $recipient['id'],
                            $recipient['senderType'],
                            $recipient['type'],
                            $recipients_count,
                            $selectedApi
                        );
                        $processedCount++;

                        Log::info("SMS sent successfully", [
                            'job_id' => $jobId,
                            'recipient' => $recipient['phone'],
                            'processed' => $processedCount
                        ]);
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        
                        Log::error('Failed to send SMS', [
                            'job_id' => $jobId,
                            'recipient' => $recipient['phone'],
                            'error' => $e->getMessage()
                        ]);
                        
                        $currentProgress = Cache::get($jobId);
                        if ($currentProgress) {
                            $errors = $currentProgress['errors'] ?? [];
                            $errors[] = "Failed to send to {$recipient['phone']}: " . $e->getMessage();
                            $currentProgress['errors'] = array_slice($errors, -10);
                            Cache::put($jobId, $currentProgress, now()->addHours(2));
                        }
                    }
                    
                    if ($processedCount % 5 === 0 || ($processedCount + $failedCount) === $recipients_count) {
                        $this->updateJobProgressWithDatabase($jobId, $processedCount, $failedCount, $recipients_count, $jobTracking);
                    }
                }
            }
            
            if ($batchIndex < count($batches) - 1) {
                sleep($delay);
            }
        }
        
        $this->updateJobProgressWithDatabase($jobId, $processedCount, $failedCount, $recipients_count, $jobTracking, true);

        // Release the balance reservation for synchronous sends
        // (Actual deductions already happened per-message via SMSHelper)
        if ($reservedCost > 0) {
            try {
                app(SmsBalanceService::class)->releaseReservation($reservedCost);
                Log::info("Balance reservation released for completed job {$jobId}", [
                    'amount' => $reservedCost
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to release balance reservation for job {$jobId}", [
                    'amount' => $reservedCost,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Bulk SMS job completed", [
            'job_id' => $jobId,
            'sent' => $processedCount,
            'failed' => $failedCount,
            'total' => $recipients_count
        ]);
    }

    private function updateJobProgressWithDatabase($jobId, $sent, $failed, $total, $jobTracking, $completed = false){
        $percentage = $total > 0 ? round((($sent + $failed) / $total) * 100) : 0;
        $status = $completed ? 'completed' : 'processing';
        
        $cacheData = Cache::get($jobId) ?? [];
        $progressData = array_merge($cacheData, [
            'status' => $status,
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'percentage' => $percentage,
            'message' => $status === 'completed' 
                ? "SMS sending completed. Sent: {$sent}, Failed: {$failed}" 
                : "Processing... {$sent}/{$total} sent",
            'updated_at' => now()->toISOString()
        ]);
        
        if ($completed) {
            $progressData['completed_at'] = now()->toISOString();
        }
        
        Cache::put($jobId, $progressData, now()->addHours(2));
        if ($jobTracking) {
            $jobTracking->sent_count = $sent;
            $jobTracking->failed_count = $failed;
            $jobTracking->percentage = $percentage;
            $jobTracking->status = $status;
            $jobTracking->status_message = $progressData['message'];
            
            if ($completed) {
                $jobTracking->completed_at = now();
                $smsCount = ceil(strlen($jobTracking->message) / 160);
                $jobTracking->sms_units_used = $smsCount * $sent;
                $jobTracking->total_cost = $jobTracking->sms_units_used * app(SmsCostCalculator::class)->getCostPerUnit();
            }
            
            $jobTracking->save();
        }
    }

    public function checkEmailRecipients(Request $request){
        $recipientType = $request->input('recipient_type');
        $count = 0;

        if ($recipientType === 'sponsor') {
            $grade = $request->input('grade');
            $sponsorFilter = $request->input('sponsorFilter');

            $sponsorsQuery = Sponsor::query();

            if ($grade) {
                $sponsorsQuery->whereHas('students.currentClassRelation', function ($query) use ($grade) {
                    $query->where('klasses.grade_id', $grade);
                });
            }

            if ($sponsorFilter) {
                $sponsorsQuery->where('sponsor_filter_id', $sponsorFilter);
            }

            $count = $sponsorsQuery->whereNotNull('email')->count();
            Log::info($count);
        } elseif ($recipientType === 'user') {
            $department = $request->input('department');
            $areaOfWork = $request->input('area_of_work');
            $position = $request->input('position');
            $filter = $request->input('filter');

            $usersQuery = User::query();
            if ($department) {
                $usersQuery->where('department', $department);
            }
            if ($areaOfWork) {
                $usersQuery->where('area_of_work', $areaOfWork);
            }
            if ($position) {
                $usersQuery->where('position', $position);
            }
            if ($filter) {
                $usersQuery->where('user_filter_id', $filter);
            }
            $count = $usersQuery->whereNotNull('email')->count();
            Log::info($count);
        }

        return response()->json(['success' => true, 'count' => $count]);
    }


    public function checkSMSRecipients(Request $request){
        $recipientType = $request->input('recipientType');
        $count = 0;

        if ($recipientType === 'sponsors') {
            $count = $this->getSponsorCount(
                $request->input('grade'),
                $request->input('sponsorFilter')
            );
        } elseif ($recipientType === 'users') {
            $count = $this->getUserCount(
                $request->input('department'),
                $request->input('areaOfWork'),
                $request->input('position'),
                $request->input('filter')
            );
        }

        // Get the actual SMS cost per unit from the system (account_balances table)
        $costPerUnit = $this->getActualSMSCostPerUnit();
        
        return response()->json([
            'success' => true, 
            'count' => $count,
            'costPerUnit' => $costPerUnit
        ]);
    }

    public function checkWhatsAppRecipients(Request $request)
    {
        $query = $this->buildUserRecipientQueryFromRequest($request);
        $totalFiltered = (clone $query)->count();
        $eligible = (clone $query)
            ->whereNotNull('phone')
            ->whereHas('channelConsents', function ($consentQuery) {
                $consentQuery->where('channel', CommunicationChannelService::CHANNEL_WHATSAPP)
                    ->where('status', 'opted_in');
            })
            ->count();

        return response()->json([
            'success' => true,
            'count' => $eligible,
            'eligible' => $eligible,
            'skipped' => max($totalFiltered - $eligible, 0),
        ]);
    }

    /**
     * Get the actual SMS cost per unit from the account_balances table
     * This uses the real package rates from the system, not hardcoded values
     */
    private function getActualSMSCostPerUnit()
    {
        try {
            $accountBalance = \App\Models\AccountBalance::first();
            if (!$accountBalance) {
                // Fallback to default Basic rate if no account balance found
                return 0.35;
            }
            
            $packageType = $accountBalance->sms_credits_package ?? 'Basic';
            return \App\Helpers\SMSHelper::getPackageRate($packageType);
            
        } catch (\Exception $e) {
            \Log::error('Error getting SMS cost per unit: ' . $e->getMessage());
            // Fallback to default Basic rate if there's an error
            return 0.35;
        }
    }

    private function getSponsorCount($grade, $sponsorFilter)
    {
        $query = Sponsor::query()->whereNotNull('phone');

        if ($grade) {
            $query->whereHas('students.currentClassRelation', function ($q) use ($grade) {
                $q->where('klasses.grade_id', $grade);
            });
        }

        if ($sponsorFilter) {
            $query->where('sponsor_filter_id', $sponsorFilter);
        }

        $count = $query->count();
        Log::info('Sponsors count:', ['count' => $count, 'query' => $query->toSql()]);

        return $count;
    }

    private function getUserCount($department, $areaOfWork, $position, $filter)
    {
        $query = User::query()->whereNotNull('phone');

        if ($department) {
            $query->where('department', $department);
        }

        if ($areaOfWork) {
            $query->where('area_of_work', $areaOfWork);
        }

        if ($position) {
            $query->where('position', $position);
        }

        if ($filter) {
            $query->where('user_filter_id', $filter);
        }

        $count = $query->count();
        Log::info('Users count:', ['count' => $count, 'query' => $query->toSql()]);

        return $count;
    }

    public function sendBulkMessagesWithTracking($message, $recipients, $jobId, $batchSize = 50){
        $selectedApi = SMSApiSetting::where('key', 'sms_api')->first()?->value ?? 'mascom';
        $recipients_count = count($recipients);
        $batches = array_chunk($recipients, $batchSize);
        $delay = 1;
        $processedCount = 0;
        $failedCount = 0;
        
        foreach ($batches as $batchIndex => $batch) {
            if (count($batch) > $batchSize) {
                $messageData = array_map(function ($recipient) use ($message, $recipients_count, $jobId) {
                    return [
                        'messageBody' => $message,
                        'formattedPhoneNumber' => self::verifyAndFormatPhoneNumber($recipient['phone']),
                        'senderId' => $recipient['id'],
                        'senderType' => $recipient['senderType'],
                        'type' => $recipient['type'],
                        'num_recipients' => $recipients_count,
                        'jobId' => $jobId 
                    ];
                }, $batch);
                
                if ($selectedApi === 'mascom') {
                    SendBulkWithLinkSMS::dispatch($messageData, $jobId)->delay(now()->addSeconds($delay));
                }
            } else {
                foreach ($batch as $recipient) {
                    try {
                        self::sendMessage(
                            $message,
                            $recipient['phone'],
                            $recipient['id'],
                            $recipient['senderType'],
                            $recipient['type'],
                            $recipients_count,
                            $selectedApi
                        );
                        $processedCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error('Failed to send SMS to ' . $recipient['phone'] . ': ' . $e->getMessage());
                    }
                    
                    $this->updateJobProgress($jobId, $processedCount, $failedCount, $recipients_count);
                }
            }
            
            sleep($delay);
        }
    }

    private function updateJobProgress($jobId, $sent, $failed, $total){
        $percentage = $total > 0 ? round((($sent + $failed) / $total) * 100) : 0;
        $status = ($sent + $failed) >= $total ? 'completed' : 'processing';
        
        $progressData = [
            'status' => $status,
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'percentage' => $percentage,
            'message' => $status === 'completed' 
                ? "SMS sending completed. Sent: {$sent}, Failed: {$failed}" 
                : "Processing... {$sent}/{$total} sent",
            'updated_at' => now()->toISOString()
        ];
        
        if ($status === 'completed') {
            $progressData['completed_at'] = now()->toISOString();
        }
        
        Cache::put($jobId, $progressData, now()->addHours(2));
    }

    public function getJobProgress($jobId){
        $progress = Cache::get($jobId);
        
        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or expired',
                'status' => 'not_found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    public function cancelJob($jobId){
        $jobService = app(SmsJobService::class);
        $result = $jobService->cancelJob($jobId, auth()->user());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found, unauthorized, or already completed'
            ], 403);
        }

        Log::info("SMS job cancelled", [
            'job_id' => $jobId,
            'user_id' => auth()->id(),
            'cancelled_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job cancellation requested',
            'jobId' => $jobId
        ]);
    }

    public function getJobProgressFromDatabase($jobId){
        $jobService = app(SmsJobService::class);
        $progress = $jobService->getJobProgress($jobId, auth()->user());

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or unauthorized',
                'status' => 'not_found'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    public function getSmsJobHistory(Request $request){
        $userId = auth()->id();
        $query = SmsJobTracking::forUser($userId);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('recipient_type')) {
            $query->where('recipient_type', $request->recipient_type);
        }

        $jobs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate summary statistics
        $summary = [
            'total_jobs' => SmsJobTracking::forUser($userId)->count(),
            'total_sms_sent' => SmsJobTracking::forUser($userId)->sum('sent_count'),
            'total_sms_failed' => SmsJobTracking::forUser($userId)->sum('failed_count'),
            'total_cost' => SmsJobTracking::forUser($userId)->sum('total_cost'),
            'completed_jobs' => SmsJobTracking::forUser($userId)->where('status', 'completed')->count(),
            'failed_jobs' => SmsJobTracking::forUser($userId)->where('status', 'failed')->count(),
            'processing_jobs' => SmsJobTracking::forUser($userId)->where('status', 'processing')->count(),
        ];

        // Calculate success rate
        $totalAttempted = $summary['total_sms_sent'] + $summary['total_sms_failed'];
        $summary['success_rate'] = $totalAttempted > 0
            ? round(($summary['total_sms_sent'] / $totalAttempted) * 100, 1)
            : 0;

        return view('communications.sms-job-history', compact('jobs', 'summary'));
    }

    public static function sendMessage($message, $phoneNumber, $senderId, $senderType = 'user', $type = 'direct', $num_recipients = 1, $apiToUse = 'mascom'){
        if (!app(CommunicationChannelService::class)->smsEnabled()) {
            throw new \RuntimeException('SMS is disabled in Communications Setup.');
        }

        $formattedPhoneNumber = self::verifyAndFormatPhoneNumber($phoneNumber);
        Log::info('Formatted phone number:', ['formattedPhoneNumber' => $formattedPhoneNumber]);
        if ($apiToUse === 'mascom') {
            $linkSMS = new \App\Helpers\LinkSMSHelper();
            $linkSMS->sendMessage($message, $formattedPhoneNumber, $senderId, $senderType, $type, $num_recipients);
        }
    }

    public static function getSignature($user, $senderType){
        $defaultSignature = config('notifications.email.defaults.sms_signature', ' :From Heritage Pro EMS');
        $schoolSignature = SchoolSetup::first()->school_sms_signature ?? $defaultSignature;
        if ($senderType === 'user' && $user->position === 'School Head' && !empty($user->sms_signature)) {
            return $user->sms_signature;
        } else {
            return $schoolSignature;
        }
    }

    public static function verifyAndFormatPhoneNumber($phoneNumber){
        if (preg_match('/^7\d{7}$/', $phoneNumber)) {
            return '+267' . $phoneNumber;
        } elseif (preg_match('/^002677\d{7}$/', $phoneNumber)) {
            return '+267' . substr($phoneNumber, 5);
        }
        return $phoneNumber;
    }

    public function saveSmsApi(Request $request){
        $validator = Validator::make($request->all(), [
            'sms_api' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $api = $request->input('sms_api');
        try {
            SMSApiSetting::updateOrCreate(
                ['key' => 'sms_api'],
                [
                    'value' => $api,
                    'category' => 'api',
                    'type' => 'string',
                    'display_name' => 'SMS API Provider',
                    'description' => 'Selected SMS API provider',
                    'is_editable' => true,
                    'display_order' => 1
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving SMS API preference: ' . $e->getMessage());

            return redirect()->back()->withErrors('An error occurred while saving the SMS API preference.');
        }

        return redirect()->back()->with('message', 'SMS API preference saved successfully.');
    }

    /**
     * Build context array for placeholder replacement from recipient data
     */
    private function buildPlaceholderContext(array $recipient): array
    {
        $context = [];

        // Handle sponsor recipients
        if (($recipient['senderType'] ?? '') === 'sponsor') {
            $context['sponsor_name'] = $recipient['name'] ?? '';
            $context['student_name'] = $recipient['student_name'] ?? '';
            $context['student_first_name'] = $recipient['student_first_name'] ?? '';
            $context['student_last_name'] = $recipient['student_last_name'] ?? '';
            $context['class_name'] = $recipient['class_name'] ?? '';
        }
        // Handle user recipients
        elseif (($recipient['senderType'] ?? '') === 'user') {
            $context['sponsor_name'] = $recipient['name'] ?? '';
            $context['student_first_name'] = $recipient['first_name'] ?? '';
            $context['student_last_name'] = $recipient['last_name'] ?? '';
        }

        return $context;
    }

    protected function validateBulkSmsPayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'min:1', 'max:480'],
            'recipientType' => ['required', 'string', 'in:sponsors,users'],
            'grade' => ['nullable', 'integer', 'exists:grades,id'],
            'sponsorFilter' => ['nullable', 'integer', 'exists:sponsor_filters,id'],
            'department' => ['nullable', 'string', 'max:255'],
            'area_of_work' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'integer', 'exists:user_filters,id'],
        ], [
            'message.required' => 'Please enter your SMS message.',
            'message.max' => 'The SMS message must not exceed 480 characters (3 SMS units).',
            'recipientType.required' => 'Please select a recipient type.',
            'recipientType.in' => 'Invalid recipient type. Must be either sponsors or users.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $accountBalance = AccountBalance::first();
            if (!$accountBalance) {
                $validator->errors()->add('balance', 'No SMS package configured. Please set up an SMS package first.');
                return;
            }

            $availableBalance = $accountBalance->balance_bwp - ($accountBalance->pending_amount ?? 0);
            if ($availableBalance <= 0) {
                $validator->errors()->add('balance', 'Insufficient SMS balance. Please top up your account.');
                return;
            }

            $costCalculator = app(SmsCostCalculator::class);
            $costPerUnit = $costCalculator->getCostPerUnit();
            $smsUnits = $costCalculator->calculateSmsUnits($request->input('message', ''));
            $minCost = $smsUnits * $costPerUnit;

            if ($availableBalance < $minCost) {
                $validator->errors()->add(
                    'balance',
                    "Insufficient balance. This message requires at least BWP " . number_format($minCost, 2) .
                    " per recipient, but you only have BWP " . number_format($availableBalance, 2) . " available."
                );
            }
        });

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    protected function buildUserRecipientQueryFromRequest(Request $request)
    {
        $usersQuery = User::query();

        if ($request->filled('department')) {
            $usersQuery->where('department', $request->input('department'));
        }

        if ($request->filled('area_of_work')) {
            $usersQuery->where('area_of_work', $request->input('area_of_work'));
        }

        if ($request->filled('position')) {
            $usersQuery->where('position', $request->input('position'));
        }

        if ($request->filled('filter')) {
            $usersQuery->where('user_filter_id', $request->input('filter'));
        }

        return $usersQuery;
    }
}
