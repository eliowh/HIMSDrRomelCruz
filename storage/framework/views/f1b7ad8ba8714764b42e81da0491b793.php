<?php $__env->startSection('title','Medicine Request History'); ?>

<?php $__env->startSection('content'); ?>
<?php $requests = $requests ?? collect(); $q = $q ?? ''; ?>

    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/nurse_patients.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/pharmacycss/pharmacy.css')); ?>">

<div class="patients-grid" style="display: flex">
    <div class="list-column" style="width: 100%;">
        <div class="inventory-card">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <h2 style="margin:0;">Medicine Request History</h2>
                <form method="GET" style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                    <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search requests..." style="padding:8px 10px;border:1px solid #ddd;border-radius:6px; height: 40px" />
                    <button type="submit" class="action-btn primary">Search</button>
                    <select name="status" onchange="this.form.submit()" style="padding:8px 10px;border:1px solid #ddd;border-radius:6px; height: 40px">
                        <option value="all" <?php echo e(request('status') == 'all' ? 'selected' : ''); ?>>All History</option>
                        <option value="dispensed" <?php echo e(request('status') == 'dispensed' ? 'selected' : ''); ?>>Dispensed</option>
                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    </select>
                    <a href="<?php echo e(route('nurse.patients.index')); ?>" class="action-btn secondary" style="margin-left:8px;text-decoration:none;">Back to Patients</a>
                </form>
            </div>

            <?php if($requests->count()): ?>
                <div class="table-wrap">
                    <table class="patients-table" style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Request ID</th>
                                <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Patient</th>
                                <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Medicine</th>
                                <th style="text-align:center;padding:10px;border-bottom:1px solid #eee;">Quantity</th>
                                <th style="text-align:right;padding:10px;border-bottom:1px solid #eee;">Total Price</th>
                                <th style="text-align:center;padding:10px;border-bottom:1px solid #eee;">Status</th>
                                <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Requested Date</th>
                                <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Processed Date</th>
                                <th style="text-align:center;padding:10px;border-bottom:1px solid #eee;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;">#<?php echo e($request->id); ?></td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;">
                                    <div>
                                        <strong><?php echo e($request->patient_name ?? '-'); ?></strong>
                                        <?php if($request->patient_no): ?>
                                            <br><small style="color:#666;"><?php echo e($request->patient_no); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;">
                                    <div>
                                        <strong><?php echo e($request->generic_name ?: $request->brand_name ?: '-'); ?></strong>
                                        <?php if($request->item_code): ?>
                                            <br><small style="color:#666;"><?php echo e($request->item_code); ?></small>
                                        <?php endif; ?>
                                        <?php if($request->generic_name && $request->brand_name): ?>
                                            <br><small style="color:#666;"><?php echo e($request->brand_name); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:center;"><?php echo e($request->quantity); ?></td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:right;">
                                    ₱<?php echo e(number_format($request->total_price ?? 0, 2)); ?>

                                </td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:center;">
                                    <span class="status-badge status-<?php echo e($request->status); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $request->status))); ?>

                                    </span>
                                </td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;">
                                    <?php echo e($request->requested_at ? $request->requested_at->format('M d, Y h:i A') : '-'); ?>

                                </td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;">
                                    <?php if($request->status === 'dispensed' && $request->dispensed_at): ?>
                                        <?php echo e($request->dispensed_at->format('M d, Y h:i A')); ?>

                                        <?php if($request->dispensedBy): ?>
                                            <br><small style="color:#666;">by <?php echo e($request->dispensedBy->name); ?></small>
                                        <?php endif; ?>
                                    <?php elseif($request->status === 'cancelled' && $request->cancelled_at): ?>
                                        <?php echo e($request->cancelled_at->format('M d, Y h:i A')); ?>

                                        <?php if($request->cancelledBy): ?>
                                            <br><small style="color:#666;">by <?php echo e($request->cancelledBy->name); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:center;">
                                    <button type="button" class="btn view-btn" onclick="viewRequestDetails(<?php echo e($request->id); ?>)">View Details</button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($requests->hasPages()): ?>
                    <div class="pagination-wrapper" style="margin-top:20px;">
                        <?php echo e($requests->appends(request()->query())->links('components.custom-pagination')); ?>

                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <h3>No Request History</h3>
                    <p>No dispensed or cancelled medicine requests found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div id="requestDetailsModal" class="modal mini">
    <div class="modal-content">
        <span class="close" onclick="closeRequestDetailsModal()">&times;</span>
        <h3>Request Details</h3>
        <div id="requestDetailsContent">
            <!-- Details will be loaded here -->
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;">
            <button type="button" class="btn cancel-btn" onclick="closeRequestDetailsModal()">Close</button>
        </div>
    </div>
