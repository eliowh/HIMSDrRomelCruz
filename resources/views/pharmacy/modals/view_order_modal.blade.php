<!-- View Order Details Modal -->
<div id="viewOrderModal" class="pharmacy-modal" style="display: none;">
    <div class="pharmacy-modal-content pharmacy-modal-large">
        <div class="pharmacy-modal-header">
            <h3>Order Details</h3>
            <span class="pharmacy-modal-close" onclick="closeViewOrderModal()">&times;</span>
        </div>
        <div class="pharmacy-modal-body">
            <div id="viewOrderContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
        <div class="pharmacy-modal-footer">
            <button type="button" class="btn pharmacy-btn-secondary" onclick="closeViewOrderModal()">Close</button>
        </div>
    </div>
</div>