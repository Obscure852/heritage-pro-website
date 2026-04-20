@extends('layouts.master')
@section('title')
    Borrowers
@endsection
@section('css')
    <style>
        .library-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .library-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .library-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        .search-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .search-wrapper .input-group {
            min-width: 60%;
        }

        .search-wrapper .form-control {
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 3px 0 0 3px;
            transition: all 0.2s ease;
        }

        .search-wrapper .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .search-wrapper .input-group-text {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 3px 3px 0;
            color: #6b7280;
            padding: 14px 16px;
        }

        .borrower-search-results {
            margin-top: 16px;
        }

        .search-spinner {
            text-align: center;
            padding: 24px;
            color: #6b7280;
        }

        .search-spinner .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
        }

        .borrower-result-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .borrower-result-item:hover {
            background: #f9fafb;
            border-color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            color: inherit;
            text-decoration: none;
        }

        .borrower-result-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 14px;
            flex-shrink: 0;
        }

        .borrower-result-icon.student-icon {
            background: #dbeafe;
            color: #1e40af;
        }

        .borrower-result-icon.staff-icon {
            background: #e0e7ff;
            color: #4338ca;
        }

        .borrower-result-info {
            flex: 1;
            min-width: 0;
        }

        .borrower-result-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .borrower-result-details {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
        }

        .borrower-result-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            flex-shrink: 0;
            margin-left: 12px;
        }

        .badge-student {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-staff {
            background: #dbeafe;
            color: #1e40af;
        }

        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 15px;
            margin: 0;
        }

        .search-hint {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .search-wrapper .input-group {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.dashboard') }}">Library</a>
        @endslot
        @slot('title')
            Borrowers
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <h4 class="mb-1 text-white"><i class="fas fa-user-friends me-2"></i>Borrower Lookup</h4>
            <p class="mb-0 opacity-75">Search and manage library borrowers</p>
        </div>
        <div class="library-body">
            <div class="help-text">
                <div class="help-title">Find a Borrower</div>
                <div class="help-content">
                    Search for students or staff by name, ID number, or exam number. Click a result to view their borrower profile.
                </div>
            </div>

            <div class="search-wrapper">
                <div class="input-group">
                    <input type="text"
                           id="borrowerSearch"
                           class="form-control"
                           placeholder="Search by name, ID number, or exam number..."
                           autocomplete="off">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="search-hint" id="searchHint">Type at least 2 characters to search</div>

            <div class="borrower-search-results" id="searchResults" style="display: none;"></div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('borrowerSearch');
            const resultsContainer = document.getElementById('searchResults');
            const searchHint = document.getElementById('searchHint');
            let debounceTimer = null;

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    resultsContainer.style.display = 'none';
                    searchHint.style.display = 'block';
                    searchHint.textContent = 'Type at least 2 characters to search';
                    return;
                }

                searchHint.style.display = 'none';

                debounceTimer = setTimeout(function() {
                    performSearch(query);
                }, 300);
            });

            function performSearch(query) {
                // Show loading spinner
                resultsContainer.style.display = 'block';
                resultsContainer.innerHTML = '<div class="search-spinner"><span class="spinner-border text-primary" role="status"></span><div class="mt-2">Searching...</div></div>';

                fetch('{{ route("library.borrowers.search") }}?search=' + encodeURIComponent(query), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Search failed');
                    }
                    return response.json();
                })
                .then(function(results) {
                    renderResults(results);
                })
                .catch(function(error) {
                    console.error('Search error:', error);
                    resultsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>An error occurred while searching. Please try again.</p></div>';
                });
            }

            function renderResults(results) {
                if (!results || results.length === 0) {
                    resultsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-user-slash d-block"></i><p>No borrowers found matching your search</p></div>';
                    return;
                }

                var html = '';
                results.forEach(function(result) {
                    var iconClass = result.type === 'student' ? 'student-icon' : 'staff-icon';
                    var icon = result.type === 'student' ? 'fa-user-graduate' : 'fa-user-tie';
                    var badgeClass = result.type === 'student' ? 'badge-student' : 'badge-staff';
                    var badgeText = result.type === 'student' ? 'Student' : 'Staff';
                    var url = '{{ url("library/borrowers") }}/' + result.type + '/' + result.id;

                    html += '<a href="' + url + '" class="borrower-result-item">';
                    html += '<div class="borrower-result-icon ' + iconClass + '"><i class="fas ' + icon + '"></i></div>';
                    html += '<div class="borrower-result-info">';
                    html += '<div class="borrower-result-name">' + escapeHtml(result.name) + '</div>';
                    html += '<div class="borrower-result-details">' + escapeHtml(String(result.identifier)) + ' &middot; ' + escapeHtml(result.extra) + '</div>';
                    html += '</div>';
                    html += '<span class="borrower-result-badge ' + badgeClass + '">' + badgeText + '</span>';
                    html += '</a>';
                });

                resultsContainer.innerHTML = html;
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(text));
                return div.innerHTML;
            }
        });
    </script>
@endsection
