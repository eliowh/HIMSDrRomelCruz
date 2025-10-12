<!-- Cancellation Reason Modal -->
<div id="cancelReasonModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeCancelReasonModal()">&times;</span>
        <h3>Cancel Lab Order</h3>
        <p class="modal-warning"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. The order will be marked as cancelled.</p>
        <form id="cancelReasonForm">
            <div class="form-group">
                <label for="cancelReason">Reason for Cancellation:</label>
                <textarea id="cancelReason" name="cancel_reason" rows="4" placeholder="Please provide a reason for cancelling this lab order..." required></textarea>
                <small class="field-hint">This information will be recorded for reference.</small>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn cancel-btn" onclick="closeCancelReasonModal()">Back</button>
                <button type="submit" class="btn complete-btn">
                    <span class="btn-text">Confirm Cancellation</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\labtech\modals\cancel_reason_modal.blade.php ENDPATH**/ ?>