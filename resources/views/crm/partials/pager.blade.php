@if ($paginator->hasPages())
    <div class="crm-pagination">
        <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        <div class="crm-pagination-links">
            @if ($paginator->onFirstPage())
                <span>Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next</a>
            @else
                <span>Next</span>
            @endif
        </div>
    </div>
@endif
