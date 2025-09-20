<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Orders</title>
    <link rel="stylesheet" href="{{ url('css/labtech.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $labtechName = auth()->user()->name ?? 'Lab Technician';
    @endphp
    @include('labtech.labtech_header')

    <div class="labtech-layout">
        @include('labtech.labtech_sidebar')

        <main class="main-content">
            <h2>Lab Orders</h2>
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn active" data-status="all">All Orders</button>
                <button class="tab-btn" data-status="pending">Pending</button>
                <button class="tab-btn" data-status="in_progress">In Progress</button>
                <button class="tab-btn" data-status="completed">Completed</button>
            </div>

            <div class="labtech-card">
                @if($orders->count() > 0)
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Patient</th>
                                    <th>Tests Requested</th>
                                    <th>Requested By</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr class="order-row" data-status="{{ $order->status }}">
                                    <td class="order-id">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="patient-info">
                                        <strong>{{ $order->patient_name }}</strong><br>
                                        <small>ID: {{ $order->patient_no }}</small>
                                    </td>
                                    <td class="test-info">
                                        <div class="test-details">{{ $order->test_requested }}</div>
                                        @if($order->notes)
                                            <small class="notes">Notes: {{ $order->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="requester">{{ $order->requestedBy->name }}</td>
                                    <td class="priority">
                                        <span class="priority-badge priority-{{ $order->priority }}">
                                            {{ ucfirst($order->priority) }}
                                        </span>
                                    </td>
                                    <td class="status">
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td class="requested-at">
                                        {{ $order->requested_at->format('M d, Y') }}<br>
                                        <small>{{ $order->requested_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="actions">
                                        @if($order->status === 'pending')
                                            <button class="btn start-btn" onclick="updateStatus({{ $order->id }}, 'in_progress')">
                                                Start
                                            </button>
                                        @elseif($order->status === 'in_progress')
                                            <button class="btn complete-btn" onclick="showCompleteModal({{ $order->id }})">
                                                Complete
                                            </button>
                                        @endif
                                        
                                        @if($order->status === 'completed' && $order->results_pdf_path)
                                            <button class="btn view-pdf-btn" onclick="viewPdf({{ $order->id }})">
                                                <i class="fas fa-file-pdf"></i> View PDF
                                            </button>
                                        @endif
                                        
                                        <button class="btn view-btn" onclick="viewOrder({{ $order->id }})">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-wrapper">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="no-orders">
                        <i class="fas fa-flask"></i>
                        <h3>No Lab Orders</h3>
                        <p>No laboratory orders have been requested yet.</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Complete Order Modal -->
    <div id="completeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Complete Lab Order</h3>
            <form id="completeForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="results">Test Results:</label>
                    <textarea id="results" name="results" rows="4" placeholder="Enter test results summary or notes..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="resultsPdf">Upload Results (PDF): *</label>
                    <div class="file-upload-container">
                        <input type="file" id="resultsPdf" name="results_pdf" accept=".pdf" required>
                        <small class="file-hint">Upload the lab results as PDF (converted from X-ray images)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="sendToDoctor" name="send_to_doctor" checked>
                        Send results to requesting doctor
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn cancel-btn" onclick="closeCompleteModal()">Cancel</button>
                    <button type="submit" class="btn complete-btn">
                        <span class="btn-text">Complete Order</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Uploading...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div id="pdfModal" class="modal">
        <div class="modal-content pdf-modal">
            <span class="close" onclick="closePdfModal()">&times;</span>
            <h3 id="pdfModalTitle">Lab Results PDF</h3>
            <div class="pdf-controls">
                <button class="btn download-btn" id="downloadPdfBtn">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
            <div class="pdf-viewer">
                <iframe id="pdfFrame" src="" width="100%" height="600px" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeOrderDetailsModal()">&times;</span>
            <h3>Order Details</h3>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let currentOrderId = null;

        // Tab filtering
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const status = this.dataset.status;
                
                // Update active tab
                document.querySelectorAll('.tab-btn').forEach(tab => tab.classList.remove('active'));
                this.classList.add('active');
                
                // Filter rows
                document.querySelectorAll('.order-row').forEach(row => {
                    if (status === 'all' || row.dataset.status === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
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
                    alert('Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        }

        function showCompleteModal(orderId) {
            currentOrderId = orderId;
            document.getElementById('completeModal').classList.add('show');
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').classList.remove('show');
            document.getElementById('results').value = '';
            document.getElementById('resultsPdf').value = '';
            document.getElementById('sendToDoctor').checked = true;
            currentOrderId = null;
        }

        // Complete form submission
        document.getElementById('completeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.complete-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Check if PDF is uploaded
            const pdfFile = document.getElementById('resultsPdf').files[0];
            if (!pdfFile) {
                alert('Please upload a PDF file with the test results.');
                return;
            }
            
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
                    location.reload();
                } else {
                    alert('Error completing order: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error completing order. Please try again.');
            })
            .finally(() => {
                // Reset button state
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            });
        });

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
                        alert('PDF not available for this order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading PDF');
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
                        alert('Error loading order details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order details');
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
            
            if (event.target === completeModal) {
                closeCompleteModal();
            } else if (event.target === pdfModal) {
                closePdfModal();
            } else if (event.target === orderDetailsModal) {
                closeOrderDetailsModal();
            }
        }

        // Close modal with X button
        document.querySelector('.close').onclick = closeCompleteModal;
    </script>
</body>
</html>
