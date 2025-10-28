<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock Management</title>
    <link rel="stylesheet" href="{{asset('css/admincss/admin.css')}}">
    <style>
        /* Modal overlay + centering for the admin masterlist modals */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none; /* JS toggles to 'flex' */
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.45);
            z-index: 11000;
            padding: 20px; /* small inset on very small screens */
            box-sizing: border-box;
            overflow: auto;
        }

        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 18px;
            max-width: 820px;
            width: 100%;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            max-height: 90vh;
            overflow: auto;
        }

        /* Basic form layout inside modal */
        .modal .form-group { margin-bottom: 10px; }
        .modal .form-group label { display:block; font-weight:600; margin-bottom:4px; }
        .modal .form-group input { width:100%; padding:8px 10px; box-sizing:border-box; }

        /* Buttons in modal footer */
        .save-btn { background:#1976d2; color:#fff; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; }
        .cancel-btn { background:#eee; color:#333; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; }
    </style>
</head>
<body>
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <div class="main-content">
            <h2>Stock Management</h2>
            <div class="controls-row">
                <div class="filter-search-controls">
                    <input type="text" id="searchInput" placeholder="Search by code or name..." class="search-input" value="{{ request('q') }}">
                    <button id="searchButton" class="search-btn">Search</button>
                </div>
                <button class="add-user-btn" onclick="openAddStockModal()">Add Item</button>
            </div>

            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Generic Name</th>
                            <th>Brand Name</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr data-item-code="{{ $item->{'COL 1'} }}">
                            <td>{{ $item->{'COL 1'} }}</td>
                            <td>{{ $item->{'COL 2'} }}</td>
                            <td>{{ $item->{'COL 3'} }}</td>
                            <td>{{ $item->{'COL 4'} }}</td>
                            <td>
                                <button class="edit-btn" onclick="openEditStockModal('{{ $item->{'COL 1'} }}')">Edit</button>
                                <button class="delete-btn" onclick="deleteStockReference('{{ $item->{'COL 1'} }}')">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                        @if($items->count() == 0)
                        <tr>
                            <td colspan="5" style="text-align:center;padding:20px;color:#666;">No masterlist items found.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                @if(isset($items) && method_exists($items,'hasPages') && $items->hasPages())
                    @include('components.custom-pagination', ['paginator'=>$items])
                @endif
            </div>
        </div>
    </div>

    <!-- Add Modal (styled like other admin modals) -->
    <div id="addStockModal" class="addUserModal" style="display:none;">
        <div class="addUserModalContent">
            <button class="addUserModalClose" onclick="closeAddStockModal()">&times;</button>
            <div class="sign">Add Item</div>
            <form id="addStockForm">
                @csrf
                <div class="form-group">
                    <label for="addItemCode">Item Code</label>
                    <input id="addItemCode" name="item_code" class="form-input" required placeholder="Enter item code" />
                    <span class="error-text" id="addItemCodeError"></span>
                </div>
                <div class="form-group">
                    <label for="addGenericName">Generic Name</label>
                    <input id="addGenericName" name="generic_name" class="form-input" placeholder="Enter generic name" />
                </div>
                <div class="form-group">
                    <label for="addBrandName">Brand Name</label>
                    <input id="addBrandName" name="brand_name" class="form-input" placeholder="Enter brand name" />
                </div>
                <div class="form-group">
                    <label for="addPrice">Price</label>
                    <input id="addPrice" name="price" class="form-input" type="number" step="0.01" min="0" required placeholder="Enter price (e.g., 50.00)" />
                </div>
                <button type="submit" class="assign-btn">Add</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal (styled like other admin modals) -->
    <div id="editStockModal" class="addUserModal" style="display:none;">
        <div class="addUserModalContent">
            <button class="addUserModalClose" onclick="closeEditStockModal()">&times;</button>
            <div class="sign">Edit Item</div>
            <form id="editStockForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-item-id">

                <div class="form-group">
                    <label for="edit-item-code">Item Code</label>
                    <input id="edit-item-code" name="item_code" class="form-input" required placeholder="Enter item code" />
                    <span class="error-text" id="editItemCodeError"></span>
                </div>
                <div class="form-group">
                    <label for="edit-generic-name">Generic Name</label>
                    <input id="edit-generic-name" name="generic_name" class="form-input" placeholder="Enter generic name" />
                </div>
                <div class="form-group">
                    <label for="edit-brand-name">Brand Name</label>
                    <input id="edit-brand-name" name="brand_name" class="form-input" placeholder="Enter brand name" />
                </div>
                <div class="form-group">
                    <label for="edit-price">Price</label>
                    <input id="edit-price" name="price" class="form-input" type="number" step="0.01" min="0" required placeholder="Enter price (e.g., 50.00)" />
                </div>
                <button type="submit" class="assign-btn">Save</button>
            </form>
        </div>
    </div>

    <script>
    // Basic search wiring
    document.getElementById('searchButton').addEventListener('click', function(){
        const q = document.getElementById('searchInput').value.trim();
        const url = new URL(window.location.href);
        if (q) url.searchParams.set('q', q); else url.searchParams.delete('q');
        window.location.href = url.toString();
    });

    // Add modal
    function openAddStockModal(){
        const modal = document.getElementById('addStockModal');
        if (modal) modal.style.display = 'flex';
        else adminError('Add modal not found. Please refresh the page.');
    }

    function closeAddStockModal(){
        const modal = document.getElementById('addStockModal');
        if (modal) modal.style.display = 'none';
        const form = document.getElementById('addStockForm');
        if (form) form.reset();
    }

    document.getElementById('addStockForm').addEventListener('submit', async function(e){
        e.preventDefault();
        const fd = new FormData(this);
        const submitBtn = this.querySelector('.assign-btn');
        const originalText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) { submitBtn.textContent = 'Adding...'; submitBtn.disabled = true; }
        try {
            const res = await fetch('/admin/stocks-reference/create', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
            const j = await res.json();
            if (res.ok && j.success) { closeAddStockModal(); adminSuccess(j.message || 'Item added'); setTimeout(()=>location.reload(),600); }
            else { adminError(j.message || 'Failed to add'); }
        } catch (e) { console.error(e); adminError('Network error'); }
        finally { if (submitBtn) { submitBtn.textContent = originalText; submitBtn.disabled = false; } }
    });

    // Edit modal
    function openEditStockModal(code){
        fetch('/admin/stocks-reference/edit', { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify({ item_code: code }) })
        .then(r=>r.json()).then(j=>{
            if (j.success) {
                document.getElementById('edit-item-id').value = j.item['COL 1'];
                document.getElementById('edit-item-code').value = j.item['COL 1'];
                document.getElementById('edit-generic-name').value = j.item['COL 2'];
                document.getElementById('edit-brand-name').value = j.item['COL 3'];
                document.getElementById('edit-price').value = j.item['COL 4'];
                const modal = document.getElementById('editStockModal');
                if (modal) modal.style.display = 'flex';
            } else adminError(j.message || 'Failed to load');
        }).catch(e=>{console.error(e); adminError('Error loading item');});
    }

    function closeEditStockModal(){
        const modal = document.getElementById('editStockModal');
        if (modal) modal.style.display = 'none';
        const form = document.getElementById('editStockForm');
        if (form) form.reset();
    }

    document.getElementById('editStockForm').addEventListener('submit', async function(e){
        e.preventDefault();
        const payload = {
            id: document.getElementById('edit-item-id').value,
            item_code: document.getElementById('edit-item-code').value,
            generic_name: document.getElementById('edit-generic-name').value,
            brand_name: document.getElementById('edit-brand-name').value,
            price: document.getElementById('edit-price').value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        const submitBtn = this.querySelector('.assign-btn');
        const originalText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) { submitBtn.textContent = 'Saving...'; submitBtn.disabled = true; }
        try {
            const res = await fetch('/admin/stocks-reference/update', { method: 'PUT', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify(payload) });
            const j = await res.json();
            if (res.ok && j.success) { closeEditStockModal(); adminSuccess(j.message || 'Item updated'); setTimeout(()=>location.reload(),600); } else { adminError(j.message || 'Failed to update'); }
        } catch (e) { console.error(e); adminError('Network error'); }
        finally { if (submitBtn) { submitBtn.textContent = originalText; submitBtn.disabled = false; } }
    });

    async function deleteStockReference(code) {
        adminConfirm(
            `Delete masterlist item ${code}? This cannot be undone.`,
            'Confirm Delete',
            async function() {
                try {
                    const encoded = encodeURIComponent(code);
                    const res = await fetch(`/admin/stocks-reference/${encoded}/delete`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
                    const j = await res.json();
                    if (res.ok && j.success) { adminSuccess(j.message || 'Deleted'); setTimeout(()=>location.reload(),600); }
                    else { adminError(j.message || 'Failed to delete'); }
                } catch (e) { console.error(e); adminError('Network error'); }
            }
        );
    }
    </script>
    @include('admin.modals.notification_system')
</body>
</html>