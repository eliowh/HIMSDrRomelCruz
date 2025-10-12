<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - HIMS</title>
        <link rel="stylesheet" href="<?php echo e(asset('css/admincss/admin.css')); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <?php echo $__env->make('admin.admin_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php
            $adminName = auth()->user()->name ?? 'Admin';
            
            // Get comprehensive statistics
            $totalUsers = \App\Models\User::count();
            $doctorsCount = \App\Models\User::where('role', 'doctor')->count();
            $nursesCount = \App\Models\User::where('role', 'nurse')->count();
            $labtechCount = \App\Models\User::where('role', 'lab_technician')->count();
            $pharmacyCount = \App\Models\User::where('role', 'pharmacy')->count();
            $billingCount = \App\Models\User::where('role', 'billing')->count();
            $cashierCount = \App\Models\User::where('role', 'cashier')->count();
            
            // Get patient statistics
            $totalPatients = \App\Models\Patient::count();
            $todayPatients = \App\Models\Patient::whereDate('created_at', today())->count();
            
            // Get recent users
            $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->take(5)->get();
        ?>
        <div class="admin-layout">
            <?php echo $__env->make('admin.admin_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="main-content">
                <div class="admin-card welcome-card">
                    <h2><i class="fas fa-user-shield"></i> Welcome, <?php echo e($adminName); ?>!</h2>
                    <p>Hospital Information Management System - Administrator Dashboard</p>
                </div>
                
                <!-- Main Statistics Grid -->
                <div class="stats-container">
                    <div class="stat-card users">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-number"><?php echo e($totalUsers); ?></span>
                            <span class="stat-label">Total Users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card patients">
                        <div class="stat-icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-number"><?php echo e($totalPatients); ?></span>
                            <span class="stat-label">Total Patients</span>
                            <span class="stat-badge"><?php echo e($todayPatients); ?> today</span>
                        </div>
                    </div>
                    
                    <div class="stat-card doctors">
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-number"><?php echo e($doctorsCount); ?></span>
                            <span class="stat-label">Doctors</span>
                        </div>
                    </div>
                    
                    <div class="stat-card staff">
                        <div class="stat-icon">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-number"><?php echo e($nursesCount + $labtechCount + $pharmacyCount + $billingCount + $cashierCount); ?></span>
                            <span class="stat-label">Staff Members</span>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Staff Statistics -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Staff Distribution</h3>
                        <a href="/admin/users" class="view-all-link">Manage All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="staff-grid">
                        <div class="staff-item">
                            <div class="staff-icon nurses">
                                <i class="fas fa-user-nurse"></i>
                            </div>
                            <div class="staff-info">
                                <span class="staff-number"><?php echo e($nursesCount); ?></span>
                                <span class="staff-label">Nurses</span>
                            </div>
                        </div>
                        <div class="staff-item">
                            <div class="staff-icon labtech">
                                <i class="fas fa-microscope"></i>
                            </div>
                            <div class="staff-info">
                                <span class="staff-number"><?php echo e($labtechCount); ?></span>
                                <span class="staff-label">Lab Technicians</span>
                            </div>
                        </div>
                        <div class="staff-item">
                            <div class="staff-icon pharmacy">
                                <i class="fas fa-pills"></i>
                            </div>
                            <div class="staff-info">
                                <span class="staff-number"><?php echo e($pharmacyCount); ?></span>
                                <span class="staff-label">Pharmacy</span>
                            </div>
                        </div>
                        <div class="staff-item">
                            <div class="staff-icon billing">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="staff-info">
                                <span class="staff-number"><?php echo e($billingCount); ?></span>
                                <span class="staff-label">Billing</span>
                            </div>
                        </div>
                        <div class="staff-item">
                            <div class="staff-icon cashier">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div class="staff-info">
                                <span class="staff-number"><?php echo e($cashierCount); ?></span>
                                <span class="staff-label">Cashier</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="admin-card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="quick-actions">
                        <a href="/admin/users" class="quick-action-btn users">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>

                        <button class="quick-action-btn add-user" onclick="openAddUserModal()">
                            <i class="fas fa-user-plus"></i>
                            <span>Add New User</span>
                        </button>

                        <a href="<?php echo e(route('admin.patients')); ?>" class="quick-action-btn patients">
                            <i class="fas fa-user-injured"></i>
                            <span>Manage Patients</span>
                        </a>

                        <button class="quick-action-btn rooms" onclick="openAddRoomModal()">
                            <i class="fas fa-bed"></i>
                            <span>Add Room</span>
                        </button>

                        <a href="<?php echo e(route('admin.reports')); ?>" class="quick-action-btn reports">
                            <i class="fas fa-chart-line"></i>
                            <span>View Reports</span>
                        </a>
                        
                        <a href="/admin/stocks" class="quick-action-btn inventory">
                            <i class="fas fa-boxes"></i>
                            <span>Inventory</span>
                        </a>
                    </div>
                </div>

                <!-- Inventory Stocks Summary Card -->
                <div class="admin-card">
                    <h3>Inventory Summary</h3>
                    <div class="stocks-summary">
                        <div class="stocks-grid">
                            <div class="stock-item">
                                <div class="stock-icon">📦</div>
                                <div class="stock-info">
                                    <span class="stock-number"><?php echo e($stocksSummary['total_items'] ?? 0); ?></span>
                                    <span class="stock-label">Total Items</span>
                                </div>
                            </div>
                            <div class="stock-item">
                                <div class="stock-icon">⚠️</div>
                                <div class="stock-info">
                                    <span class="stock-number"><?php echo e($stocksSummary['low_stock'] ?? 0); ?></span>
                                    <span class="stock-label">Low Stock</span>
                                </div>
                            </div>
                            <div class="stock-item">
                                <div class="stock-icon">❌</div>
                                <div class="stock-info">
                                    <span class="stock-number"><?php echo e($stocksSummary['out_of_stock'] ?? 0); ?></span>
                                    <span class="stock-label">Out of Stock</span>
                                </div>
                            </div>
                            <div class="stock-item">
                                <div class="stock-icon">💰</div>
                                <div class="stock-info">
                                    <span class="stock-number">₱<?php echo e(number_format((float)($stocksSummary['total_value'] ?? 0), 2)); ?></span>
                                    <span class="stock-label">Total Value</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Recent Users</h3>
                        <a href="/admin/users" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <?php if($recentUsers->count() > 0): ?>
                        <div class="table-wrap">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php switch($user->role):
                                                        case ('doctor'): ?>
                                                            <i class="fas fa-user-md"></i>
                                                            <?php break; ?>
                                                        <?php case ('nurse'): ?>
                                                            <i class="fas fa-user-nurse"></i>
                                                            <?php break; ?>
                                                        <?php case ('lab_technician'): ?>
                                                            <i class="fas fa-microscope"></i>
                                                            <?php break; ?>
                                                        <?php case ('pharmacy'): ?>
                                                            <i class="fas fa-pills"></i>
                                                            <?php break; ?>
                                                        <?php case ('billing'): ?>
                                                            <i class="fas fa-file-invoice-dollar"></i>
                                                            <?php break; ?>
                                                        <?php case ('cashier'): ?>
                                                            <i class="fas fa-cash-register"></i>
                                                            <?php break; ?>
                                                        <?php default: ?>
                                                            <i class="fas fa-user"></i>
                                                    <?php endswitch; ?>
                                                </div>
                                                <div class="user-name"><?php echo e($user->name); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?php echo e($user->role); ?>">
                                                <?php echo e(ucwords(str_replace('_', ' ', $user->role))); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($user->created_at->format('M d, Y')); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-users"></i>
                            <p>No users found</p>
                        </div>
                    <?php endif; ?>
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
                            <span class="info-value"><?php echo e(now()->format('M d, Y - g:i A')); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Administrator</span>
                            <span class="info-value"><?php echo e($adminName); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $__env->make('admin.modals.admin_createUser', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.modals.admin_createRoom', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }

        function openAddRoomModal() {
            const modal = document.getElementById('addRoomModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeAddRoomModal() {
            const modal = document.getElementById('addRoomModal');
            if (modal) modal.style.display = 'none';
        }
        </script>        
    </body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\admin\admin_home.blade.php ENDPATH**/ ?>