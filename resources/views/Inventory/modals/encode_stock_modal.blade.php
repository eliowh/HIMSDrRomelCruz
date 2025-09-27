<!-- Encode Stock Modal -->
<div id="encodeStockModal" class="inventory-modal" style="display: none;">
    <div class="inventory-modal-content">
        <div class="inventory-modal-header">
            <h3>Encode Approved Order</h3>
            <span class="inventory-modal-close" onclick="closeEncodeStockModal()">&times;</span>
        </div>
        <div class="inventory-modal-body">
            <form id="encodeStockForm">
                <input type="hidden" name="order_id" id="encode-order-id">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div class="form-group">
                        <label>Item Code</label>
                        <input id="encode-item_code" name="item_code" readonly />
                    </div>
                    <div class="form-group">
                        <label>Generic Name</label>
                        <input id="encode-generic_name" name="generic_name" readonly />
                    </div>
                    <div class="form-group">
                        <label>Brand Name</label>
                        <input id="encode-brand_name" name="brand_name" readonly />
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input id="encode-price" name="price" type="number" step="0.01" min="0" required />
                    </div>
                </div>

                <div class="form-group" style="margin-top:8px;">
                    <label>Quantity to add</label>
                    <input id="encode-quantity" name="quantity" type="number" min="1" required />
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                    <div class="form-group">
                        <label>Reorder Level</label>
                        <input name="reorder_level" type="number" placeholder="Reorder Level" value="10" />
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input name="expiry_date" type="date" id="encode-expiry-date" />
                    </div>
                </div>

                <div class="form-group" style="margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="non_perishable" id="encode-non-perishable" style="width: auto; height: auto; margin-right: 5px;"> 
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

                <div class="form-actions" style="margin-top:16px;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="btn cancel-btn" onclick="closeEncodeStockModal()">Cancel</button>
                    <button type="submit" class="btn submit-btn">Encode</button>
                </div>
            </form>
        </div>
    </div>
</div>
