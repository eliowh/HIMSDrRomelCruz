<div class="admin-header">
    <div class="header-container">
        <div class="hospital-info">
            <img src="{{ asset('img/hospital_logo.png') }}" alt="Hospital Logo" class="hospital-logo">
            <h1 class="hospital-name">Romel Cruz Hospital</h1>
        </div>
        <div class="admin-name">{{ Auth::check() ? Auth::user()->name : 'Admin' }}</div>
    </div>   
</div>