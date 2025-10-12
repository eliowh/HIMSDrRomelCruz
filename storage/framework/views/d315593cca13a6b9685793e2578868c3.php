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
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div class="form-group">
                        <label>Item Code</label>
                        <input id="encode-item_code" name="item_code" readonly />
                    </div>
                    <div class="form-group">
                        <label>Generic Name</label>
                        <input id="encode-generic_name" name="generic_name" readonly />
                    </div>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                    <div class="form-group">
                        <label>Brand Name</label>
                        <input id="encode-brand_name" name="brand_name" readonly />
                    </div>
                    <div class="form-group">
                        <label>Requested Quantity</label>
                        <input id="encode-quantity" name="quantity" type="number" min="1" readonly />
                    </div>
                </div>

                <div id="stock-availability" class="form-group" style="margin-top:8px;">
                    <label>Available in Stock</label>
                    <input id="available-quantity" type="text" readonly style="background: #f8f9fa; color: #28a745; font-weight: bold;" />
                </div>

                <div class="form-actions" style="margin-top:16px;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="btn cancel-btn" onclick="closeEncodeStockModal()">Cancel</button>
                    <button type="submit" class="btn submit-btn">Encode</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\modals\encode_stock_modal.blade.php ENDPATH**/ ?>