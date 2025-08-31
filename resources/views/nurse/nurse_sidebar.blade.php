@php
    $nurseName = auth()->user()->name ?? 'Nurse';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="{{url('css/nurse.css')}}">
</head>
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
                <a href="{{ url('/nurse/appointments') }}"
                   class="sidebar-btn{{ request()->is('nurse/appointments') ? ' active' : '' }}">
                    <span class="icon">📅</span> <span class="text">Your Appointments</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/nurse/patients') }}"
                   class="sidebar-btn{{ request()->is('nurse/patients') ? ' active' : '' }}">
                    <span class="icon">👥</span> <span class="text">Patients List</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/nurse/schedule') }}"
                   class="sidebar-btn{{ request()->is('nurse/schedule') ? ' active' : '' }}">
                    <span class="icon">⏰</span> <span class="text">Schedule</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/nurse/account') }}"
                   class="sidebar-btn{{ request()->is('nurse/account') ? ' active' : '' }}">
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
        const isCollapsed = localStorage.getItem('nurseSidebarCollapsed') === 'true';
        
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
        
        // Save state to localStorage with unique key for nurse
        localStorage.setItem('nurseSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
</script>
