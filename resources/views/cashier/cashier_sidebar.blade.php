@php
    $cashierName = auth()->user()->name ?? 'Cashier';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="{{url('css/cashier.css')}}">
</head>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>Cashier Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="{{ url('/cashier/home') }}"
                   class="sidebar-btn{{ request()->is('cashier/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/cashier/billing') }}"
                   class="sidebar-btn{{ request()->is('cashier/billing') ? ' active' : '' }}">
                    <span class="icon">💰</span> <span class="text">Billing</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/cashier/transactions') }}"
                   class="sidebar-btn{{ request()->is('cashier/transactions') ? ' active' : '' }}">
                    <span class="icon">📋</span> <span class="text">Transactions</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/cashier/account') }}"
                   class="sidebar-btn{{ request()->is('cashier/account') ? ' active' : '' }}">
                    <span class="icon">⚙️</span> <span class="text">Account</span>
                </a>
            </li>
        </ul>
        <form action="{{ url('/logout') }}" method="POST" class="logout-form" onsubmit="localStorage.clear();">
            @csrf
            <button type="submit" class="sidebar-btn">
                <span class="icon">🚪</span> <span class="text">Log Out</span>
            </button>
        </form>
    </nav>
</div>

<script>
    // Check localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('cashierSidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    });

    // Toggle and save state
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        // Save state to localStorage with unique key for cashier
        localStorage.setItem('cashierSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
</script>
