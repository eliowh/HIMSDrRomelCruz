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
                <a href="{{ url('/admin/userapproval') }}"
                   class="sidebar-btn{{ request()->is('admin/userapproval') ? ' active' : '' }}">
                    <span class="icon">👥</span> <span class="text">User Approval</span>
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
                <form action="{{ url('/logout') }}" method="POST" class="logout-form" onsubmit="localStorage.clear();">
                    @csrf
                    <button type="submit" class="sidebar-btn">
                        <span class="icon">🚪</span> <span class="text">Log Out</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>

<script>
    // Check localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
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
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
</script>