</div>

<style>
/* Status badges */
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-dispensed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-completed {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Action buttons */
.action-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.action-btn.primary {
    background: #367F2B;
    color: white;
}

.action-btn.primary:hover {
    background: #2d6623;
}

.action-btn.secondary {
    background: #f8f9fa;
    color: #367F2B;
    border: 2px solid #e9ecef;
}

.action-btn.secondary:hover {
    background: #e9ecef;
}

.btn {
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
}

.view-btn {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.view-btn:hover {
    background: #0056b3;
    border-color: #0056b3;
}

.cancel-btn {
    background: #6c757d;
    color: white;
    border-color: #6c757d;
}

.cancel-btn:hover {
    background: #545b62;
    border-color: #545b62;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal.mini .modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.modal .close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.modal .close:hover {
    color: #000;
}

/* Table styling */
.inventory-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(31, 38, 135, 0.15);
    padding: 24px;
    margin-bottom: 24px;
}

.alert {
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Detail list styling */
.details-list {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 8px;
    margin: 10px 0;
}

.details-list dt {
    font-weight: 600;
    color: #1a4931;
}

.details-list dd {
    margin: 0;
    color: #333;
}
</style>

<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<script>
// View request details
function viewRequestDetails(requestId) {
    fetch(`/nurse/pharmacy-requests/${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                let statusColor = request.status.toLowerCase() === 'dispensed' ? '#28a745' : '#dc3545';
                
                let content = `
                    <div class="details-list">
                        <dt>Request ID:</dt><dd>#${request.id}</dd>
                        <dt>Patient:</dt><dd>${request.patient_name || 'N/A'}</dd>
                        <dt>Patient Number:</dt><dd>${request.patient_no || 'N/A'}</dd>
                        <dt>Medicine:</dt><dd>${request.generic_name || request.brand_name || 'N/A'}</dd>
                        ${request.generic_name && request.brand_name ? `<dt>Brand:</dt><dd>${request.brand_name}</dd>` : ''}
                        <dt>Item Code:</dt><dd>${request.item_code || 'N/A'}</dd>
                        <dt>Quantity:</dt><dd>${request.quantity} units</dd>
                        <dt>Unit Price:</dt><dd>₱${parseFloat(request.unit_price || 0).toFixed(2)}</dd>
                        <dt>Total Price:</dt><dd>₱${parseFloat(request.total_price || 0).toFixed(2)}</dd>
                        <dt>Status:</dt><dd><span style="color: ${statusColor}; font-weight: bold;">${request.status}</span></dd>
                        <dt>Requested Date:</dt><dd>${request.requested_at || 'N/A'}</dd>
                        <dt>Requested By:</dt><dd>${request.requested_by || 'N/A'}</dd>
                        ${request.dispensed_by ? `<dt>Dispensed By:</dt><dd>${request.dispensed_by}</dd>` : ''}
                        ${request.dispensed_at ? `<dt>Dispensed Date:</dt><dd>${request.dispensed_at}</dd>` : ''}
                        ${request.notes && request.notes !== 'No additional notes' ? `<dt>Notes:</dt><dd>${request.notes}</dd>` : ''}
                    </div>
                `;
                
                document.getElementById('requestDetailsContent').innerHTML = content;
                document.getElementById('requestDetailsModal').classList.add('show');
            } else {
                alert('Failed to load request details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading request details');
        });
}

function closeRequestDetailsModal() {
    document.getElementById('requestDetailsModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('requestDetailsModal');
    if (event.target === modal) {
        closeRequestDetailsModal();
    }
}

console.log('Medicine Request History page loaded');
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\nurse\medicine_request_history.blade.php ENDPATH**/ ?>