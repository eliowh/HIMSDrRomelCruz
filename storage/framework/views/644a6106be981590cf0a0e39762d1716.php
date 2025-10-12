<?php
    $adminName = auth()->user()->name ?? 'Admin';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/admincss/admin.css')); ?>">
</head>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>Admin Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="<?php echo e(asset('/admin/home')); ?>"
                   class="sidebar-btn<?php echo e(request()->is('admin/home') ? ' active' : ''); ?>">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?php echo e(asset('/admin/users')); ?>"
                   class="sidebar-btn<?php echo e(request()->is('admin/users') ? ' active' : ''); ?>">
                    <span class="icon">👤</span> <span class="text">Users Management</span>
                </a>
            </li>
            <li>
                <a href="<?php echo e(asset('/admin/rooms')); ?>"
                   class="sidebar-btn<?php echo e(request()->is('admin/rooms') ? ' active' : ''); ?>">
                    <span class="icon">🏠</span> <span class="text">Room Management</span>
                </a>
            </li>
            <li>
                <a href="<?php echo e(asset('/admin/patients')); ?>"
                   class="sidebar-btn<?php echo e(request()->is('admin/patients') ? ' active' : ''); ?>">
                    <span class="icon">👥</span> <span class="text">Patient Records</span>
                </a>
            </li>
            <li>
                <a href="<?php echo e(asset('/admin/reports')); ?>"
                   class="sidebar-btn<?php echo e(request()->is('admin/reports') ? ' active' : ''); ?>">
                    <span class="icon">📊</span> <span class="text">Reports</span>
                </a>
            </li>
            <li>
                <form action="/logout" method="POST" id="logout-form" class="logout-form">
                    <?php echo csrf_field(); ?>
                    <button type="button" class="sidebar-btn" onclick="confirmLogout('logout-form')">
                        <span class="icon">🚪</span> <span class="text">Log Out</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>

<script>
    let isToggling = false; // Global scope
    
    // Check localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
        
        // Function to adjust layout height
        function adjustLayoutHeight() {
            // Don't adjust height during toggle animation
            if (isToggling) return;
            
            // Don't adjust height during filtering (check if window.isFiltering exists)
            if (window.isFiltering) return;
            
            requestAnimationFrame(() => {
                const contentHeight = mainContent.scrollHeight;
                const viewportHeight = window.innerHeight - 120; // Account for header height
                const minHeight = Math.max(contentHeight, viewportHeight);
                
                // Only update if there's a significant difference to avoid constant updates
                const currentHeight = parseInt(sidebar.style.minHeight) || 0;
                if (Math.abs(minHeight - currentHeight) > 10) {
                    sidebar.style.minHeight = minHeight + 'px';
                }
            });
        }
        
        // Debounced height adjustment
        let heightTimeout;
        function debouncedHeightAdjustment() {
            if (isToggling) return; // Extra check
            if (window.isFiltering) return; // Don't adjust during filtering
            clearTimeout(heightTimeout);
            heightTimeout = setTimeout(adjustLayoutHeight, 300);
        }
        
        // Initial height adjustment with delay
        setTimeout(() => {
            if (!isToggling) adjustLayoutHeight();
        }, 300);
        
        // Listen for window resize
        window.addEventListener('resize', debouncedHeightAdjustment);
        
        // Only observe specific content changes that actually affect layout
        const observer = new MutationObserver(function(mutations) {
            let shouldAdjust = false;
            
            mutations.forEach(function(mutation) {
                // Only adjust for actual content additions/removals, not attribute changes
                if (mutation.type === 'childList') {
                    // Check if nodes were actually added or removed (not just hidden/shown)
                    if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                        // Filter out text nodes and only respond to element changes
                        const hasElementChanges = Array.from(mutation.addedNodes).some(node => node.nodeType === 1) ||
                                                Array.from(mutation.removedNodes).some(node => node.nodeType === 1);
                        if (hasElementChanges) {
                            shouldAdjust = true;
                        }
                    }
                }
            });
            
            if (shouldAdjust) {
                debouncedHeightAdjustment();
            }
        });
        
        observer.observe(mainContent, { 
            childList: true, 
            subtree: true
            // Removed attributes: true to prevent UI interaction triggers
        });
        
        // Toggle and save state
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            // Set toggle flag to prevent ALL height adjustments
            isToggling = true;
            
            // Clear any pending height calculations
            clearTimeout(heightTimeout);
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            
            // Reset toggle flag after animation completes - but DON'T recalculate height
            setTimeout(() => {
                isToggling = false;
                // Only adjust height if content actually changed during toggle
                // This prevents the incremental height increases
            }, 400); // Give extra time for animation
        });
    });
</script>
<?php echo $__env->make('shared.logout_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/admin/admin_sidebar.blade.php ENDPATH**/ ?>