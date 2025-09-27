<header class="labtech-header">
    <div class="header-container">
        <h1 class="hospital-name">Romel Cruz Hospital</h1>
        <span class="labtech-name">{{ Auth::check() ? Auth::user()->name : 'Lab Technician' }}</span>
    </div>
</header>
