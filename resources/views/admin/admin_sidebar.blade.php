@php
    $adminName = auth()->user()->name ?? 'Admin';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Approval</title>
    <link rel="stylesheet" href="{{url('css/admin.css')}}">
</head>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>HIMS Admin</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="{{ url('/admin/home') }}"
                   class="sidebar-btn{{ request()->is('admin/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/users') }}"
                   class="sidebar-btn{{ request()->is('admin/users') ? ' active' : '' }}">
                    <span class="icon">👤</span> <span class="text">Users Management</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/reports') }}"
                   class="sidebar-btn{{ request()->is('admin/reports') ? ' active' : '' }}">
                    <span class="icon">📊</span> <span class="text">Reports</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/account') }}"
                   class="sidebar-btn{{ request()->is('admin/account') ? ' active' : '' }}">
                    <span class="icon">⚙️</span> <span class="text">Account</span>
                </a>
            </li>
            <li>
                <form action="{{ url('/logout') }}" method="POST" id="logout-form" class="logout-form">
                    @csrf
                    <button type="button" class="sidebar-btn" onclick="confirmLogout()">
                        <span class="icon">🚪</span> <span class="text">Log Out</span>
                    </button>
                </form>
            </li>

            <script>
                function confirmLogout() {
                    if (confirm('Are you sure you want to logout?')) {
                        localStorage.clear();
                        document.getElementById('logout-form').submit();
                    }
                }
            </script>
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