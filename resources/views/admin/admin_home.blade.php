<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Approval</title>
        <link rel="stylesheet" href="{{url('css/admin.css')}}">
    </head>
    @include('admin.admin_header')
    <body>
        @php
            $adminName = auth()->user()->name ?? 'Admin';
        @endphp
        <div class="admin-layout">
            @include('admin.admin_sidebar')
            <div class="main-content">
                <h2>Welcome, {{ $adminName }}!</h2>
                
                <!-- Dashboard Statistics Cards -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <h3>Total Users</h3>
                            <p class="stat-number">{{ \App\Models\User::count() }}</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">👨‍⚕️</div>
                        <div class="stat-content">
                            <h3>Doctors</h3>
                            <p class="stat-number">{{ \App\Models\User::where('role', 'doctor')->count() }}</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">👩‍⚕️</div>
                        <div class="stat-content">
                            <h3>Nurses</h3>
                            <p class="stat-number">{{ \App\Models\User::where('role', 'nurse')->count() }}</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🔬</div>
                        <div class="stat-content">
                            <h3>Lab Technicians</h3>
                            <p class="stat-number">{{ \App\Models\User::where('role', 'lab_technician')->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="admin-card">
                    <h3>Quick Actions</h3>
                    <div class="quick-actions">
                        <a href="/admin/users" class="action-btn primary">
                            <span class="btn-icon">👥</span>
                            Manage Users
                        </a>
                        <button class="action-btn secondary" onclick="openAddUserModal()">
                            <span class="btn-icon">➕</span>
                            Add New User
                        </button>
                        <a href="{{ route('admin.reports') }}" class="action-btn secondary">
                            <span class="btn-icon">📊</span>
                            View Reports
                        </a>
                        <a href="#" class="action-btn secondary">
                            <span class="btn-icon">⚙️</span>
                            System Settings
                        </a>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="admin-card">
                    <h3>Recent Activity</h3>
                    <div class="activity-list">
                        @php
                            $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->take(5)->get();
                        @endphp
                        @if($recentUsers->count() > 0)
                            @foreach($recentUsers as $user)
                            <div class="activity-item">
                                <div class="activity-icon">
                                    @switch($user->role)
                                        @case('doctor')
                                            👨‍⚕️
                                            @break
                                        @case('nurse')
                                            👩‍⚕️
                                            @break
                                        @case('lab_technician')
                                            🔬
                                            @break
                                        @case('cashier')
                                            💰
                                            @break
                                        @default
                                            👤
                                    @endswitch
                                </div>
                                <div class="activity-content">
                                    <p class="activity-title">New {{ ucwords(str_replace('_', ' ', $user->role)) }} registered</p>
                                    <p class="activity-detail">{{ $user->name }} - {{ $user->email }}</p>
                                    <p class="activity-time">{{ $user->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="no-activity">No recent activity to display.</p>
                        @endif
                    </div>
                </div>

                <!-- System Overview Card -->
                <div class="admin-card">
                    <h3>System Overview</h3>
                    <div class="system-info">
                        <div class="info-row">
                            <span class="info-label">Hospital Management System</span>
                            <span class="info-value">Dr. Romel Cruz Clinic</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">System Status</span>
                            <span class="info-value status-online">🟢 Online</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Login</span>
                            <span class="info-value">{{ now()->format('M d, Y - g:i A') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Administrator</span>
                            <span class="info-value">{{ $adminName }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.modal.admin_createUser')

        <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        </script>
    </body>
</html>