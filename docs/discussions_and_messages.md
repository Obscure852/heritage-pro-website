# Discussions & Direct Messaging Feature

## Overview

This feature adds course discussions and direct messaging capabilities to the LMS student portal, enabling:
- **Direct Messaging**: 1-on-1 private messages between students and teachers
- **Course Discussions**: Forum-based discussions for each course
- **Content-Specific Discussions**: "Discuss" buttons on quizzes/assignments for targeted Q&A
- **Teacher Thread Creation**: Teachers can create announcements and Q&A sessions

---

## Database Schema

### New Tables

#### lms_conversations
Stores 1-on-1 conversations between students and instructors.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| student_id | bigint | FK to students |
| instructor_id | bigint | FK to users (teacher) |
| course_id | bigint | FK to lms_courses (optional context) |
| subject | varchar(255) | Optional conversation subject |
| last_message_at | timestamp | Time of last message |
| student_read_at | timestamp | When student last read |
| instructor_read_at | timestamp | When instructor last read |
| is_archived_by_student | boolean | Student archived flag |
| is_archived_by_instructor | boolean | Instructor archived flag |

**Unique constraint:** (student_id, instructor_id, course_id)

#### lms_direct_messages
Individual messages within conversations.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| conversation_id | bigint | FK to lms_conversations |
| sender_type | varchar(255) | Polymorphic type (Student or User) |
| sender_id | bigint | Polymorphic ID |
| body | text | Message content |
| read_at | timestamp | When recipient read message |
| created_at | timestamp | Send time |
| deleted_at | timestamp | Soft delete |

#### lms_message_attachments
File attachments for messages.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| message_id | bigint | FK to lms_direct_messages |
| filename | varchar(255) | Stored filename |
| original_filename | varchar(255) | Original upload name |
| file_path | varchar(255) | Storage path |
| mime_type | varchar(100) | File MIME type |
| file_size | integer | Size in bytes |

---

## Existing Discussion Tables (Already in Database)

The following tables already exist and will be utilized:

- `lms_discussion_forums` - One forum per course
- `lms_discussion_categories` - Categories within forums
- `lms_discussion_threads` - Discussion topics (has `content_item_id` for content linking)
- `lms_discussion_posts` - Replies with threading
- `lms_discussion_likes` - Likes on threads/posts
- `lms_discussion_subscriptions` - Thread subscriptions
- `lms_discussion_attachments` - File attachments
- `lms_discussion_mentions` - @mentions

---

## Models

### New Models

#### Conversation (`app/Models/Lms/Conversation.php`)
```php
class Conversation extends Model {
    protected $table = 'lms_conversations';

    // Relationships
    public function student(): BelongsTo;
    public function instructor(): BelongsTo;
    public function course(): BelongsTo;
    public function messages(): HasMany;
    public function latestMessage(): HasOne;

    // Methods
    public function markAsReadByStudent(): void;
    public function markAsReadByInstructor(): void;
    public function hasUnreadForStudent(): bool;
    public function hasUnreadForInstructor(): bool;
}
```

#### DirectMessage (`app/Models/Lms/DirectMessage.php`)
```php
class DirectMessage extends Model {
    use SoftDeletes;
    protected $table = 'lms_direct_messages';

    // Relationships
    public function conversation(): BelongsTo;
    public function sender(): MorphTo;
    public function attachments(): HasMany;

    // Methods
    public function markAsRead(): void;
}
```

#### MessageAttachment (`app/Models/Lms/MessageAttachment.php`)
```php
class MessageAttachment extends Model {
    protected $table = 'lms_message_attachments';

    // Relationships
    public function message(): BelongsTo;

    // Accessors
    public function getUrlAttribute(): string;
    public function getFormattedSizeAttribute(): string;
}
```

---

## Controllers

### StudentMessagingController
Location: `app/Http/Controllers/StudentPortal/StudentMessagingController.php`

| Method | Route | Description |
|--------|-------|-------------|
| inbox() | GET /student/lms/messages | List conversations |
| compose() | GET /student/lms/messages/compose | New message form |
| send() | POST /student/lms/messages | Send new message |
| conversation($id) | GET /student/lms/messages/{id} | View conversation |
| reply($id) | POST /student/lms/messages/{id}/reply | Reply to conversation |
| archive($id) | POST /student/lms/messages/{id}/archive | Archive conversation |
| unreadCount() | GET /student/lms/messages/unread-count | AJAX unread count |

### StudentDiscussionController
Location: `app/Http/Controllers/StudentPortal/StudentDiscussionController.php`

| Method | Route | Description |
|--------|-------|-------------|
| forum($course) | GET /student/lms/courses/{course}/discussions | Course forum |
| thread($thread) | GET /student/lms/discussions/threads/{thread} | View thread |
| createThread($course) | GET /student/lms/courses/{course}/discussions/create | New thread form |
| storeThread($course) | POST /student/lms/courses/{course}/discussions | Create thread |
| storePost($thread) | POST /student/lms/discussions/threads/{thread}/posts | Post reply |
| contentDiscussions($item) | GET /student/lms/content/{item}/discussions | Content threads |
| storeContentThread($item) | POST /student/lms/content/{item}/discussions | Create content thread |
| toggleLike() | POST /student/lms/discussions/like | Like/unlike |
| toggleSubscription($thread) | POST /student/lms/discussions/threads/{thread}/subscribe | Subscribe/unsubscribe |

