<header class="labtech-header">
    <div class="header-container">
        <div class="hospital-info">
            <img src="{{ asset('img/hospital_logo.png') }}" alt="Hospital Logo" class="hospital-logo">
            <h1 class="hospital-name">Romel Cruz Hospital</h1>
        </div>
        <span class="labtech-name">{{ Auth::check() ? Auth::user()->name : 'Lab Technician' }}</span>
    </div>
</header>
