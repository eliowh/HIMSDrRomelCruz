<div class="admin-header">
    <div class="header-container">
        <div class="hospital-name">Dr. Romel Cruz Hospital</div>
        <div class="admin-name">{{ Auth::check() ? Auth::user()->name : 'Admin' }}</div>
    </div>   
</div>