### TeacherMessagingController
Location: `app/Http/Controllers/Lms/TeacherMessagingController.php`

| Method | Route | Description |
|--------|-------|-------------|
| inbox() | GET /lms/messaging | Teacher inbox |
| conversation($id) | GET /lms/messaging/{id} | View conversation |
| reply($id) | POST /lms/messaging/{id}/reply | Reply to student |
| archive($id) | POST /lms/messaging/{id}/archive | Archive conversation |

---

## Views

### Student Portal Views

```
resources/views/students/portal/lms/
├── messaging/
│   ├── inbox.blade.php          # Messages inbox with gradient header
│   ├── conversation.blade.php   # Single conversation thread
│   └── compose.blade.php        # Compose new message
└── discussions/
    ├── index.blade.php          # Course forum listing
    ├── thread.blade.php         # Thread with replies
    └── create-thread.blade.php  # New thread form
```

### Teacher/LMS Views

```
resources/views/lms/
└── messaging/
    ├── inbox.blade.php          # Teacher inbox
    └── conversation.blade.php   # Conversation view
```

---

## Routes

### Student Routes (`routes/student-auth/student.php`)

```php
// Direct Messaging
Route::prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [StudentMessagingController::class, 'inbox'])->name('inbox');
    Route::get('/compose', [StudentMessagingController::class, 'compose'])->name('compose');
    Route::post('/', [StudentMessagingController::class, 'send'])->name('send');
    Route::get('/{conversation}', [StudentMessagingController::class, 'conversation'])->name('conversation');
    Route::post('/{conversation}/reply', [StudentMessagingController::class, 'reply'])->name('reply');
    Route::post('/{conversation}/archive', [StudentMessagingController::class, 'archive'])->name('archive');
    Route::get('/unread-count', [StudentMessagingController::class, 'unreadCount'])->name('unread');
});

// Course Discussions
Route::get('/courses/{course}/discussions', [StudentDiscussionController::class, 'forum'])->name('discussions.forum');
Route::get('/courses/{course}/discussions/create', [StudentDiscussionController::class, 'createThread'])->name('discussions.create');
Route::post('/courses/{course}/discussions', [StudentDiscussionController::class, 'storeThread'])->name('discussions.store');
Route::get('/discussions/threads/{thread}', [StudentDiscussionController::class, 'thread'])->name('discussions.thread');
Route::post('/discussions/threads/{thread}/posts', [StudentDiscussionController::class, 'storePost'])->name('discussions.post');
Route::post('/discussions/like', [StudentDiscussionController::class, 'toggleLike'])->name('discussions.like');
Route::post('/discussions/threads/{thread}/subscribe', [StudentDiscussionController::class, 'toggleSubscription'])->name('discussions.subscribe');

// Content-Specific Discussions
Route::get('/content/{contentItem}/discussions', [StudentDiscussionController::class, 'contentDiscussions'])->name('discussions.content');
Route::post('/content/{contentItem}/discussions', [StudentDiscussionController::class, 'storeContentThread'])->name('discussions.content.store');
```

### Teacher Routes (`routes/lms/lms.php`)

```php
// Teacher Messaging
Route::prefix('messaging')->name('messaging.')->group(function () {
    Route::get('/', [TeacherMessagingController::class, 'inbox'])->name('inbox');
    Route::get('/{conversation}', [TeacherMessagingController::class, 'conversation'])->name('conversation');
    Route::post('/{conversation}/reply', [TeacherMessagingController::class, 'reply'])->name('reply');
    Route::post('/{conversation}/archive', [TeacherMessagingController::class, 'archive'])->name('archive');
});
```

---

## UI Theming

All views follow the project's established theming patterns:

### Page Header (Gradient)
```css
.page-header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}
```

### Help Text
```css
.help-text {
    background: #f8f9fa;
    padding: 12px;
    border-left: 4px solid #3b82f6;
    border-radius: 0 3px 3px 0;
    margin-bottom: 20px;
}
```

### Primary Buttons
```css
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    /* ... with hover effects and loading states */
}
```

---

## Notifications

The feature integrates with the existing notification system (`lms_notifications` table):

### New Notification Types
- `new_message` - When student/teacher receives a new direct message
- `content_discussion` - When someone posts in a content-specific discussion

### Notification Triggers
1. New direct message → Notify recipient
2. Reply to subscribed thread → Notify subscribers
3. @mention in discussion → Notify mentioned student
4. Content discussion activity → Notify course instructor

---

## Progress Tracking

Progress is tracked in `/docs/progress.txt` with the following phases:
1. Database & Models
2. Student Portal - Direct Messaging
3. Student Portal - Discussion Integration
4. Content-Specific Discussions
5. Teacher Messaging
6. Teacher Discussion Features
