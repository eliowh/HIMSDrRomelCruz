<!-- Add Stock Modal -->
<div id="addStockModal" class="modal mini">
    <div class="modal-content">
        <span class="close" onclick="closeAddStockModal()">&times;</span>
        <h3>Add Stock</h3>
        <form id="addStockForm">
            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div class="form-group">
                    <label>Item Code *</label>
                    <div class="search-container">
                        <input id="item_code_input" name="item_code" placeholder="Type to search by item code..." autocomplete="off" />
                        <div id="item_code_suggestions" class="suggestions-container"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Generic Name <small>(auto-filled)</small></label>
                    <div class="search-container">
                        <input id="generic_name_input" name="generic_name" placeholder="Generic name" autocomplete="off" />
                        <div id="generic_name_suggestions" class="suggestions-container"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Brand Name <small>(auto-filled)</small></label>
                    <div class="search-container">
                        <input id="brand_name_input" name="brand_name" placeholder="Brand name" autocomplete="off" />
                        <div id="brand_name_suggestions" class="suggestions-container"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Price *</label>
                    <input id="price_input" name="price" type="number" step="0.01" min="0" placeholder="0.00" required />
                </div>
            </div>

            <div class="form-group" style="margin-top:8px;">
                <label>Quantity to add *</label>
                <input name="quantity" type="number" min="1" required placeholder="Quantity" />
            </div>

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input name="reorder_level" type="number" placeholder="Reorder Level" value="10" />
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input name="expiry_date" type="date" id="add-expiry-date" />
                </div>
            </div>

            <div class="form-group" style="margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="non_perishable" id="add-non-perishable" style="width: auto; height: auto; margin-right: 5px;"> 
                    <span>Non-perishable</span>
                </label>
            </div>

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                <div class="form-group">
                    <label>Supplier</label>
                    <input name="supplier" placeholder="Supplier" />
                </div>
                <div class="form-group">
                    <label>Batch Number</label>
                    <input name="batch_number" placeholder="Batch Number" />
                </div>
            </div>
            <div class="form-group" style="margin-top:8px;">
                <label>Date Received</label>
                <input name="date_received" type="date" />
            </div>

            <div class="form-actions" style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn cancel-btn" onclick="closeAddStockModal()">Cancel</button>
                <button type="submit" class="btn submit-btn">Add</button>
            </div>
        </form>
    </div>
</div>

<script>
// Add Stock modal functions
function openAddStockModal() {
    window.isModalOpen = true;
    const modal = document.getElementById('addStockModal');
    modal.classList.add('show');
    modal.classList.add('open');
    document.getElementById('addStockForm').reset();
    
    // Initialize search functionality when modal opens
    initializeStockSearch();
}

function closeAddStockModal() {
    const modal = document.getElementById('addStockModal');
    modal.classList.remove('show');
    modal.classList.remove('open');
    document.getElementById('addStockForm').reset();
    
    // Clear all suggestions
    document.querySelectorAll('.suggestions-container').forEach(container => {
        container.style.display = 'none';
        container.innerHTML = '';
    });
    
    setTimeout(() => { window.isModalOpen = false; }, 300);
}

// Search functionality
let searchTimeout = null;
let activeInput = null;

function initializeStockSearch() {
    const itemCodeInput = document.getElementById('item_code_input');
    const genericNameInput = document.getElementById('generic_name_input');
    const brandNameInput = document.getElementById('brand_name_input');
    
    // Setup search input event listeners
    setupSearchInput(itemCodeInput, 'item_code');
    setupSearchInput(genericNameInput, 'generic_name');
    setupSearchInput(brandNameInput, 'brand_name');
}

function setupSearchInput(inputElement, field) {
    inputElement.addEventListener('input', function() {
        const query = this.value.trim();
        activeInput = inputElement;
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSuggestions(inputElement);
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchStocks(query, field, inputElement);
        }, 300);
    });
    
    inputElement.addEventListener('blur', function() {
        // Delay hiding suggestions to allow clicks
        setTimeout(() => {
            if (activeInput === inputElement) {
                hideSuggestions(inputElement);
            }
        }, 200);
    });
    
    inputElement.addEventListener('focus', function() {
        activeInput = inputElement;
    });
}

