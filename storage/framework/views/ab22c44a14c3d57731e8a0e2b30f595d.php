<!-- Edit Order Modal -->
<div id="editOrderModal" class="pharmacy-modal" style="display: none;">
    <div class="pharmacy-modal-content">
        <div class="pharmacy-modal-header">
            <h3>Edit Order</h3>
            <span class="pharmacy-modal-close" onclick="closeEditOrderModal()">&times;</span>
        </div>
        <div class="pharmacy-modal-body">
            <form id="editOrderForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" id="edit_order_id" name="order_id">
                
                <div class="form-group">
                    <label for="edit_quantity">Quantity *</label>
                    <input type="number" id="edit_quantity" name="quantity" class="pharmacy-input" min="1" required>
                </div>

                <div class="form-group">
                    <label for="edit_notes">Notes (Optional)</label>
                    <textarea id="edit_notes" name="notes" class="pharmacy-textarea" rows="3" placeholder="Any additional notes or special instructions..."></textarea>
                </div>

                <div class="pharmacy-modal-footer">
                    <button type="button" class="btn pharmacy-btn-secondary" onclick="closeEditOrderModal()">Cancel</button>
                    <button type="submit" class="btn pharmacy-btn-primary">
                        <i class="fas fa-save"></i> Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\pharmacy\modals\edit_order_modal.blade.php ENDPATH**/ ?>