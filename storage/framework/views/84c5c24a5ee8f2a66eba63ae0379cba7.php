<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title>Reports</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/admincss/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
</head>
<body>
    <?php
        $adminName = auth()->user()->name ?? 'Admin';
    ?>
    <?php echo $__env->make('admin.admin_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="admin-layout">
        <?php echo $__env->make('admin.admin_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-content">
            <h2>Reports & Analytics</h2>
            
            <!-- Report Statistics -->
            <div class="report-stats">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>Total Reports</h3>
                        <p class="stat-number"><?php echo e($stats['total_reports'] ?? 0); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3>Pending</h3>
                        <p class="stat-number"><?php echo e($stats['pending_reports'] ?? 0); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3>Completed</h3>
                        <p class="stat-number"><?php echo e($stats['completed_reports'] ?? 0); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <h3>Failed</h3>
                        <p class="stat-number"><?php echo e($stats['failed_reports'] ?? 0); ?></p>
                    </div>
                </div>
            </div>

            <!-- Generate New Report Card -->
            <div class="admin-card">
                <h3>Generate New Report</h3>
                <form id="generateReportForm" class="report-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reportTitle" class="form-label">Report Title</label>
                            <input type="text" id="reportTitle" name="title" class="form-input" required 
                                   placeholder="Enter report title">
                        </div>
                        
                        <div class="form-group">
                            <label for="reportType" class="form-label">Report Type</label>
                            <select id="reportType" name="type" class="form-input" required>
                                <option value="">Select report type</option>
                                <option value="user_activity">User Activity</option>
                                <option value="login_report">Login Report</option>
                                <option value="user_registration">User Registration</option>
                                <option value="system_log">System Log</option>
                                <option value="custom">Custom Report</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateFrom" class="form-label">From Date</label>
                            <input type="date" id="dateFrom" name="date_from" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="dateTo" class="form-label">To Date</label>
                            <input type="date" id="dateTo" name="date_to" class="form-input">
                        </div>
                    </div>
                    
                    <button type="submit" class="generate-btn">
                        <span class="btn-icon">📊</span>
                        Generate Report
                    </button>
                </form>
            </div>

            <!-- Recent Reports Card -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>Recent Reports</h3>
                    <div class="header-actions">
                        <button class="action-btn secondary" onclick="refreshReports()">
                            <span class="btn-icon">🔄</span>
                            Refresh
                        </button>
                    </div>
                </div>
                
                <div class="reports-table-container">
                    <?php if(isset($reports) && $reports->count() > 0): ?>
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="title">
                                        Title 
                                        <?php if(request('sort') === 'title'): ?>
                                            <span class="sort-indicator"><?php echo e(request('direction') === 'desc' ? '▼' : '▲'); ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" data-sort="type">
                                        Type 
                                        <?php if(request('sort') === 'type'): ?>
                                            <span class="sort-indicator"><?php echo e(request('direction') === 'desc' ? '▼' : '▲'); ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" data-sort="status">
                                        Status 
                                        <?php if(request('sort') === 'status'): ?>
                                            <span class="sort-indicator"><?php echo e(request('direction') === 'desc' ? '▼' : '▲'); ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" data-sort="generated_by">
                                        Generated By 
                                        <?php if(request('sort') === 'generated_by'): ?>
                                            <span class="sort-indicator"><?php echo e(request('direction') === 'desc' ? '▼' : '▲'); ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" data-sort="generated_at">
                                        Date 
                                        <?php if(request('sort') === 'generated_at'): ?>
                                            <span class="sort-indicator"><?php echo e(request('direction') === 'desc' ? '▼' : '▲'); ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="report-title"><?php echo e($report->title); ?></td>
                                    <td>
                                        <span class="type-badge type-<?php echo e($report->type); ?>">
                                            <?php echo e($report->formatted_type); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo e($report->status); ?>">
                                            <?php echo e(ucfirst($report->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($report->generatedBy->name ?? 'System'); ?></td>
                                    <td><?php echo e($report->generated_at ? $report->generated_at->format('M d, Y g:i A') : 'N/A'); ?></td>
                                    <td>
                                        <div class="report-actions">
                                            <button class="action-btn small" onclick="viewReport(<?php echo e($report->id); ?>)" title="View Report">
                                                👁️
                                            </button>
                                            <button class="action-btn small" onclick="exportReport(<?php echo e($report->id); ?>)" title="Export Report">
                                                📥
                                            </button>
                                            <button class="action-btn small delete" onclick="deleteReport(<?php echo e($report->id); ?>)" title="Delete Report">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-reports">
                            <div class="no-reports-icon">📊</div>
                            <h4>No Reports Generated Yet</h4>
                            <p>Generate your first report using the form above to get started with analytics.</p>
                        </div>
                    <?php endif; ?>
                </div>                
            </div>
            <?php if($reports->hasPages()): ?>
                <div class="pagination-wrapper">
                    <?php echo $__env->make('components.custom-pagination', ['paginator' => $reports], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report View Modal -->
    <div id="reportModal" class="report-modal">
        <div class="report-modal-content">
            <div class="modal-header">
                <h3 id="modalReportTitle">Report Details</h3>
                <button class="modal-close" onclick="closeReportModal()">×</button>
            </div>
            <div class="modal-body" id="modalReportBody">
                <!-- Report content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
    // Form submission for generating reports
    document.getElementById('generateReportForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('.generate-btn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Generating...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/admin/reports/generate', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess('Report generated successfully!');
                location.reload(); // Refresh to show new report
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            adminError('An error occurred while generating the report.');
            console.error('Error:', error);
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // View report function
    async function viewReport(reportId) {
        try {
            const response = await fetch(`/admin/reports/${reportId}`);
            const html = await response.text();
            
            document.getElementById('modalReportBody').innerHTML = html;
            document.getElementById('reportModal').style.display = 'flex';
        } catch (error) {
            adminError('Error loading report details.');
            console.error('Error:', error);
        }
    }

    // Export report function
    function exportReport(reportId) {
        window.open(`/admin/reports/${reportId}/export`, '_blank');
    }

    // Delete report function
    async function deleteReport(reportId) {
        adminConfirm(
            'Are you sure you want to delete this report? This action cannot be undone.',
            'Confirm Deletion',
            () => performReportDeletion(reportId),
            () => console.log('Report deletion cancelled')
        );
        return;
    }

    async function performReportDeletion(reportId) {
        
        try {
            const response = await fetch(`/admin/reports/${reportId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess('Report deleted successfully!');
                location.reload();
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            adminError('An error occurred while deleting the report.');
            console.error('Error:', error);
        }
    }

    // Refresh reports function
    function refreshReports() {
        location.reload();
    }

    // Close modal function
    function closeReportModal() {
        document.getElementById('reportModal').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('reportModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReportModal();
        }
    });

    // Set default date range (last 30 days)
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
    });

    // Sorting functionality
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            const url = new URL(window.location.href);
            
            // Get current sort parameters
            const currentSort = url.searchParams.get('sort');
            const currentDirection = url.searchParams.get('direction') || 'asc';
            
            // Determine new direction
            let newDirection = 'asc';
            if (currentSort === sortBy && currentDirection === 'asc') {
                newDirection = 'desc';
            }
            
            // Set new sort parameters
            url.searchParams.set('sort', sortBy);
            url.searchParams.set('direction', newDirection);
            url.searchParams.delete('page'); // Reset to page 1 when sorting
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    </script>
    <?php echo $__env->make('admin.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\admin\admin_reports.blade.php ENDPATH**/ ?>