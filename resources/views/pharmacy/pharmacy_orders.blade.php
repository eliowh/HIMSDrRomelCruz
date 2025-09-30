<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Orders</title>
    <link rel="stylesheet" href="{{ url('css/pharmacycss/pharmacy.css') }}">
    <link rel="stylesheet" href="{{ url('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $pharmacyName = auth()->user()->name ?? 'Pharmacy Staff';
    @endphp
    @include('pharmacy.pharmacy_header')

    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')

        <main class="main-content">
            <div class="page-header">
                <div class="header-content">
                    <h2>Pharmacy Orders</h2>
                    @if($orders->total() > 0)
                        <p class="results-summary">
                            @if($status !== 'all')
                                Showing {{ $orders->total() }} {{ ucfirst($status) }} order{{ $orders->total() !== 1 ? 's' : '' }}
                            @else
                                Showing {{ $orders->total() }} total order{{ $orders->total() !== 1 ? 's' : '' }}
                            @endif
                        </p>
                    @endif
                </div>
                <button class="btn pharmacy-btn-primary" onclick="openRequestOrderModal()">
                    <i class="fas fa-plus"></i> Request Order
                </button>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">
                    All Orders <span class="count-badge">{{ $statusCounts['all'] }}</span>
                </button>
                <button class="tab-btn {{ $status === 'pending' ? 'active' : '' }}" data-status="pending">
                    Pending <span class="count-badge">{{ $statusCounts['pending'] }}</span>
                </button>
                <button class="tab-btn {{ $status === 'approved' ? 'active' : '' }}" data-status="approved">
                    Approved <span class="count-badge">{{ $statusCounts['approved'] }}</span>
                </button>
                <button class="tab-btn {{ $status === 'completed' ? 'active' : '' }}" data-status="completed">
                    Completed <span class="count-badge">{{ $statusCounts['completed'] }}</span>
                </button>
                <button class="tab-btn {{ $status === 'cancelled' ? 'active' : '' }}" data-status="cancelled">
                    Cancelled <span class="count-badge">{{ $statusCounts['cancelled'] }}</span>
                </button>
            </div>

            <div class="pharmacy-card">
                @if($orders->count() > 0)
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
                                @foreach($orders as $order)
                                <tr class="order-row" data-status="{{ $order->status }}">
                                    <td class="order-id" data-value="{{ $order->id }}">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="item-code" data-value="{{ $order->item_code }}">{{ $order->item_code }}</td>
                                    <td class="medicine-info">
                                        <div class="generic-name"><strong>{{ $order->generic_name }}</strong></div>
                                        @if($order->brand_name)
                                            <div class="brand-name">{{ $order->brand_name }}</div>
                                        @endif
                                        @if($order->notes)
                                            <small class="notes">Notes: {{ $order->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="quantity" data-value="{{ $order->quantity }}">{{ number_format($order->quantity) }}</td>
                                    <td class="unit-price" data-value="{{ $order->unit_price }}">₱{{ number_format($order->unit_price, 2) }}</td>
                                    <td class="total-price" data-value="{{ $order->total_price }}">₱{{ number_format($order->total_price, 2) }}</td>
                                    <td class="status" data-value="{{ $order->status }}">
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ $order->formatted_status }}
                                        </span>
                                    </td>
                                    <td class="requested-at" data-value="{{ $order->requested_at->timestamp }}">
                                        {{ $order->requested_at->format('M d, Y') }}<br>
                                        <small>{{ $order->requested_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="actions">
                                        @if($order->status === 'pending')
                                            <button class="btn pharmacy-btn-secondary btn-sm" onclick="editOrder({{ $order->id }})">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn pharmacy-btn-danger btn-sm" onclick="cancelOrder({{ $order->id }})">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        @elseif($order->status === 'approved')
                                            <button class="btn pharmacy-btn-warning btn-sm" onclick="cancelOrder({{ $order->id }})">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        @endif
                                        
                                        <button class="btn pharmacy-btn-info btn-sm" onclick="viewOrder({{ $order->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>                   
                    
                @else
                    <div class="no-orders">
                        <i class="fas fa-pills"></i>
                        @if($status == 'pending')
                            <h3>No Pending Orders</h3>
                            <p>You don't have any pending orders at the moment.</p>
                        @elseif($status == 'approved')
                            <h3>No Approved Orders</h3>
                            <p>You don't have any approved orders at the moment.</p>
                        @elseif($status == 'completed')
                            <h3>No Completed Orders</h3>
                            <p>You don't have any completed orders yet.</p>
                        @elseif($status == 'cancelled')
                            <h3>No Cancelled Orders</h3>
                            <p>You don't have any cancelled orders.</p>
                        @else
                            <h3>No Pharmacy Orders</h3>
                            <p>No pharmacy orders have been requested yet. Click "Request Order" to create your first order.</p>
                        @endif
                    </div>
                @endif
            </div>
            <!-- Custom Pagination -->
            @if($orders->hasPages() && $orders->lastPage() > 1)
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <span>Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders</span>
                    </div>
                    <div class="pagination-container">
                        <x-custom-pagination :paginator="$orders" />
                    </div>
                </div>
            @endif
        </main>
    </div>

    <!-- Include Modals -->
    @include('pharmacy.modals.request_order_modal')
    @include('pharmacy.modals.edit_order_modal')
    @include('pharmacy.modals.view_order_modal')
    @include('pharmacy.modals.notification_system')

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

        function populateFieldsFromStock(stock) {
            document.getElementById('item_code_input').value = stock.item_code || '';
            document.getElementById('generic_name_input').value = stock.generic_name || '';
            document.getElementById('brand_name_input').value = stock.brand_name || '';
            document.getElementById('unit_price').value = stock.price || '0';
            
            hideAllSuggestions();
            
            // Clear and recalculate total
            calculateTotalPrice();
        }

        function calculateTotalPrice() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
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
