<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} - Heritage Junior Secondary School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            background: #f3f4f6;
        }

        /* Navbar */
        .public-navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 12px 0;
        }
        .public-navbar .brand {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .public-navbar .brand i {
            color: #4e73df;
            font-size: 22px;
        }
        .public-navbar .label {
            font-size: 13px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 500;
        }

        /* Header */
        .doc-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px 0;
        }
        .doc-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .doc-header .meta {
            font-size: 14px;
            opacity: 0.85;
        }

        /* Content */
        .doc-content {
            max-width: 900px;
            margin: 28px auto;
            padding: 0 16px;
        }

        .doc-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            padding: 28px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-item label {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .info-item span {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }
        .info-item .category-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Description */
        .doc-description {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        /* Preview Area */
        .preview-area {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .preview-area iframe {
            width: 100%;
            height: 600px;
            border: none;
            display: block;
        }
        .preview-area img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .preview-placeholder {
            padding: 60px 20px;
            text-align: center;
            color: #9ca3af;
        }
        .preview-placeholder i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }
        .preview-placeholder p {
            font-size: 14px;
            margin: 0;
        }

        /* Download button */
        .download-section {
            text-align: center;
            padding-top: 4px;
        }
        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .download-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }
        .no-download {
            color: #9ca3af;
            font-size: 13px;
            font-style: italic;
        }

        /* Footer */
        .public-footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 24px 0;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="public-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('documents.public.portal') }}" class="brand">
                <i class="fas fa-graduation-cap"></i>
                Heritage Junior Secondary School
            </a>
            <span class="label">
                <i class="fas fa-globe me-1"></i> Public Document
            </span>
        </div>
    </nav>

    {{-- Document Header --}}
    <div class="doc-header">
        <div class="container">
            <h1>{{ $document->title }}</h1>
            <div class="meta">
                Shared by {{ $document->owner->full_name ?? 'Unknown' }}
                @if($document->category)
                    &middot; {{ $document->category->name }}
                @endif
                &middot; {{ strtoupper($document->extension ?: ($document->isExternalUrl() ? 'LINK' : 'FILE')) }} file
            </div>
        </div>
    </div>

    {{-- Document Content --}}
    <div class="doc-content">
        <div class="doc-card">
            {{-- Metadata Grid --}}
            <div class="info-grid">
                <div class="info-item">
                    <label>File Type</label>
                    <span>
                        @php
                            $iconMap = [
                                'pdf' => 'fa-file-pdf',
                                'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
                                'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
                                'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
                                'png' => 'fa-file-image', 'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'gif' => 'fa-file-image',
                                'zip' => 'fa-file-zipper', 'rar' => 'fa-file-zipper',
                                'txt' => 'fa-file-lines', 'csv' => 'fa-file-csv',
                            ];
                            $fileIcon = $iconMap[strtolower((string) $document->extension)] ?? 'fa-file';
                        @endphp
                        <i class="fas {{ $fileIcon }} me-1" style="color: #4e73df;"></i>
                        {{ strtoupper($document->extension ?: ($document->isExternalUrl() ? 'LINK' : 'UNKNOWN')) }}
                    </span>
                </div>
                <div class="info-item">
                    <label>Published</label>
                    <span>{{ $document->published_at?->format('M d, Y') ?? 'N/A' }}</span>
                </div>
                @if($document->category)
                    <div class="info-item">
                        <label>Category</label>
                        <span class="category-badge" style="background: {{ $document->category->color ?? '#eff6ff' }}20; color: {{ $document->category->color ?? '#3b82f6' }};">
                            {{ $document->category->name }}
                        </span>
                    </div>
                @endif
                <div class="info-item">
                    <label>File Size</label>
                    <span>
                        @if(is_null($document->size_bytes))
                            Remote size not tracked
                        @elseif($document->size_bytes >= 1048576)
                            {{ number_format($document->size_bytes / 1048576, 1) }} MB
                        @elseif($document->size_bytes >= 1024)
                            {{ number_format($document->size_bytes / 1024, 1) }} KB
                        @else
                            {{ $document->size_bytes }} B
                        @endif
                    </span>
                </div>
            </div>

            {{-- Description --}}
            @if($document->description)
                <div class="doc-description">{{ $document->description }}</div>
            @endif

            {{-- Preview Area --}}
            <div class="preview-area">
                @php
                    $ext = strtolower((string) $document->extension);
                    $isPdf = $ext === 'pdf';
                    $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']);
                @endphp

                @if($document->isExternalUrl())
                    <div class="preview-placeholder">
                        <i class="fas fa-link"></i>
                        <p>This document is hosted externally.</p>
                        <p style="font-size: 12px; margin-top: 6px; color: #c0c4cc;">Open it using the button below.</p>
                    </div>
                @elseif($isPdf)
                    <iframe src="{{ route('documents.public.preview', ['token' => $token]) }}" title="Document Preview"></iframe>
                @elseif($isImage)
                    <img src="{{ route('documents.public.preview', ['token' => $token]) }}" alt="{{ $document->title }}">
                @else
                    <div class="preview-placeholder">
                        <i class="fas {{ $fileIcon }}"></i>
                        <p>Preview is not available for this file type.</p>
                        <p style="font-size: 12px; margin-top: 6px; color: #c0c4cc;">{{ $document->original_name }}</p>
                    </div>
                @endif
            </div>

            {{-- Download Section --}}
            <div class="download-section">
                <a href="{{ route('documents.public.preview', ['token' => $token]) }}" class="download-btn mb-2">
                    <i class="fas fa-up-right-from-square"></i>
                    Open Document
                </a>
                <br>
                @if($allowDownload)
                    <a href="{{ route('documents.public.download', ['token' => $token]) }}" class="download-btn">
                        <i class="fas fa-download"></i>
                        {{ $document->isExternalUrl() ? 'Go To Download' : 'Download ' . strtoupper($document->extension ?: 'FILE') . ' File' }}
                    </a>
                @else
                    <p class="no-download">
                        <i class="fas fa-lock me-1"></i>
                        Download is not available for this link.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="public-footer">
        <div class="container">
            <a href="{{ route('documents.public.portal') }}" style="color: #4e73df; text-decoration: none; font-weight: 500;">
                <i class="fas fa-arrow-left me-1"></i> Browse Public Documents
            </a>
            <div class="mt-2">
                Heritage Junior Secondary School &copy; {{ date('Y') }} &mdash; Powered by Heritage DMS
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
