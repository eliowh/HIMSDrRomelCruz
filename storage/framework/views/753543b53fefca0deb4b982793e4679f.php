<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Orders</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/labtechcss/labtech.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <!-- Modal styles moved to labtech.css -->
</head>
<body>
    <?php
        $labtechName = auth()->user()->name ?? 'Lab Technician';
    ?>
    <?php echo $__env->make('labtech.labtech_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="labtech-layout">
        <?php echo $__env->make('labtech.labtech_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <div class="page-header">
                <h2><i class="fas fa-flask"></i> Lab Orders</h2>
                <div class="page-subtitle">Manage laboratory test orders and results</div>
            </div>
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn <?php echo e($status === 'pending' ? 'active' : ''); ?>" data-status="pending">
                    <i class="fas fa-clock"></i> Pending <span class="count-badge"><?php echo e($statusCounts['pending']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'in_progress' ? 'active' : ''); ?>" data-status="in_progress">
                    <i class="fas fa-spinner"></i> In Progress <span class="count-badge"><?php echo e($statusCounts['in_progress']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'completed' ? 'active' : ''); ?>" data-status="completed">
                    <i class="fas fa-check-circle"></i> Completed <span class="count-badge"><?php echo e($statusCounts['completed']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'cancelled' ? 'active' : ''); ?>" data-status="cancelled">
                    <i class="fas fa-times-circle"></i> Cancelled <span class="count-badge"><?php echo e($statusCounts['cancelled']); ?></span>
                </button>
            </div>

            <div class="labtech-card">
                <?php if($orders->count() > 0): ?>
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="order-id">
                                        Order ID <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th>Patient</th>
                                    <th>Tests Requested</th>
                                    <th class="sortable" data-sort="requester">
                                        Requested By <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="priority">
                                        Priority <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="status">
                                        Status <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="requested-at">
                                        Requested At <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="order-row" data-status="<?php echo e($order->status); ?>">
                                    <td class="order-id" data-value="<?php echo e($order->id); ?>">#<?php echo e(str_pad($order->id, 4, '0', STR_PAD_LEFT)); ?></td>
                                    <td class="patient-info">
                                        <strong><?php echo e($order->patient_name); ?></strong><br>
                                        <small>ID: <?php echo e($order->patient_no); ?></small>
                                    </td>
                                    <td class="test-info">
                                        <div class="test-details"><?php echo e($order->test_requested); ?></div>
                                        <?php if($order->notes): ?>
                                            <small class="notes">Notes: <?php echo e($order->notes); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="requester" data-value="<?php echo e($order->requestedBy->name); ?>"><?php echo e($order->requestedBy->name); ?></td>
                                    <td class="priority" data-value="<?php echo e($order->priority); ?>">
                                        <span class="priority-badge priority-<?php echo e($order->priority); ?>">
                                            <?php echo e(ucfirst($order->priority)); ?>

                                        </span>
                                    </td>
                                    <td class="status" data-value="<?php echo e($order->status); ?>">
                                        <span class="status-badge status-<?php echo e($order->status); ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $order->status))); ?>

                                        </span>
                                    </td>
                                    <td class="requested-at" data-value="<?php echo e($order->requested_at->timestamp); ?>">
                                        <?php echo e($order->requested_at->format('M d, Y')); ?><br>
                                        <small><?php echo e($order->requested_at->format('h:i A')); ?></small>
                                    </td>
                                    <td class="actions">
                                        <?php if($order->status === 'pending'): ?>
                                            <button class="btn start-btn" onclick="updateStatus(<?php echo e($order->id); ?>, 'in_progress')">
                                                Confirm
                                            </button>
                                        <?php elseif($order->status === 'in_progress'): ?>
                                            <button class="btn complete-btn" onclick="showCompleteModal(<?php echo e($order->id); ?>)">
                                                Proceed
                                            </button>
                                            <button class="btn cancel-btn" onclick="cancelOrder(<?php echo e($order->id); ?>)">
                                                Cancel
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($order->status === 'completed' && $order->results_pdf_path): ?>
                                            <button class="btn view-pdf-btn" onclick="viewPdf(<?php echo e($order->id); ?>)">
                                                <i class="fas fa-file-pdf"></i> View PDF
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn view-btn" onclick="viewOrder(<?php echo e($order->id); ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty state placeholders for filtered tabs -->
                    <div id="empty-pending" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-clock"></i>
                        <h3>No Pending Orders</h3>
                        <p>There are currently no pending lab orders to display.</p>
                    </div>
                    
                    <div id="empty-in-progress" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <h3>No In Progress Orders</h3>
                        <p>There are currently no lab orders in progress.</p>
                    </div>
                    
                    <div id="empty-completed" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <h3>No Completed Orders</h3>
                        <p>There are currently no completed lab orders to display.</p>
                    </div>
                    
                    <div id="empty-cancelled" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-ban"></i>
                        <h3>No Cancelled Orders</h3>
                        <p>There are currently no cancelled lab orders to display.</p>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-flask"></i>
                        <h3>No Lab Orders</h3>
                        <p>No laboratory orders have been requested yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-wrapper" id="pagination-container" style="<?php echo e($orders->count() > 0 ? 'display: flex;' : 'display: none;'); ?>">
                <?php if($orders->count() > 0): ?>
                    <?php echo e($orders->appends(['status' => request('status')])->links('components.custom-pagination')); ?>

                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Complete Order Modal -->
    <?php echo $__env->make('labtech.modals.complete_order_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Template Selection & Generation Modal -->
    <?php echo $__env->make('labtech.modals.template_select_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- PDF Viewer Modal -->
    <?php echo $__env->make('labtech.modals.pdf_viewer_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Order Details Modal -->
    <?php echo $__env->make('labtech.modals.order_details_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <!-- Cancellation Reason Modal -->
    <?php echo $__env->make('labtech.modals.cancel_reason_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <!-- Notification System -->
    <?php echo $__env->make('labtech.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        let currentOrderId = null;
        let currentSort = { column: null, direction: 'asc' };
        let currentStatus = 'pending';

        // Initialize on document ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeFromURL();
            
            // Set up sortable columns
            setupSortableColumns();
            
            // Handle browser back/forward navigation
            window.addEventListener('popstate', function() {
                initializeFromURL();
            });
            
            // Set up cancel reason form submission
            document.getElementById('cancelReasonForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const reason = document.getElementById('cancelReason').value.trim();
                if (reason) {
                    processCancellation(reason);
                } else {
                    showLabtechWarning('Please provide a reason for cancellation.', 'Required Information');
                    document.getElementById('cancelReason').focus();
                }
            });
        });
        
        // Function to initialize the view based on the URL parameters
        function initializeFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            let statusParam = urlParams.has('status') ? urlParams.get('status') : 'pending';
            
            // Validate status parameter
            if(!['pending', 'in_progress', 'completed', 'cancelled'].includes(statusParam)) {
                statusParam = 'pending';
            }
            
            currentStatus = statusParam;
            
            // Set the correct tab as active
            document.querySelectorAll('.tab-btn').forEach(tab => {
                tab.classList.remove('active');
                if(tab.dataset.status === statusParam) {
                    tab.classList.add('active');
                }
            });
            
            // Apply the filter
            filterByStatus(statusParam);
            
            // Update pagination links to maintain current status
            updatePaginationLinks();
        }
        
        // Function to update pagination links to maintain the current status filter
        function updatePaginationLinks() {
            // Update links in custom pagination component
            document.querySelectorAll('#pagination-container a').forEach(link => {
                if (link.href) {
                    const linkUrl = new URL(link.href);
                    
                    // Add status parameter to pagination links
                    linkUrl.searchParams.set('status', currentStatus);
                    
                    link.href = linkUrl.toString();
                }
            });
            
            // Also update the page input handling in custom pagination
            const pageInputs = document.querySelectorAll('.page-input');
            if (pageInputs.length > 0) {
                // Replace the default goToPage function if it exists
                if (typeof window.goToPage === 'function') {
                    window.originalGoToPage = window.goToPage;
                    window.goToPage = function(page) {
                        const currentUrl = new URL(window.location.href);
                        const pageInput = document.querySelector('.page-input');
                        
                        // Get max page from the pagination component
                        const maxPage = parseInt(pageInput.getAttribute('max') || 1);
                        
                        // Validate page number
                        page = parseInt(page);
                        if (page < 1) page = 1;
                        if (page > maxPage) page = maxPage;
                        
                        // Update input value to corrected page
                        if (pageInput) pageInput.value = page;
                        
                        // Set the page and maintain current status
                        currentUrl.searchParams.set('page', page);
                        currentUrl.searchParams.set('status', currentStatus);
                        
                        window.location.href = currentUrl.toString();
                    };
                }
            }
        }

        // Tab filtering with loading indicator
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const status = this.dataset.status;
                
                // Only redirect if status changed
                if (status !== currentStatus) {
                    // Add loading state
                    this.disabled = true;
                    this.innerHTML = this.innerHTML.replace('</span>', '</span> <i class="fas fa-spinner fa-spin"></i>');

                    // Build the new URL - reset to page 1 when changing tabs
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.delete('page'); // Reset to page 1
                    currentUrl.searchParams.set('status', status);
                    
                    // Navigate to the new URL
                    window.location.href = currentUrl.toString();
                }
            });
        });

        function updateStatus(orderId, status) {
            fetch(`/labtech/orders/update-status/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showLabtechError('Failed to update order status. Please try again.', 'Status Update Failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showLabtechError('Network error occurred. Please check your connection and try again.', 'Connection Error');
            });
        }

        function showCompleteModal(orderId) {
            currentOrderId = orderId;
            // Expose globally for template modal button
            window.__activeLabOrderId = orderId;
            document.getElementById('completeModal').classList.add('show');
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').classList.remove('show');
            document.getElementById('results').value = '';
            document.getElementById('resultsPdf').value = '';
            currentOrderId = null;
        }

        // Complete form submission
        document.getElementById('completeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.complete-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            // Add status and order ID to form data
            formData.append('status', 'completed');
            formData.append('order_id', currentOrderId);
            
            fetch(`/labtech/orders/update-status/${currentOrderId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCompleteModal();
                    showLabtechSuccess('Order has been completed successfully!', 'Order Completed', () => {
                        location.reload();
                    });
                } else {
                    showLabtechError(data.message || 'Failed to complete order', 'Completion Failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showLabtechError('Network error occurred. Please try again.', 'Connection Error');
            })
            .finally(() => {
                // Reset button state
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            });
        });

        // Cancel order function - Show modal instead of confirm
        function cancelOrder(orderId) {
            // Store the order ID for the cancellation process
            currentOrderId = orderId;
            
            // Clear any previous reasons
            document.getElementById('cancelReason').value = '';
            
            // Show the cancellation reason modal
            document.getElementById('cancelReasonModal').classList.add('show');
        }
        
        // Close cancellation reason modal
        function closeCancelReasonModal() {
            document.getElementById('cancelReasonModal').classList.remove('show');
            currentOrderId = null;
        }
        
        // Function to actually process the cancellation with reason
        function processCancellation(reason) {
            if (!currentOrderId) return;
            
            const orderId = currentOrderId;
            const submitBtn = document.querySelector('#cancelReasonForm .complete-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            fetch(`/labtech/orders/update-status/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    status: 'cancelled',
                    cancel_reason: reason 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCancelReasonModal();
                    showLabtechSuccess('Order has been cancelled successfully.', 'Order Cancelled', () => {
                        location.reload();
                    });
                } else {
                    showLabtechError(data.message || 'Failed to cancel order', 'Cancellation Failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showLabtechError('Network error occurred. Please try again.', 'Connection Error');
            })
            .finally(() => {
                // Reset button state
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            });
        }

        // PDF Viewing Functions
        function viewPdf(orderId) {
            fetch(`/labtech/orders/view/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.pdf_url) {
                        document.getElementById('pdfModalTitle').textContent = 
                            `Lab Results - ${data.order.patient_name} (ID: ${data.order.patient_no})`;
                        document.getElementById('pdfFrame').src = data.pdf_url;
                        document.getElementById('downloadPdfBtn').onclick = () => downloadPdf(orderId);
                        document.getElementById('pdfModal').classList.add('show');
                    } else {
                        showLabtechWarning('PDF results are not available for this order yet.', 'PDF Not Available');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showLabtechError('Failed to load PDF. Please try again.', 'PDF Loading Error');
                });
        }

        function closePdfModal() {
            document.getElementById('pdfModal').classList.remove('show');
            document.getElementById('pdfFrame').src = '';
        }

        function downloadPdf(orderId) {
            window.open(`/labtech/orders/download-pdf/${orderId}`, '_blank');
        }

        // Order Details Functions
        function viewOrder(orderId) {
            fetch(`/labtech/orders/view/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        const detailsHtml = `
                            <div class="order-details">
                                <div class="detail-group">
                                    <h4>Patient Information</h4>
                                    <p><strong>Name:</strong> ${order.patient_name}</p>
                                    <p><strong>Patient No:</strong> ${order.patient_no}</p>
                                </div>
                                
                                <div class="detail-group">
                                    <h4>Test Information</h4>
                                    <p><strong>Test Requested:</strong> ${order.test_requested}</p>
                                    <p><strong>Priority:</strong> <span class="priority-badge priority-${order.priority}">${order.priority.toUpperCase()}</span></p>
                                    <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.replace('_', ' ').toUpperCase()}</span></p>
                                    ${order.notes ? `<p><strong>Notes:</strong> ${order.notes}</p>` : ''}
                                </div>
                                
                                <div class="detail-group">
                                    <h4>Timeline</h4>
                                    <p><strong>Requested:</strong> ${new Date(order.requested_at).toLocaleString()}</p>
                                    <p><strong>Requested By:</strong> ${order.requested_by.name}</p>
                                    ${order.started_at ? `<p><strong>Started:</strong> ${new Date(order.started_at).toLocaleString()}</p>` : ''}
                                    ${order.completed_at ? `<p><strong>Completed:</strong> ${new Date(order.completed_at).toLocaleString()}</p>` : ''}
                                    ${order.lab_tech ? `<p><strong>Lab Tech:</strong> ${order.lab_tech.name}</p>` : ''}
                                </div>
                                
                                ${order.status === 'cancelled' && order.cancel_reason ? `
                                <div class="detail-group">
                                    <h4>Cancellation Information</h4>
                                    <p><strong>Reason for Cancellation:</strong></p>
                                    <div class="cancel-reason">${order.cancel_reason}</div>
                                    <p><strong>Cancelled At:</strong> ${order.cancelled_at ? new Date(order.cancelled_at).toLocaleString() : 'Unknown'}</p>
                                </div>
                                ` : ''}
                                
                                ${order.results ? `
                                <div class="detail-group">
                                    <h4>Results</h4>
                                    <p>${order.results}</p>
                                </div>
                                ` : ''}
                                
                                ${data.pdf_url ? `
                                <div class="detail-group">
                                    <h4>Results PDF</h4>
                                    <button class="btn view-pdf-btn" onclick="viewPdf(${order.id})">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </button>
                                    <button class="btn download-btn" onclick="downloadPdf(${order.id})">
                                        <i class="fas fa-download"></i> Download PDF
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                        `;
                        
                        document.getElementById('orderDetailsContent').innerHTML = detailsHtml;
                        document.getElementById('orderDetailsModal').classList.add('show');
                    } else {
                        showLabtechError('Failed to load order details. Please try again.', 'Loading Failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showLabtechError('Network error occurred while loading order details.', 'Connection Error');
                });
        }

        function closeOrderDetailsModal() {
            document.getElementById('orderDetailsModal').classList.remove('show');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const completeModal = document.getElementById('completeModal');
            const pdfModal = document.getElementById('pdfModal');
            const orderDetailsModal = document.getElementById('orderDetailsModal');
            const cancelReasonModal = document.getElementById('cancelReasonModal');
            
            if (event.target === completeModal) {
                closeCompleteModal();
            } else if (event.target === pdfModal) {
                closePdfModal();
            } else if (event.target === orderDetailsModal) {
                closeOrderDetailsModal();
            } else if (event.target === cancelReasonModal) {
                closeCancelReasonModal();
            }
        }

        // Close modal with X buttons
        document.querySelectorAll('.close').forEach(closeBtn => {
            const modalParent = closeBtn.closest('.modal');
            if (modalParent) {
                if (modalParent.id === 'completeModal') {
                    closeBtn.onclick = closeCompleteModal;
                } else if (modalParent.id === 'pdfModal') {
                    closeBtn.onclick = closePdfModal;
                } else if (modalParent.id === 'orderDetailsModal') {
                    closeBtn.onclick = closeOrderDetailsModal;
                } else if (modalParent.id === 'cancelReasonModal') {
                    closeBtn.onclick = closeCancelReasonModal;
                }
            }
        });
        
        // Function to set up sortable columns
        function setupSortableColumns() {
            document.querySelectorAll('th.sortable').forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.dataset.sort;
                    
                    // Toggle sort direction or set initial direction
                    if(currentSort.column === column) {
                        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = column;
                        currentSort.direction = 'asc';
                    }
                    
                    // Update sort icons
                    document.querySelectorAll('.sort-icon i').forEach(icon => {
                        icon.className = 'fas fa-sort';
                    });
                    
                    const thisIcon = this.querySelector('.sort-icon i');
                    thisIcon.className = currentSort.direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                    
                    // Sort table rows
                    sortTable(column, currentSort.direction);
                });
            });
        }
        
        // Function to sort table
        function sortTable(column, direction) {
            const tbody = document.querySelector('.orders-table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Sort rows
            rows.sort((a, b) => {
                const aCell = a.querySelector('.' + column);
                const bCell = b.querySelector('.' + column);
                
                if(!aCell || !bCell) return 0;
                
                // Get sorting values - first try data-value attribute, then cell text
                let aValue = aCell.getAttribute('data-value');
                let bValue = bCell.getAttribute('data-value');
                
                if(!aValue) aValue = aCell.textContent.trim();
                if(!bValue) bValue = bCell.textContent.trim();
                
                // Check if values are numbers
                const aNum = parseFloat(aValue);
                const bNum = parseFloat(bValue);
                
                if(!isNaN(aNum) && !isNaN(bNum)) {
                    return direction === 'asc' ? aNum - bNum : bNum - aNum;
                }
                
                // Otherwise compare as strings
                return direction === 'asc' ? 
                    aValue.localeCompare(bValue) : 
                    bValue.localeCompare(aValue);
            });
            
            // Re-append rows in the sorted order
            rows.forEach(row => tbody.appendChild(row));
            
            // Re-check visibility based on current filter
            filterByStatus(currentStatus);
        }
        
        // Function to filter by status
        function filterByStatus(status) {
            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;
            const table = document.querySelector('.orders-table');
            const paginationContainer = document.getElementById('pagination-container');
            
            // Hide all placeholders first
            document.querySelectorAll('.empty-state-placeholder').forEach(placeholder => {
                placeholder.style.display = 'none';
            });
            
            // Show table by default
            if (table) {
                table.style.display = '';
            }
            
            // Filter the rows
            rows.forEach(row => {
                if(row.dataset.status === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show empty state placeholder if needed
            if(visibleCount === 0) {
                const emptyStateId = `empty-${status.replace('_', '-')}`;
                const emptyState = document.getElementById(emptyStateId);
                
                if(emptyState) {
                    if(table) table.style.display = 'none';
                    emptyState.style.display = 'block';
                    
                    // Hide pagination for empty tabs
                    if(paginationContainer) {
                        paginationContainer.style.display = 'none';
                    }
                } else {
                    // Show pagination for non-empty tabs
                    if(paginationContainer) {
                        paginationContainer.style.display = visibleCount > 0 ? 'flex' : 'none';
                    }
                }
            } else {
                // Show pagination for non-empty tabs
                if(paginationContainer) {
                    paginationContainer.style.display = visibleCount > 0 ? 'flex' : 'none';
                }
            }
            
            return visibleCount;
        }
        
        // Function to check if we need to display empty states
        function checkEmptyStates() {
            // Run filterByStatus to show/hide appropriate content based on current status
            filterByStatus(currentStatus);
        }

        // Check for highlight parameter on page load
        document.addEventListener('DOMContentLoaded', function() {
            const highlightTestId = sessionStorage.getItem('highlightTestId');
            if (highlightTestId) {
                // Clear the highlight ID from session storage
                sessionStorage.removeItem('highlightTestId');
                
                // Find and highlight the order row
                const orderRows = document.querySelectorAll('.order-row');
                orderRows.forEach(row => {
                    const orderIdCell = row.querySelector('.order-id');
                    if (orderIdCell) {
                        const orderId = orderIdCell.getAttribute('data-value');
                        if (orderId == highlightTestId) {
                            // Highlight the row
                            row.style.backgroundColor = '#fff3cd';
                            row.style.border = '2px solid #ffc107';
                            row.style.boxShadow = '0 0 10px rgba(255, 193, 7, 0.5)';
                            
                            // Scroll to the row
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            // Remove highlight after 5 seconds
                            setTimeout(() => {
                                row.style.backgroundColor = '';
                                row.style.border = '';
                                row.style.boxShadow = '';
                            }, 5000);
                            
                            return;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\labtech\labtech_orders.blade.php ENDPATH**/ ?>