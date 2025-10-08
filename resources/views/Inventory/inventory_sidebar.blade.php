@php
    $inventoryName = auth()->user()->name ?? 'Inventory';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Panel</title>
    <link rel="stylesheet" href="{{ asset('css/inventorycss/inventory.css') }}">
</head>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>Inventory Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="/inventory/home" class="sidebar-btn{{ request()->is('inventory/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/inventory/stocks" class="sidebar-btn{{ request()->is('inventory/stocks') ? ' active' : '' }}">
                    <span class="icon">📦</span> <span class="text">Stocks</span>
                </a>
            </li>
            <li>
                <a href="/inventory/orders" class="sidebar-btn{{ request()->is('inventory/orders') ? ' active' : '' }}">
                    <span class="icon">📝</span> <span class="text">Orders</span>
                </a>
            </li>
            <li>
                <a href="/inventory/reports" class="sidebar-btn{{ request()->is('inventory/reports') ? ' active' : '' }}">
                    <span class="icon">📊</span> <span class="text">Reports</span>
                </a>
            </li>
        </ul>
        <form action="/logout" method="POST" id="inventory-logout-form" class="logout-form">
            @csrf
            <button type="button" class="sidebar-btn" onclick="confirmLogout('inventory-logout-form')">
                <span class="icon">🚪</span> <span class="text">Logout</span>
            </button>
        </form>
    </nav>
</div>

<script>
    let isToggling = false;
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('inventorySidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
        }

        function adjustLayoutHeight() {
            if (isToggling) return;
            if (window.isFiltering) return;
            requestAnimationFrame(() => {
                if (!mainContent) return;
                const contentHeight = mainContent.scrollHeight;
                const viewportHeight = window.innerHeight - 120;
                const minHeight = Math.max(contentHeight, viewportHeight);
                const currentHeight = parseInt(sidebar.style.minHeight) || 0;
                if (Math.abs(minHeight - currentHeight) > 10) {
                    sidebar.style.minHeight = minHeight + 'px';
                }
            });
        }

        let heightTimeout;
        function debouncedHeightAdjustment() {
            if (isToggling) return;
            if (window.isFiltering) return;
            clearTimeout(heightTimeout);
            heightTimeout = setTimeout(adjustLayoutHeight, 300);
        }

        setTimeout(() => { if (!isToggling) adjustLayoutHeight(); }, 300);
        window.addEventListener('resize', debouncedHeightAdjustment);

        const observer = new MutationObserver(function(mutations) {
            let shouldAdjust = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                        const hasElementChanges = Array.from(mutation.addedNodes).some(node => node.nodeType === 1) ||
                                                Array.from(mutation.removedNodes).some(node => node.nodeType === 1);
                        if (hasElementChanges) shouldAdjust = true;
                    }
                }
            });
            if (shouldAdjust) debouncedHeightAdjustment();
        });
        if (mainContent) observer.observe(mainContent, { childList: true, subtree: true });

        const toggle = document.getElementById('sidebarToggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                isToggling = true;
                clearTimeout(heightTimeout);
                sidebar.classList.toggle('collapsed');
                if (mainContent) mainContent.classList.toggle('expanded');
                localStorage.setItem('inventorySidebarCollapsed', sidebar.classList.contains('collapsed'));
                setTimeout(() => { isToggling = false; }, 400);
            });
        }
    });
</script>
@include('Inventory.modals.notification_system')
@include('shared.logout_modal')