async function searchStocks(query, field, inputElement) {
    try {
        const response = await fetch(`/inventory/stocks-reference?search=${encodeURIComponent(query)}&type=${field}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            showSuggestions(inputElement, result.data, field);
        } else {
            hideSuggestions(inputElement);
        }
    } catch (error) {
        console.error('Search error:', error);
        hideSuggestions(inputElement);
    }
}

function showSuggestions(inputElement, suggestions, field) {
    const container = inputElement.parentNode.querySelector('.suggestions-container');
    container.innerHTML = '';
    
    suggestions.forEach(item => {
        const suggestionDiv = document.createElement('div');
        suggestionDiv.className = 'suggestion-item';
        
        let displayText = '';
        let value = '';
        
        switch (field) {
            case 'item_code':
                value = item.item_code;
                displayText = `${item.item_code} - ${item.generic_name || 'N/A'} (${item.brand_name || 'N/A'})`;
                break;
            case 'generic_name':
                value = item.generic_name;
                displayText = `${item.generic_name} - ${item.item_code} (${item.brand_name || 'N/A'})`;
                break;
            case 'brand_name':
                value = item.brand_name;
                displayText = `${item.brand_name} - ${item.item_code} (${item.generic_name || 'N/A'})`;
                break;
        }
        
        suggestionDiv.textContent = displayText;
        suggestionDiv.addEventListener('click', function() {
            selectSuggestion(inputElement, value, item);
        });
        
        container.appendChild(suggestionDiv);
    });
    
    container.style.display = 'block';
}

function selectSuggestion(inputElement, value, stockData) {
    inputElement.value = value;
    hideSuggestions(inputElement);
    
    // Auto-fill other fields when item is selected
    document.getElementById('item_code_input').value = stockData.item_code || '';
    document.getElementById('generic_name_input').value = stockData.generic_name || '';
    document.getElementById('brand_name_input').value = stockData.brand_name || '';
    
    // Handle price with comma parsing
    const priceInput = document.getElementById('price_input');
    if (stockData.price !== null && stockData.price !== undefined && stockData.price !== '') {
        // Remove commas and parse as float to handle prices like "1,060.00"
        const cleanPrice = String(stockData.price).replace(/,/g, '');
        const parsedPrice = parseFloat(cleanPrice);
        priceInput.value = isNaN(parsedPrice) ? '' : parsedPrice.toFixed(2);
    } else {
        priceInput.value = '';
    }
}

function hideSuggestions(inputElement) {
    const container = inputElement.parentNode.querySelector('.suggestions-container');
    container.style.display = 'none';
    container.innerHTML = '';
}

// Wire Add Stock button to open modal
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addStockBtn').addEventListener('click', function(){ openAddStockModal(); });

    // Submit handler for addStockForm
    document.getElementById('addStockForm').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding...'; 
        submitBtn.disabled = true;

        const formData = new FormData(form);
        
        // Validate required fields
        const itemCode = formData.get('item_code');
        const quantity = parseInt(formData.get('quantity') || 0, 10);
        const price = parseFloat(formData.get('price') || 0);
        
        if (!itemCode || itemCode.trim() === '') {
            showError('Please enter an item code', 'Validation Error');
            submitBtn.textContent = originalText; 
            submitBtn.disabled = false; 
            return;
        }
        
        if (!quantity || quantity <= 0) {
            showError('Please enter a valid quantity', 'Validation Error');
            submitBtn.textContent = originalText; 
            submitBtn.disabled = false; 
            return;
        }
        
        if (!price || price <= 0) {
            showError('Please enter a valid price', 'Validation Error');
            submitBtn.textContent = originalText; 
            submitBtn.disabled = false; 
            return;
        }

        fetch('/inventory/stocks/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                closeAddStockModal();
                showSuccess(data.message || 'Stock added successfully!', 'Stock Added', function() {
                    location.reload();
                });
            } else {
                showError(data.message || 'Failed to add stock', 'Add Stock Error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error adding stock. Please try again.', 'Network Error');
        })
        .finally(() => {
            submitBtn.textContent = originalText; 
            submitBtn.disabled = false;
        });
    });
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('addStockModal');
    if (event.target === modal) {
        closeAddStockModal();
    }
});
</script>


<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\modals\add_stock_modal.blade.php ENDPATH**/ ?>