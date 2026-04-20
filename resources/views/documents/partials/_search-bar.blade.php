{{-- Search bar partial with live AJAX suggestions --}}
<div class="search-bar-wrapper" style="position: relative; width: 100%; max-width: 380px;">
    <form action="{{ route('documents.search') }}" method="GET" id="search-bar-form" style="margin: 0;">
        <div class="input-group" style="position: relative;">
            <span class="input-group-text" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="search-input" name="q"
                placeholder="Search documents..."
                autocomplete="off"
                style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: white; font-size: 14px;"
            >
        </div>
    </form>

    {{-- Suggestion dropdown --}}
    <div id="search-suggestions"
        style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050;
            background:white; border:1px solid #e5e7eb; border-radius:0 0 6px 6px;
            box-shadow:0 4px 16px rgba(0,0,0,0.12); max-height:320px; overflow-y:auto; margin-top:2px;">
    </div>
</div>

<style>
    .search-bar-wrapper #search-input::placeholder {
        color: rgba(255,255,255,0.6);
    }
    .search-bar-wrapper #search-input:focus {
        background: rgba(255,255,255,0.25) !important;
        border-color: rgba(255,255,255,0.5) !important;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.1) !important;
        color: white !important;
    }
    .suggestion-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        text-decoration: none;
        color: #374151;
        font-size: 13px;
        transition: background 0.1s;
        border-bottom: 1px solid #f3f4f6;
    }
    .suggestion-item:last-child {
        border-bottom: none;
    }
    .suggestion-item:hover {
        background: #f0f4ff;
        color: #3b82f6;
    }
    .suggestion-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 13px;
        flex-shrink: 0;
    }
    .suggestion-icon.pdf { background: #fef2f2; color: #ef4444; }
    .suggestion-icon.word { background: #eff6ff; color: #3b82f6; }
    .suggestion-icon.excel { background: #f0fdf4; color: #22c55e; }
    .suggestion-icon.ppt { background: #fff7ed; color: #f97316; }
    .suggestion-icon.image { background: #faf5ff; color: #a855f7; }
    .suggestion-empty {
        padding: 16px;
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
    }
</style>

<script>
(function() {
    var searchInput = document.getElementById('search-input');
    var suggestionsBox = document.getElementById('search-suggestions');
    var debounceTimer = null;
    var currentXhr = null;

    function getFileIcon(ext) {
        ext = (ext || '').toLowerCase();
        if (ext === 'pdf') return { icon: 'fa-file-pdf', cls: 'pdf' };
        if (ext === 'doc' || ext === 'docx') return { icon: 'fa-file-word', cls: 'word' };
        if (ext === 'xls' || ext === 'xlsx') return { icon: 'fa-file-excel', cls: 'excel' };
        if (ext === 'ppt' || ext === 'pptx') return { icon: 'fa-file-powerpoint', cls: 'ppt' };
        if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].indexOf(ext) !== -1) return { icon: 'fa-file-image', cls: 'image' };
        if (ext === 'txt') return { icon: 'fa-file-lines', cls: '' };
        return { icon: 'fa-file', cls: '' };
    }

    if (!searchInput || !suggestionsBox) return;

    searchInput.addEventListener('input', function() {
        var query = this.value.trim();

        if (debounceTimer) clearTimeout(debounceTimer);

        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function() {
            // Abort previous request
            if (currentXhr) {
                currentXhr.abort();
            }

            currentXhr = new XMLHttpRequest();
            currentXhr.open('GET', '{{ route("documents.search.suggestions") }}?q=' + encodeURIComponent(query));
            currentXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            currentXhr.onload = function() {
                if (currentXhr.status === 200) {
                    var results = JSON.parse(currentXhr.responseText);
                    suggestionsBox.innerHTML = '';

                    if (results.length === 0) {
                        suggestionsBox.innerHTML = '<div class="suggestion-empty">No documents found</div>';
                    } else {
                        results.forEach(function(doc) {
                            var fi = getFileIcon(doc.extension);
                            var a = document.createElement('a');
                            a.className = 'suggestion-item';
                            a.href = '/documents/' + doc.id;
                            a.innerHTML =
                                '<div class="suggestion-icon ' + fi.cls + '"><i class="fas ' + fi.icon + '"></i></div>' +
                                '<span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' +
                                    doc.title +
                                '</span>' +
                                '<span style="color:#9ca3af; font-size:11px;">.' + (doc.extension || '') + '</span>';
                            suggestionsBox.appendChild(a);
                        });
                    }

                    suggestionsBox.style.display = 'block';
                }
                currentXhr = null;
            };

            currentXhr.onerror = function() {
                currentXhr = null;
            };

            currentXhr.send();
        }, 300);
    });

    // Hide dropdown on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-bar-wrapper')) {
            suggestionsBox.style.display = 'none';
        }
    });

    // Show suggestions again when focusing input (if already populated)
    searchInput.addEventListener('focus', function() {
        if (suggestionsBox.innerHTML.trim() !== '' && this.value.trim().length >= 2) {
            suggestionsBox.style.display = 'block';
        }
    });
})();
</script>
