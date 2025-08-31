@php
    $doctorName = auth()->user()->name ?? 'Doctor';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="{{url('css/doctor.css')}}">
</head>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <span class="toggle-btn" id="sidebarToggle">☰</span>
        <span>Doctor Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="{{ url('/doctor/home') }}"
                   class="sidebar-btn{{ request()->is('doctor/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/doctor/appointments') }}"
                   class="sidebar-btn{{ request()->is('doctor/appointments') ? ' active' : '' }}">
                    <span class="icon">📅</span> <span class="text">Appointments</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/doctor/patients') }}"
                   class="sidebar-btn{{ request()->is('doctor/patients') ? ' active' : '' }}">
                    <span class="icon">👥</span> <span class="text">Patients</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/doctor/schedule') }}"
                   class="sidebar-btn{{ request()->is('doctor/schedule') ? ' active' : '' }}">
                    <span class="icon">⏰</span> <span class="text">Schedule</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/doctor/account') }}"
                   class="sidebar-btn{{ request()->is('doctor/account') ? ' active' : '' }}">
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
        const isCollapsed = localStorage.getItem('doctorSidebarCollapsed') === 'true';
        
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
        
        // Save state to localStorage with unique key for doctor
        localStorage.setItem('doctorSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
</script>
