<?php if($paginator->hasPages()): ?>
    <div class="custom-pagination">
        
        <?php if($paginator->onFirstPage()): ?>
            <span class="pagination-arrow disabled">‹</span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="pagination-arrow">‹</a>
        <?php endif; ?>

        
        <div class="page-input-section">
            <span class="page-text">Page</span>
            <input 
                type="number" 
                class="page-input" 
                value="<?php echo e($paginator->currentPage()); ?>" 
                min="1" 
                max="<?php echo e($paginator->lastPage()); ?>"
                onchange="goToPage(this.value)"
                onkeypress="if(event.key==='Enter') goToPage(this.value)"
            >
            <span class="page-text">of <?php echo e($paginator->lastPage()); ?></span>
        </div>

        
        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="pagination-arrow">›</a>
        <?php else: ?>
            <span class="pagination-arrow disabled">›</span>
        <?php endif; ?>
    </div>

    <script>
        function goToPage(page) {
            const currentUrl = new URL(window.location.href);
            const maxPage = <?php echo e($paginator->lastPage()); ?>;
            const currentPage = <?php echo e($paginator->currentPage()); ?>;
            
            // Validate page number
            page = parseInt(page);
            if (isNaN(page) || page < 1) page = 1;
            if (page > maxPage) page = maxPage;
            
            // Update input value to corrected page
            if (event && event.target) {
                event.target.value = page;
            }
            
            // Don't navigate if we're already on this page
            if (page === currentPage) {
                return;
            }
            
            // Add loading state
            const paginationWrapper = document.querySelector('.pagination-wrapper');
            if (paginationWrapper) {
                paginationWrapper.style.opacity = '0.6';
                paginationWrapper.style.pointerEvents = 'none';
            }
            
            // Navigate to the page
            if (page === 1) {
                currentUrl.searchParams.delete('page');
            } else {
                currentUrl.searchParams.set('page', page);
            }
            window.location.href = currentUrl.toString();
        }
    </script>
<?php endif; ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/components/custom-pagination.blade.php ENDPATH**/ ?>