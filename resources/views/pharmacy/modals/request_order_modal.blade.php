<!-- Request Order Modal -->
<div id="requestOrderModal" class="pharmacy-modal" style="display: none;">
    <div class="pharmacy-modal-content">
        <div class="pharmacy-modal-header">
            <h3>Request Medicine Order</h3>
            <span class="pharmacy-modal-close" onclick="closeRequestOrderModal()">&times;</span>
        </div>
        <div class="pharmacy-modal-body">
            <form id="requestOrderForm">
                @csrf
                <div class="form-group">
                    <label for="item_code_input">Item Code</label>
                    <div class="pharmacy-search-container">
                        <input type="text" id="item_code_input" name="item_code" class="pharmacy-input pharmacy-search-input" 
                               placeholder="Type to search item code..." required autocomplete="off">
                        <div id="item_code_suggestions" class="pharmacy-suggestions" style="display: none;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="generic_name_input">Generic Name <span class="optional-field">(Optional if Brand Name is provided)</span></label>
                    <div class="pharmacy-search-container">
                        <input type="text" id="generic_name_input" name="generic_name" class="pharmacy-input pharmacy-search-input" 
                               placeholder="Type to search generic name..." autocomplete="off">
                        <div id="generic_name_suggestions" class="pharmacy-suggestions" style="display: none;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="brand_name_input">Brand Name <span class="optional-field">(Optional if Generic Name is provided)</span></label>
                    <div class="pharmacy-search-container">
                        <input type="text" id="brand_name_input" name="brand_name" class="pharmacy-input pharmacy-search-input" 
                               placeholder="Type to search brand name..." autocomplete="off">
                        <div id="brand_name_suggestions" class="pharmacy-suggestions" style="display: none;"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_price">Unit Price</label>
                        <input type="number" id="unit_price" name="unit_price" class="pharmacy-input" step="0.01" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" class="pharmacy-input" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="total_price">Total Price</label>
                    <input type="number" id="total_price" name="total_price" class="pharmacy-input" step="0.01" readonly>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="pharmacy-textarea" rows="3" placeholder="Any additional notes or special instructions..."></textarea>
                </div>

                <div class="pharmacy-modal-footer">
                    <button type="button" class="btn pharmacy-btn-secondary" onclick="closeRequestOrderModal()">Cancel</button>
                    <button type="submit" class="btn pharmacy-btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>