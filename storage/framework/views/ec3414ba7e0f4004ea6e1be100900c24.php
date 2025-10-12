<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Orders</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/pharmacycss/pharmacy.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>
    <?php
        $pharmacyName = auth()->user()->name ?? 'Pharmacy Staff';
    ?>
    <?php echo $__env->make('pharmacy.pharmacy_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="pharmacy-layout">
        <?php echo $__env->make('pharmacy.pharmacy_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <div class="page-header">
                <div class="header-content">
                    <h2>Pharmacy Orders</h2>
                    <?php if($orders->total() > 0): ?>
                        <p class="results-summary">
                            <?php if($status !== 'all'): ?>
                                Showing <?php echo e($orders->total()); ?> <?php echo e(ucfirst($status)); ?> order<?php echo e($orders->total() !== 1 ? 's' : ''); ?>

                            <?php else: ?>
                                Showing <?php echo e($orders->total()); ?> total order<?php echo e($orders->total() !== 1 ? 's' : ''); ?>

                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <button class="btn pharmacy-btn-primary" onclick="openRequestOrderModal()">
                    <i class="fas fa-plus"></i> Request Order
                </button>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn <?php echo e($status === 'all' ? 'active' : ''); ?>" data-status="all">
                    All Orders <span class="count-badge"><?php echo e($statusCounts['all']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'pending' ? 'active' : ''); ?>" data-status="pending">
                    Pending <span class="count-badge"><?php echo e($statusCounts['pending']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'approved' ? 'active' : ''); ?>" data-status="approved">
                    Approved <span class="count-badge"><?php echo e($statusCounts['approved']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'completed' ? 'active' : ''); ?>" data-status="completed">
                    Completed <span class="count-badge"><?php echo e($statusCounts['completed']); ?></span>
                </button>
                <button class="tab-btn <?php echo e($status === 'cancelled' ? 'active' : ''); ?>" data-status="cancelled">
                    Cancelled <span class="count-badge"><?php echo e($statusCounts['cancelled']); ?></span>
                </button>
            </div>

            <div class="pharmacy-card">
                <?php if($orders->count() > 0): ?>
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="order-id">
                                        Order ID <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="item-code">
                                        Item Code <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th>Medicine Details</th>
                                    <th class="sortable" data-sort="quantity">
                                        Quantity <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="unit-price">
                                        Unit Price <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="total-price">
                                        Total Price <span class="sort-icon"><i class="fas fa-sort"></i></span>
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
                                    <td class="item-code" data-value="<?php echo e($order->item_code); ?>"><?php echo e($order->item_code); ?></td>
                                    <td class="medicine-info">
                                        <div class="generic-name"><strong><?php echo e($order->generic_name); ?></strong></div>
                                        <?php if($order->brand_name): ?>
                                            <div class="brand-name"><?php echo e($order->brand_name); ?></div>
                                        <?php endif; ?>
                                        <?php if($order->notes): ?>
                                            <small class="notes">Notes: <?php echo e($order->notes); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="quantity" data-value="<?php echo e($order->quantity); ?>"><?php echo e(number_format($order->quantity)); ?></td>
                                    <td class="unit-price" data-value="<?php echo e($order->unit_price); ?>">₱<?php echo e(number_format($order->unit_price, 2)); ?></td>
                                    <td class="total-price" data-value="<?php echo e($order->total_price); ?>">₱<?php echo e(number_format($order->total_price, 2)); ?></td>
                                    <td class="status" data-value="<?php echo e($order->status); ?>">
                                        <span class="status-badge status-<?php echo e($order->status); ?>">
                                            <?php echo e($order->formatted_status); ?>

                                        </span>
                                    </td>
                                    <td class="requested-at" data-value="<?php echo e($order->requested_at->timestamp); ?>">
                                        <?php echo e($order->requested_at->format('M d, Y')); ?><br>
                                        <small><?php echo e($order->requested_at->format('h:i A')); ?></small>
                                    </td>
                                    <td class="actions">
                                        <?php if($order->status === 'pending'): ?>
                                            <button class="btn pharmacy-btn-secondary btn-sm" onclick="editOrder(<?php echo e($order->id); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn pharmacy-btn-danger btn-sm" onclick="cancelOrder(<?php echo e($order->id); ?>)">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn pharmacy-btn-info btn-sm" onclick="viewOrder(<?php echo e($order->id); ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>                   
                    
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-pills"></i>
                        <?php if($status == 'pending'): ?>
                            <h3>No Pending Orders</h3>
                            <p>You don't have any pending orders at the moment.</p>
                        <?php elseif($status == 'approved'): ?>
                            <h3>No Approved Orders</h3>
                            <p>You don't have any approved orders at the moment.</p>
                        <?php elseif($status == 'completed'): ?>
                            <h3>No Completed Orders</h3>
                            <p>You don't have any completed orders yet.</p>
                        <?php elseif($status == 'cancelled'): ?>
                            <h3>No Cancelled Orders</h3>
                            <p>You don't have any cancelled orders.</p>
                        <?php else: ?>
                            <h3>No Pharmacy Orders</h3>
                            <p>No pharmacy orders have been requested yet. Click "Request Order" to create your first order.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Custom Pagination -->
            <?php if($orders->hasPages() && $orders->lastPage() > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <span>Showing <?php echo e($orders->firstItem()); ?> to <?php echo e($orders->lastItem()); ?> of <?php echo e($orders->total()); ?> orders</span>
                    </div>
                    <div class="pagination-container">
                        <?php if (isset($component)) { $__componentOriginald2c13c0488e53309ee86563089ed1a17 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald2c13c0488e53309ee86563089ed1a17 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.custom-pagination','data' => ['paginator' => $orders]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('custom-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($orders)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald2c13c0488e53309ee86563089ed1a17)): ?>
<?php $attributes = $__attributesOriginald2c13c0488e53309ee86563089ed1a17; ?>
<?php unset($__attributesOriginald2c13c0488e53309ee86563089ed1a17); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald2c13c0488e53309ee86563089ed1a17)): ?>
<?php $component = $__componentOriginald2c13c0488e53309ee86563089ed1a17; ?>
<?php unset($__componentOriginald2c13c0488e53309ee86563089ed1a17); ?>
<?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Include Modals -->
    <?php echo $__env->make('pharmacy.modals.request_order_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('pharmacy.modals.edit_order_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('pharmacy.modals.view_order_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('pharmacy.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        let currentOrderId = null;
        let currentSort = { column: null, direction: 'asc' };


        // Initialize on document ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set up filter tabs
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Add loading state to clicked tab
                    btn.disabled = true;
                    btn.innerHTML = btn.innerHTML.replace('</span>', '</span> <i class="fas fa-spinner fa-spin"></i>');
                    
                    filterByStatus(this.dataset.status);
                });
            });
            
            // Set up sortable columns
            setupSortableColumns();
            
            // Set up form event listeners
            setupFormEventListeners();
            
            // Handle back/forward browser navigation
            window.addEventListener('popstate', function() {
                // Reload page when user uses browser back/forward
                window.location.reload();
            });
        });

        function filterByStatus(status) {
            // Redirect to same page with status filter
            const url = new URL(window.location);
            
            // Reset to page 1 when switching tabs
            url.searchParams.delete('page');
            
            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            window.location = url;
        }

        function setupSortableColumns() {
            const sortableHeaders = document.querySelectorAll('.sortable');
            sortableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.dataset.sort;
                    let direction = 'asc';
                    
                    if (currentSort.column === column && currentSort.direction === 'asc') {
                        direction = 'desc';
                    }
                    
                    sortTable(column, direction);
                });
            });
        }

        function sortTable(column, direction) {
            currentSort = { column, direction };
            
            // Update sort icons
            document.querySelectorAll('.sort-icon i').forEach(icon => {
                icon.className = 'fas fa-sort';
            });
            
            const activeHeader = document.querySelector(`[data-sort="${column}"] .sort-icon i`);
            if (activeHeader) {
                activeHeader.className = direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
            
            // Sort table rows
            const tbody = document.querySelector('.orders-table tbody');
            if (!tbody) return;
            
            const rows = Array.from(tbody.querySelectorAll('.order-row'));
            
            rows.sort((a, b) => {
                let aVal, bVal;
                
                if (column === 'order-id') {
                    aVal = parseInt(a.querySelector('.order-id').dataset.value);
                    bVal = parseInt(b.querySelector('.order-id').dataset.value);
                } else if (column === 'requested-at') {
                    aVal = parseInt(a.querySelector('.requested-at').dataset.value);
                    bVal = parseInt(b.querySelector('.requested-at').dataset.value);
                } else if (column === 'quantity' || column === 'unit-price' || column === 'total-price') {
                    aVal = parseFloat(a.querySelector(`.${column.replace('-', '-')}`).dataset.value);
                    bVal = parseFloat(b.querySelector(`.${column.replace('-', '-')}`).dataset.value);
                } else {
                    const aCell = a.querySelector(`.${column.replace('-', '-')}`);
                    const bCell = b.querySelector(`.${column.replace('-', '-')}`);
                    aVal = aCell?.dataset.value || aCell?.textContent || '';
                    bVal = bCell?.dataset.value || bCell?.textContent || '';
                }
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (direction === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        }

        // Search functionality variables
        let searchTimeout = null;
        let activeInput = null;
        let suggestionIndex = -1;
        
        async function searchStocks(query, type = 'all') {
            try {
                console.log('Searching stocks:', query, 'type:', type);
                const params = new URLSearchParams({
                    search: query,
                    type: type
                });
                
                const response = await fetch(`/pharmacy/stocks-reference?${params}`);
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        console.log('Search results:', result.data.length, 'items');
                        return result.data;
                    } else {
                        console.error('API returned error:', result.message);
                        return [];
                    }
                } else {
                    console.error('HTTP Error:', response.status, response.statusText);
                    return [];
                }
            } catch (error) {
                console.error('Error searching stocks:', error);
                return [];
            }
        }

        function showSuggestions(inputElement, suggestions, field) {
            const container = inputElement.parentNode;
            const suggestionsDiv = container.querySelector('.pharmacy-suggestions');
            
            if (suggestions.length === 0) {
                suggestionsDiv.innerHTML = '<div class="pharmacy-suggestion-no-results">No results found</div>';
                suggestionsDiv.style.display = 'block';
                return;
            }
            
            suggestionsDiv.innerHTML = '';
            suggestions.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'pharmacy-suggestion-item';
                
                let displayText = '';
                let value = '';
                
                if (field === 'item_code') {
                    const nameToShow = item.brand_name || item.generic_name || 'N/A';
                    displayText = `${item.item_code} - ${nameToShow}`;
                    value = item.item_code;
                } else if (field === 'generic_name') {
                    if (!item.generic_name) return; // Skip items without generic name
                    displayText = item.generic_name;
                    if (item.brand_name) {
                        displayText += ` (${item.brand_name})`;
                    }
                    value = item.generic_name;
                } else if (field === 'brand_name') {
                    if (!item.brand_name) return; // Skip items without brand name
                    displayText = item.brand_name;
                    if (item.generic_name) {
                        displayText += ` (${item.generic_name})`;
                    }
                    value = item.brand_name;
                }
                
                div.textContent = displayText;
                div.addEventListener('click', () => {
                    selectSuggestion(inputElement, value, item);
                    hideSuggestions(suggestionsDiv);
                });
                
                suggestionsDiv.appendChild(div);
            });
            
            suggestionsDiv.style.display = 'block';
            suggestionIndex = -1;
        }
        
        function selectSuggestion(inputElement, value, stockData) {
            inputElement.value = value;
            populateFieldsFromStock(stockData);
        }
        
        function hideSuggestions(suggestionsDiv) {
            suggestionsDiv.style.display = 'none';
            suggestionIndex = -1;
        }
        
        function hideAllSuggestions() {
            document.querySelectorAll('.pharmacy-suggestions').forEach(div => {
                div.style.display = 'none';
            });
            suggestionIndex = -1;
        }

        function setupFormEventListeners() {
            const itemCodeInput = document.getElementById('item_code_input');
            const genericNameInput = document.getElementById('generic_name_input');
            const brandNameInput = document.getElementById('brand_name_input');
            const quantityInput = document.getElementById('quantity');

            // Setup search input event listeners
            setupSearchInput(itemCodeInput, 'item_code');
            setupSearchInput(genericNameInput, 'generic_name');
            setupSearchInput(brandNameInput, 'brand_name');

            // Calculate total price when quantity changes
            quantityInput.addEventListener('input', calculateTotalPrice);

            // Handle form submissions
            document.getElementById('requestOrderForm').addEventListener('submit', handleOrderSubmission);
            document.getElementById('editOrderForm').addEventListener('submit', handleOrderEdit);
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.pharmacy-search-container')) {
                    hideAllSuggestions();
                }
            });
        }
        
        function setupSearchInput(inputElement, fieldType) {
            inputElement.addEventListener('input', function(e) {
                const query = e.target.value.trim();
                
                // Clear search timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                if (query.length < 2) {
                    hideAllSuggestions();
                    return;
                }
                
                // Set a timeout to avoid too many API calls
                searchTimeout = setTimeout(async () => {
                    const suggestions = await searchStocks(query, fieldType);
                    showSuggestions(inputElement, suggestions, fieldType);
                }, 300);
            });
            
            // Handle keyboard navigation
            inputElement.addEventListener('keydown', function(e) {
                const suggestionsDiv = this.parentNode.querySelector('.pharmacy-suggestions');
                const items = suggestionsDiv.querySelectorAll('.pharmacy-suggestion-item');
                
                if (items.length === 0) return;
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        suggestionIndex = Math.min(suggestionIndex + 1, items.length - 1);
                        updateHighlight(items);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        suggestionIndex = Math.max(suggestionIndex - 1, -1);
                        updateHighlight(items);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (suggestionIndex >= 0 && items[suggestionIndex]) {
                            items[suggestionIndex].click();
                        }
                        break;
                    case 'Escape':
                        hideAllSuggestions();
                        break;
                }
            });
        }
        
        function updateHighlight(items) {
            items.forEach((item, index) => {
                item.classList.toggle('highlighted', index === suggestionIndex);
            });
        }

        // Helper function to parse price values that may contain commas
        function parsePrice(value) {
            if (typeof value === 'number') return value;
            if (typeof value !== 'string') return 0;
            // Remove commas, currency symbols, and extra spaces, then parse as float
            const cleaned = value.replace(/[,₱$\s]/g, '');
            const parsed = parseFloat(cleaned);
            return isNaN(parsed) ? 0 : parsed;
        }

        function populateFieldsFromStock(stock) {
            document.getElementById('item_code_input').value = stock.item_code || '';
            document.getElementById('generic_name_input').value = stock.generic_name || '';
            document.getElementById('brand_name_input').value = stock.brand_name || '';
            document.getElementById('unit_price').value = parsePrice(stock.price).toFixed(2) || '0';
            
            hideAllSuggestions();
            
            // Clear and recalculate total
            calculateTotalPrice();
        }

        function calculateTotalPrice() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const unitPrice = parsePrice(document.getElementById('unit_price').value);
            const totalPrice = quantity * unitPrice;
            document.getElementById('total_price').value = totalPrice.toFixed(2);
        }

        // Modal functions - Matching Labtech style
        function openRequestOrderModal() {
            const modal = document.getElementById('requestOrderModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.getElementById('requestOrderForm').reset();
        }

        function closeRequestOrderModal() {
            const modal = document.getElementById('requestOrderModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        }

        function openEditOrderModal() {
            const modal = document.getElementById('editOrderModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
        }

        function closeEditOrderModal() {
            const modal = document.getElementById('editOrderModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        }

        // Handle form submissions
        async function handleOrderSubmission(e) {
            e.preventDefault();
            
            // Client-side validation: ensure at least one name field is filled
            const genericName = document.getElementById('generic_name_input').value.trim();
            const brandName = document.getElementById('brand_name_input').value.trim();
            
            if (!genericName && !brandName) {
                showPharmacyWarning('Please provide either a Generic Name or Brand Name (or both).', 'Missing Information');
                return;
            }
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/pharmacy/orders', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showPharmacySuccess('Your order request has been submitted successfully!', 'Order Submitted', () => {
                        closeRequestOrderModal();
                        window.location.reload();
                    });
                } else {
                    let errorMessage = result.message || 'Failed to submit order';
                    if (result.errors) {
                        const errors = Object.values(result.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                    showPharmacyError(errorMessage, 'Submission Failed');
                }
            } catch (error) {
                console.error('Error submitting order:', error);
                showPharmacyError('Network error occurred. Please check your connection and try again.', 'Connection Error');
            }
        }

        async function handleOrderEdit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const orderId = formData.get('order_id');
            const quantity = formData.get('quantity');
            const notes = formData.get('notes');
            
            console.log('Updating order:', orderId);
            console.log('New quantity:', quantity);
            console.log('New notes:', notes);
            
            // Create a simple object with the data
            const updateData = {
                quantity: parseInt(quantity),
                notes: notes || '',
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                _method: 'PUT'
            };
            
            console.log('Update data:', updateData);
            
            try {
                const response = await fetch(`/pharmacy/orders/${orderId}`, {
                    method: 'POST', // Use POST with _method override
                    body: JSON.stringify(updateData),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    showPharmacySuccess('Order has been updated successfully!', 'Order Updated', () => {
                        closeEditOrderModal();
                        window.location.reload();
                    });
                } else {
                    showPharmacyError(result.message || 'Failed to update order', 'Update Failed');
                    console.error('Update failed:', result);
                }
            } catch (error) {
                console.error('Error updating order:', error);
                showPharmacyError('Network error occurred. Please try again.', 'Connection Error');
            }
        }

        // Order action functions
        function editOrder(orderId) {
            // Get order data and populate edit form
            const orderRow = document.querySelector(`[data-status] .order-id[data-value="${orderId}"]`).closest('tr');
            const quantity = orderRow.querySelector('.quantity').dataset.value;
            const notes = orderRow.querySelector('.notes')?.textContent.replace('Notes: ', '') || '';
            
            document.getElementById('edit_order_id').value = orderId;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_notes').value = notes;
            
            openEditOrderModal();
        }

        async function cancelOrder(orderId) {
            // Show confirmation dialog
            showPharmacyConfirm('Are you sure you want to cancel this order? This action cannot be undone.', 'Confirm Cancellation', (confirmed) => {
                if (!confirmed) return;
                
                // Proceed with cancellation
                proceedWithCancellation(orderId);
            });
        }
        
        async function proceedWithCancellation(orderId) {
            
            try {
                const response = await fetch(`/pharmacy/orders/${orderId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showPharmacySuccess('Order has been cancelled successfully.', 'Order Cancelled', () => {
                        window.location.reload();
                    });
                } else {
                    showPharmacyError(result.message || 'Failed to cancel order', 'Cancellation Failed');
                }
            } catch (error) {
                console.error('Error cancelling order:', error);
                showPharmacyError('Network error occurred. Please try again.', 'Connection Error');
            }
        }

        function viewOrder(orderId) {
            const orderRow = document.querySelector(`[data-status] .order-id[data-value="${orderId}"]`).closest('tr');
            const itemCode = orderRow.querySelector('.item-code').textContent.trim();
            const genericName = orderRow.querySelector('.generic-name').textContent.trim();
            const brandName = orderRow.querySelector('.brand-name')?.textContent.trim() || '';
            const quantity = orderRow.querySelector('.quantity').textContent.trim();
            const unitPrice = orderRow.querySelector('.unit-price').textContent.trim();
            const totalPrice = orderRow.querySelector('.total-price').textContent.trim();
            const status = orderRow.querySelector('.status-badge').textContent.trim();
            const requestedAt = orderRow.querySelector('.requested-at').textContent.replace(/\s+/g, ' ').trim();
            const notesElement = orderRow.querySelector('.notes');
            const notes = notesElement ? notesElement.textContent.replace('Notes: ', '').trim() : '';
            
            // Create the order details HTML
            const detailsHtml = `
                <div class="order-detail-grid">
                    <div class="detail-group">
                        <h4>Order Information</h4>
                        <p><strong>Order ID:</strong> #${String(orderId).padStart(4, '0')}</p>
                        <p><strong>Item Code:</strong> ${itemCode}</p>
                        <p><strong>Status:</strong> <span class="status-display ${status.toLowerCase()}">${status}</span></p>
                        <p><strong>Requested:</strong> ${requestedAt}</p>
                    </div>
                    
                    <div class="detail-group">
                        <h4>Medicine Details</h4>
                        ${genericName ? `<p><strong>Generic Name:</strong> ${genericName}</p>` : ''}
                        ${brandName ? `<p><strong>Brand Name:</strong> ${brandName}</p>` : ''}
                        ${!genericName && !brandName ? `<p><strong>Name:</strong> Not specified</p>` : ''}
                    </div>
                </div>
                
                <div class="order-detail-grid">
                    <div class="detail-group">
                        <h4>Quantity & Pricing</h4>
                        <p><strong>Quantity:</strong> ${quantity}</p>
                        <p><strong>Unit Price:</strong> ${unitPrice}</p>
                        <p><strong>Total Price:</strong> ${totalPrice}</p>
                    </div>
                    
                    ${notes ? `
                    <div class="detail-group">
                        <h4>Notes</h4>
                        <p>${notes}</p>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('viewOrderContent').innerHTML = detailsHtml;
            openViewOrderModal();
        }
        
        function openViewOrderModal() {
            const modal = document.getElementById('viewOrderModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
        }
        
        function closeViewOrderModal() {
            const modal = document.getElementById('viewOrderModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const requestModal = document.getElementById('requestOrderModal');
            const editModal = document.getElementById('editOrderModal');
            const viewModal = document.getElementById('viewOrderModal');
            
            if (event.target === requestModal) {
                closeRequestOrderModal();
            }
            if (event.target === editModal) {
                closeEditOrderModal();
            }
            if (event.target === viewModal) {
                closeViewOrderModal();
            }
        }
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\pharmacy\pharmacy_orders.blade.php ENDPATH**/ ?>