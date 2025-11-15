<header class="cashier-header">
    <div class="header-container">
        <div class="hospital-info">
            <img src="{{ asset('img/hospital_logo.png') }}" alt="Hospital Logo" class="hospital-logo">
            <h1 class="hospital-name">Romel Cruz Hospital</h1>
        </div>
        <span class="cashier-name">{{ auth()->user()->name ?? 'Cashier' }}</span>
    </div>
</header>
