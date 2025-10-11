<!-- PDF Viewer Modal -->
<div id="pdfModal" class="modal">
    <div class="modal-content pdf-modal">
        <span class="close" onclick="closePdfModal()">&times;</span>
        <h3 id="pdfModalTitle">Lab Results PDF</h3>
        <div class="pdf-controls">
            <button class="btn download-btn" id="downloadPdfBtn">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>
        <div class="pdf-viewer">
            <iframe id="pdfFrame" src="" width="100%" height="600px" frameborder="0"></iframe>
        </div>
    </div>
</div><?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/labtech/modals/pdf_viewer_modal.blade.php ENDPATH**/ ?>