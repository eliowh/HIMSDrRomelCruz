@php
    $nurseName = auth()->user()->name ?? 'Nurse';
@endphp
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>Nurse Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="{{ url('/nurse/home') }}"
                   class="sidebar-btn{{ request()->is('nurse/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/nurse/patients') }}"
                   class="sidebar-btn{{ request()->is('nurse/patients') ? ' active' : '' }}">
                    <span class="icon">👥</span> <span class="text">Patients List</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/nurse/addPatients') }}" 
                   class="sidebar-btn{{ request()->is('nurse/addPatients') ? ' active' : '' }}">
                    <span class="icon">➕</span> <span class="text">Add Patient</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/nurse/account') }}"
                   class="sidebar-btn{{ request()->is('nurse/account') ? ' active' : '' }}">
                    <span class="icon">⚙️</span> <span class="text">Account</span>
                </a>
            </li>
        </ul>
        <form action="{{ url('/logout') }}" method="POST" id="nurse-logout-form" class="logout-form">
            @csrf
            <button type="button" class="sidebar-btn logout-btn" onclick="confirmLogout('nurse-logout-form')">
                <span class="icon">🚪</span> <span class="text">Logout</span>
            </button>
        </form>
    </nav>
</div>

<script>
    let isToggling = false; // Global scope
    
    // Check localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dropdown state variables if not already set
        if (typeof window.isDropdownOpen === 'undefined') {
            window.isDropdownOpen = false;
        }
        if (typeof window.isModalOpen === 'undefined') {
            window.isModalOpen = false;
        }
        
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('nurseSidebarCollapsed') === 'true';
        
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
            
            // Don't adjust height during modal interactions
            if (window.isModalOpen) return;
            
            // Don't adjust height during dropdown interactions
            if (window.isDropdownOpen) return;
            
            // Check if any modal is currently visible
            const visibleModals = document.querySelectorAll('.modal[style*="display: block"], .modal.show');
            if (visibleModals.length > 0) return;
            
            // Check if any suggestion dropdowns are visible
            const visibleDropdowns = document.querySelectorAll('.icd-suggestions[style*="display: block"], #room-suggestions[style*="display: block"], #icd10-suggestions[style*="display: block"]');
            if (visibleDropdowns.length > 0) return;
            
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
            if (window.isModalOpen) return; // Don't adjust during modal interactions
            if (window.isDropdownOpen) return; // Don't adjust during dropdown interactions
            
            // Check if any modal is currently visible
            const visibleModals = document.querySelectorAll('.modal[style*="display: block"], .modal.show');
            if (visibleModals.length > 0) return;
            
            // Check if any suggestion dropdowns are visible
            const visibleDropdowns = document.querySelectorAll('.icd-suggestions[style*="display: block"], #room-suggestions[style*="display: block"], #icd10-suggestions[style*="display: block"]');
            if (visibleDropdowns.length > 0) return;
            
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
            
            // Don't adjust during modal interactions
            if (window.isModalOpen) return;
            
            // Don't adjust during dropdown interactions
            if (window.isDropdownOpen) return;
            
            // Check if any modal is currently visible
            const visibleModals = document.querySelectorAll('.modal[style*="display: block"], .modal.show');
            if (visibleModals.length > 0) return;
            
            // Check if any suggestion dropdowns are visible
            const visibleDropdowns = document.querySelectorAll('.icd-suggestions[style*="display: block"], #room-suggestions[style*="display: block"], #icd10-suggestions[style*="display: block"]');
            if (visibleDropdowns.length > 0) return;
            
            mutations.forEach(function(mutation) {
                // Skip mutations related to modals, modal content, or dropdown suggestions
                if (mutation.target.closest('.modal') || 
                    mutation.target.classList.contains('modal') ||
                    mutation.target.id === 'labRequestModal' ||
                    mutation.target.closest('#labRequestModal') ||
                    mutation.target.classList.contains('icd-suggestions') ||
                    mutation.target.classList.contains('icd-suggestion') ||
                    mutation.target.classList.contains('room-suggestion') ||
                    mutation.target.id === 'room-suggestions' ||
                    mutation.target.id === 'icd10-suggestions' ||
                    mutation.target.closest('#room-suggestions') ||
                    mutation.target.closest('#icd10-suggestions') ||
                    mutation.target.closest('.icd-suggestions')) {
                    return;
                }
                
                // Only adjust for actual content additions/removals, not attribute changes
                if (mutation.type === 'childList') {
                    // Check if nodes were actually added or removed (not just hidden/shown)
                    if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                        // Filter out text nodes and only respond to element changes
                        const hasElementChanges = Array.from(mutation.addedNodes).some(node => node.nodeType === 1) ||
                                                Array.from(mutation.removedNodes).some(node => node.nodeType === 1);
                        if (hasElementChanges) {
                            // Double-check that the changes aren't in modal content
                            const addedElementsInModal = Array.from(mutation.addedNodes).some(node => 
                                node.nodeType === 1 && (node.closest('.modal') || node.classList.contains('modal'))
                            );
                            const removedElementsInModal = Array.from(mutation.removedNodes).some(node => 
                                node.nodeType === 1 && (node.closest && node.closest('.modal')) || (node.classList && node.classList.contains('modal'))
                            );
                            
                            if (!addedElementsInModal && !removedElementsInModal) {
                                shouldAdjust = true;
                            }
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
            
            // Save state to localStorage with unique key for nurse
            localStorage.setItem('nurseSidebarCollapsed', sidebar.classList.contains('collapsed'));
            
            // Reset toggle flag after animation completes - but DON'T recalculate height
            setTimeout(() => {
                isToggling = false;
                // Only adjust height if content actually changed during toggle
                // This prevents the incremental height increases
            }, 400); // Give extra time for animation
        });
    });
</script>
@include('shared.logout_modal')