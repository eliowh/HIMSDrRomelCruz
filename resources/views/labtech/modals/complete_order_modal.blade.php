<!-- Complete Order Modal -->
<div id="completeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Complete Lab Order</h3>
        <form id="completeForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="results">Test Results:</label>
                <textarea id="results" name="results" rows="4" placeholder="Enter test results summary or notes..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="resultsPdf">Upload Results (PDF):</label>
                <div class="file-upload-container">
                    <input type="file" id="resultsPdf" name="results_pdf" accept=".pdf">
                    <small class="file-hint">Upload the lab results as PDF (optional)</small>
                </div>
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
</div>