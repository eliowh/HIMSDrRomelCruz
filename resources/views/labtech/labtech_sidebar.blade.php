@php
    $labtechName = auth()->user()->name ?? 'Lab Technician';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Technician Dashboard</title>
    <link rel="stylesheet" href="{{url('css/labtech.css')}}">
</head>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>Lab Tech Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="{{ url('/labtech/home') }}"
                   class="sidebar-btn{{ request()->is('labtech/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/labtech/orders') }}"
                   class="sidebar-btn{{ request()->is('labtech/orders') ? ' active' : '' }}">
                    <span class="icon">🧪</span> <span class="text">Orders</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/labtech/patients') }}"
                   class="sidebar-btn{{ request()->is('labtech/patients') ? ' active' : '' }}">
                    <span class="icon">👥</span> <span class="text">Patients List</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/labtech/account') }}"
                   class="sidebar-btn{{ request()->is('labtech/account') ? ' active' : '' }}">
                    <span class="icon">⚙️</span> <span class="text">Account</span>
                </a>
            </li>
        </ul>
        <form action="{{ url('/logout') }}" method="POST" id="labtech-logout-form" class="logout-form">
            @csrf
            <button type="button" class="sidebar-btn" onclick="confirmLogout()">
                <span class="icon">🚪</span> <span class="text">Logout</span>
            </button>
        </form>
        
        <script>
            function confirmLogout() {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.clear();
                    document.getElementById('labtech-logout-form').submit();
                }
            }
        </script>
    </nav>
</div>

<script>
    // Check localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('labtechSidebarCollapsed') === 'true';
        
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
        
        // Save state to localStorage with unique key for lab technician
        localStorage.setItem('labtechSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
</script>
