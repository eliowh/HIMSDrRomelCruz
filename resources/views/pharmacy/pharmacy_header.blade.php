<header class="pharmacy-header">
    <div class="header-container">
        <h1 class="hospital-name">Dr. Romel Cruz Hospital</h1>
        <span class="pharmacy-name">{{ Auth::check() ? Auth::user()->name : 'Pharmacy' }}</span>
    </div>
</header>
