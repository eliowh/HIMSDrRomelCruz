<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports</title>
    <link rel="stylesheet" href="{{url('css/admin.css')}}">
</head>
<body>
    @php
        $adminName = auth()->user()->name ?? 'Admin';
    @endphp
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <div class="main-content">
            <h2>Reports & Analytics</h2>
            
            <!-- Report Statistics -->
            <div class="report-stats">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>Total Reports</h3>
                        <p class="stat-number">{{ $stats['total_reports'] ?? 0 }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3>Pending</h3>
                        <p class="stat-number">{{ $stats['pending_reports'] ?? 0 }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3>Completed</h3>
                        <p class="stat-number">{{ $stats['completed_reports'] ?? 0 }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <h3>Failed</h3>
                        <p class="stat-number">{{ $stats['failed_reports'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Generate New Report Card -->
            <div class="admin-card">
                <h3>Generate New Report</h3>
                <form id="generateReportForm" class="report-form">
                    @csrf
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
                    @if(isset($reports) && $reports->count() > 0)
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Generated By</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $report)
                                <tr>
                                    <td class="report-title">{{ $report->title }}</td>
                                    <td>
                                        <span class="type-badge type-{{ $report->type }}">
                                            {{ $report->formatted_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $report->status }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $report->generatedBy->name ?? 'System' }}</td>
                                    <td>{{ $report->generated_at ? $report->generated_at->format('M d, Y g:i A') : 'N/A' }}</td>
                                    <td>
                                        <div class="report-actions">
                                            <button class="action-btn small" onclick="viewReport({{ $report->id }})" title="View Report">
                                                👁️
                                            </button>
                                            <button class="action-btn small" onclick="exportReport({{ $report->id }})" title="Export Report">
                                                📥
                                            </button>
                                            <button class="action-btn small delete" onclick="deleteReport({{ $report->id }})" title="Delete Report">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @if($reports->hasPages())
                        <div class="pagination-wrapper">
                            {{ $reports->links() }}
                        </div>
                        @endif
                    @else
                        <div class="no-reports">
                            <div class="no-reports-icon">📊</div>
                            <h4>No Reports Generated Yet</h4>
                            <p>Generate your first report using the form above to get started with analytics.</p>
                        </div>
                    @endif
                </div>
            </div>
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
            const response = await fetch('{{ route("admin.reports.generate") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Report generated successfully!');
                location.reload(); // Refresh to show new report
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred while generating the report.');
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
            alert('Error loading report details.');
            console.error('Error:', error);
        }
    }

    // Export report function
    function exportReport(reportId) {
        window.open(`/admin/reports/${reportId}/export`, '_blank');
    }

    // Delete report function
    async function deleteReport(reportId) {
        if (!confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/reports/${reportId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Report deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred while deleting the report.');
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
    </script>
</body>
</html>