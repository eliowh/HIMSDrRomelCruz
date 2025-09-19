@if ($paginator->hasPages())
    <div class="custom-pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-arrow disabled">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-arrow">‹</a>
        @endif

        {{-- Page Input Section --}}
        <div class="page-input-section">
            <span class="page-text">Page</span>
            <input 
                type="number" 
                class="page-input" 
                value="{{ $paginator->currentPage() }}" 
                min="1" 
                max="{{ $paginator->lastPage() }}"
                onchange="goToPage(this.value)"
                onkeypress="if(event.key==='Enter') goToPage(this.value)"
            >
            <span class="page-text">of {{ $paginator->lastPage() }}</span>
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-arrow">›</a>
        @else
            <span class="pagination-arrow disabled">›</span>
        @endif
    </div>

    <script>
        function goToPage(page) {
            const currentUrl = new URL(window.location.href);
            const maxPage = {{ $paginator->lastPage() }};
            
            // Validate page number
            page = parseInt(page);
            if (page < 1) page = 1;
            if (page > maxPage) page = maxPage;
            
            // Update input value to corrected page
            event.target.value = page;
            
            // Navigate to the page
            currentUrl.searchParams.set('page', page);
            window.location.href = currentUrl.toString();
        }
    </script>
@endif