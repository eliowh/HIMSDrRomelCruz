<!-- Complete Order Modal -->
<div id="completeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Complete Lab Order</h3>
        <form id="completeForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="results">Note:</label>
                <textarea id="results" name="results" rows="4" placeholder="Enter test results summary or notes..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="resultsPdf">Upload Results (PDF):</label>
                <div class="file-upload-container">
                    <input type="file" id="resultsPdf" name="results_pdf" accept=".pdf">
                    <small class="file-hint">Upload the lab results as PDF (optional)</small>
                </div>
            </div>

            <div class="form-group" style="border-top:1px solid #e2e2e2;padding-top:12px;margin-top:18px;">
                <label style="display:block;margin-bottom:6px;">Or Generate Using Template:</label>
                <button type="button" class="btn" style="background:#455A64;color:#fff;" onclick="openLabTemplateModal(window.__activeLabOrderId)">
                    <i class="fas fa-file-medical"></i> Open Template Library
                </button>
                <small class="file-hint" style="display:block;margin-top:6px;">Select a predefined lab form, enter values, and automatically generate & attach a PDF. This will also complete the order.</small>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn cancel-btn" onclick="closeCompleteModal()">Cancel</button>
                <button type="submit" class="btn complete-btn">
                    <span class="btn-text">Complete Order</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Uploading...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\labtech\modals\complete_order_modal.blade.php ENDPATH**/ ?>