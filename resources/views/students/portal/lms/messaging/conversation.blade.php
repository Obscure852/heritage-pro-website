@extends('layouts.master-student-portal')

@section('title')
    {{ $conversation->instructor->full_name ?? 'Conversation' }}
@endsection

@section('css')
    <style>
        .chat-wrapper {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 200px);
            min-height: 500px;
        }

        .chat-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            padding: 16px 24px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .chat-header .back-btn {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: background 0.2s;
        }

        .chat-header .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .chat-user-info h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .chat-user-info small {
            opacity: 0.85;
            font-size: 12px;
        }

        .chat-header-actions .btn-action {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .chat-header-actions .btn-action:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: #f8f9fa;
        }

        .date-divider {
            text-align: center;
            margin: 20px 0;
        }

        .date-divider span {
            background: #e5e7eb;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .message-row {
            display: flex;
            margin-bottom: 16px;
        }

        .message-row.sent {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #6366f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
            flex-shrink: 0;
        }

        .message-row.sent .message-avatar {
            background: #4e73df;
        }

        .message-content {
            max-width: 70%;
            margin: 0 10px;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            background: white;
            color: #1f2937;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .message-row.sent .message-bubble {
            background: #4e73df;
            color: white;
        }

        .message-row:not(.sent) .message-bubble {
            border-bottom-left-radius: 4px;
        }

        .message-row.sent .message-bubble {
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
            padding: 0 4px;
        }

        .message-row.sent .message-time {
            text-align: right;
        }

        .message-attachments {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 12px;
            text-decoration: none;
            color: inherit;
            transition: background 0.2s;
        }

        .message-row:not(.sent) .attachment-link {
            background: #f3f4f6;
            color: #374151;
        }

        .attachment-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .message-row:not(.sent) .attachment-link:hover {
            background: #e5e7eb;
        }

        .chat-input-area {
            padding: 16px 24px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .chat-input-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .input-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .message-input-wrapper {
            flex: 1;
            position: relative;
        }

        .message-input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            font-size: 14px;
            resize: none;
            min-height: 48px;
            max-height: 120px;
            line-height: 1.4;
            transition: border-color 0.2s;
        }

        .message-input:focus {
            outline: none;
            border-color: #4e73df;
        }

        .input-actions {
            display: flex;
            gap: 8px;
        }

        .btn-attach {
            width: 48px;
            height: 48px;
            background: #f3f4f6;
            border: none;
            border-radius: 50%;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-attach:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-send {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-send:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }

        .btn-send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .file-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .file-preview-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #eff6ff;
            border-radius: 8px;
            font-size: 13px;
            color: #4e73df;
        }

        .file-preview-item .remove-file {
            cursor: pointer;
            color: #ef4444;
            margin-left: 4px;
        }

        @media (max-width: 768px) {
            .message-content {
                max-width: 85%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('student.lms.messages.inbox') }}">Messages</a>
        @endslot
        @slot('title')
            Conversation
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="chat-wrapper">
        <div class="chat-header">
            <div class="chat-header-left">
                <a href="{{ route('student.lms.messages.inbox') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="chat-user-avatar">
                    {{ strtoupper(substr($conversation->instructor->firstname ?? 'T', 0, 1)) }}
                </div>
                <div class="chat-user-info">
                    <h5>{{ $conversation->instructor->full_name ?? 'Unknown Teacher' }}</h5>
                    <small>{{ $conversation->course->title ?? $conversation->subject ?? 'General' }}</small>
                </div>
            </div>
            <div class="chat-header-actions">
                <form action="{{ route('student.lms.messages.archive', $conversation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-action" title="Archive">
                        <i class="fas fa-archive"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            @php $lastDate = null; @endphp
            @foreach ($messages as $message)
                @php
                    $messageDate = $message->created_at->format('Y-m-d');
                    $isNewDate = $lastDate !== $messageDate;
                    $lastDate = $messageDate;
                    $isSent = $message->isSentByStudent();
                @endphp

                @if ($isNewDate)
                    <div class="date-divider">
                        <span>{{ $message->created_at->format('M j, Y') }}</span>
                    </div>
                @endif

                <div class="message-row {{ $isSent ? 'sent' : '' }}">
                    <div class="message-avatar">
                        @if ($isSent)
                            {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                        @else
                            {{ strtoupper(substr($conversation->instructor->firstname ?? 'T', 0, 1)) }}
                        @endif
                    </div>
                    <div class="message-content">
                        <div class="message-bubble">
                            {!! nl2br(e($message->body)) !!}

                            @if ($message->attachments->count() > 0)
                                <div class="message-attachments">
                                    @foreach ($message->attachments as $attachment)
                                        <a href="{{ $attachment->url }}" target="_blank" class="attachment-link">
                                            <i class="fas fa-paperclip"></i>
                                            {{ Str::limit($attachment->original_filename, 18) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="message-time">
                            {{ $message->created_at->format('g:i A') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="chat-input-area">
            <form action="{{ route('student.lms.messages.reply', $conversation) }}" method="POST"
                  enctype="multipart/form-data" id="replyForm" class="chat-input-form">
                @csrf
                <div class="file-preview" id="filePreview"></div>
                <div class="input-row">
                    <div class="message-input-wrapper">
                        <textarea name="body" class="message-input" placeholder="Type a message..."
                                  required minlength="2" maxlength="10000" rows="1"></textarea>
                    </div>
                    <div class="input-actions">
                        <input type="file" name="attachments[]" id="attachmentInput" multiple hidden
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip">
                        <button type="button" class="btn-attach" onclick="document.getElementById('attachmentInput').click()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button type="submit" class="btn-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Scroll to bottom of messages
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Auto-resize textarea
        const textarea = document.querySelector('.message-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // File attachment preview
        const attachmentInput = document.getElementById('attachmentInput');
        const filePreview = document.getElementById('filePreview');

        attachmentInput.addEventListener('change', function() {
            filePreview.innerHTML = '';
            const files = Array.from(this.files);

            files.forEach(file => {
                const item = document.createElement('div');
                item.className = 'file-preview-item';
                item.innerHTML = `
                    <i class="fas fa-file"></i>
                    ${file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name}
                `;
                filePreview.appendChild(item);
            });
        });

        // Form submit loading state
        document.getElementById('replyForm').addEventListener('submit', function() {
            const btn = this.querySelector('.btn-send');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        });

        // Submit on Enter (but Shift+Enter for new line)
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length >= 2) {
                    document.getElementById('replyForm').submit();
                }
            }
        });
    </script>
@endsection
