<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/hospital_logo.png') }}">
    <title>Reports</title>
    <link rel="stylesheet" href="{{asset('css/admincss/admin.css')}}">
    <link rel="stylesheet" href="{{asset('css/pagination.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @php
        $adminName = auth()->user()->name ?? 'Admin';
    @endphp
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <div class="main-content">
            <h2>Reports & Audits</h2>
            
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
                    <div class="stat-icon">🏥</div>
                    <div class="stat-content">
                        <h3>Total Patients Admitted</h3>
                        <p class="stat-number">{{ $stats['total_admitted_patients'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🛏️</div>
                    <div class="stat-content">
                        <h3>Currently Admitted</h3>
                        <p class="stat-number">{{ $stats['currently_admitted'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- View All Admitted Patients Card (replaces Generate New Report) -->
            <div class="admin-card">
                <h3>Print Admitted Patients</h3>
                <p>Show all patients who are admitted within Romel Cruz Hospital.</p>
                <p style="font-size: 1.0rem; font-style: italic;">You can view the list or open a printable report.</p>
                <div style="display:flex;gap:12px;margin-top:12px;flex-wrap:wrap;align-items:center;">
                    <form id="admittedFilterForm" method="GET" action="{{ route('admin.patients') }}" style="display:flex;gap:8px;align-items:center;">
                        <input type="hidden" name="filter" value="admitted">
                        <label for="periodSelect" style="font-weight:600; margin-right:6px;">Period</label>
                        <select id="periodSelect" name="period" style="padding:6px;border-radius:4px;border:1px solid #ccc;">
                            <option value="">All time</option>
                            <optgroup label="Past">
                                <option value="past_year">Past Year</option>
                                <option value="past_month">Past Month</option>
                                <option value="past_week">Past Week</option>
                            </optgroup>
                            <optgroup label="This">
                                <option value="this_year">This Year</option>
                                <option value="this_month">This Month</option>
                                <option value="this_week">This Week</option>
                            </optgroup>
                        </select>

                        <button type="submit" class="period-btn view" style="width:auto;padding:8px 12px;">View Admitted Patients</button>

                        <button type="button" class="period-btn print" id="printAdmittedBtn" style="width:auto;padding:8px 12px;">Print Admitted Patients</button>
                    </form>
                </div>
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
                                    <th class="sortable" data-sort="title">
                                        Title 
                                        @if(request('sort') === 'title')
                                            <span class="sort-indicator">{{ request('direction') === 'desc' ? '▼' : '▲' }}</span>
                                        @endif
                                    </th>
                                    <th class="sortable" data-sort="type">
                                        Type 
                                        @if(request('sort') === 'type')
                                            <span class="sort-indicator">{{ request('direction') === 'desc' ? '▼' : '▲' }}</span>
                                        @endif
                                    </th>
                                    <th class="sortable" data-sort="status">
                                        Status 
                                        @if(request('sort') === 'status')
                                            <span class="sort-indicator">{{ request('direction') === 'desc' ? '▼' : '▲' }}</span>
                                        @endif
                                    </th>
                                    <th class="sortable" data-sort="generated_by">
                                        Generated By 
                                        @if(request('sort') === 'generated_by')
                                            <span class="sort-indicator">{{ request('direction') === 'desc' ? '▼' : '▲' }}</span>
                                        @endif
                                    </th>
                                    <th class="sortable" data-sort="generated_at">
                                        Date 
                                        @if(request('sort') === 'generated_at')
                                            <span class="sort-indicator">{{ request('direction') === 'desc' ? '▼' : '▲' }}</span>
                                        @endif
                                    </th>
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
                                            <button class="view-btn" onclick="viewReport({{ $report->id }})" title="View Report">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="edit-btn" onclick="exportReport({{ $report->id }})" title="Export Report">
                                                <i class="fas fa-download"></i> Export
                                            </button>
                                            <button class="delete-btn" onclick="deleteReport({{ $report->id }})" title="Delete Report">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-reports">
                            <div class="no-reports-icon">📊</div>
                            <h4>No Reports Generated Yet</h4>
                            <p>Generate your first report using the form above to get started with analytics.</p>
                        </div>
                    @endif
                </div>                
            </div>
            @if($reports->hasPages())
                <div class="pagination-wrapper">
                    @include('components.custom-pagination', ['paginator' => $reports])
                </div>
            @endif
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
    // Form submission for generating reports (guard presence)
    (function() {
        const genForm = document.getElementById('generateReportForm');
        if (!genForm) return;

        genForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = genForm;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('.generate-btn');
            const originalText = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Generating...';
                submitBtn.disabled = true;
            }

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
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
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }
        });
    })();

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

    // Export report function (opens printable view)
    function exportReport(reportId) {
        window.open(`/admin/reports/${reportId}/export?format=print`, '_blank');
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
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

    // Open printable admitted patients with selected period
    document.addEventListener('DOMContentLoaded', function() {
        const printBtn = document.getElementById('printAdmittedBtn');
        if (!printBtn) return;
        printBtn.addEventListener('click', function() {
            const period = document.getElementById('periodSelect')?.value || '';
            let url = '{{ route('admin.patients') }}?filter=admitted&print=1';
            if (period) url += '&period=' + encodeURIComponent(period);
            window.open(url, '_blank');
        });
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
    @include('admin.modals.notification_system')

    <style>
    .report-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
        align-items: center;
    }

    .report-actions .view-btn,
    .report-actions .edit-btn,
    .report-actions .delete-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-width: auto;
        white-space: nowrap;
    }

    .report-actions .view-btn {
        background: #28a745;
        color: #fff;
        box-shadow: 0 2px 6px 0 rgba(40, 167, 69, 0.10);
    }

    .report-actions .view-btn:hover {
        background: #218838;
    }

    .report-actions .edit-btn {
        background: #28a745;
        color: #fff;
        box-shadow: 0 2px 6px 0 rgba(40, 167, 69, 0.10);
    }

    .report-actions .edit-btn:hover {
        background: #218838;
    }

    .report-actions .delete-btn {
        background: #dc3545;
        color: #fff;
        box-shadow: 0 2px 6px 0 rgba(220, 53, 69, 0.10);
    }

    .report-actions .delete-btn:hover {
        background: #c82333;
    }

    .report-actions i {
        font-size: 0.75rem;
    }
    /* Period/action buttons on Reports card */
    .period-btn {
        border: none;
        border-radius: 4px;
        color: #fff;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .period-btn.view {
        background: #28a745; /* green */
        box-shadow: 0 2px 6px 0 rgba(40,167,69,0.10);
    }
    .period-btn.view:hover { background: #218838; }
    .period-btn.print {
        background: #007bff; /* blue */
        box-shadow: 0 2px 6px 0 rgba(0,123,255,0.08);
    }
    .period-btn.print:hover { background: #0069d9; }
    </style>
</body>
</html>