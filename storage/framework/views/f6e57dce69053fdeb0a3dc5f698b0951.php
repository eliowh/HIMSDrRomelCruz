<!-- Edit User Modal -->
<div id="editUserModal" class="addUserModal">
    <div class="addUserModalContent">
        <button class="addUserModalClose" onclick="closeEditUserModal()">&times;</button>
        <div class="sign">Edit User</div>
        
        <form id="editUserForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="editUserId" name="id">
            
            <div class="form-group">
                <label for="editUserName" class="form-label">Full Name</label>
                <input type="text" id="editUserName" name="name" class="form-input" required>
                <span class="error-text" id="editNameError"></span>
            </div>
            
            <div class="form-group">
                <label for="editUserEmail" class="form-label">Email Address</label>
                <input type="email" id="editUserEmail" name="email" class="form-input" required>
                <span class="error-text" id="editEmailError"></span>
            </div>
            
            <div class="form-group">
                <label for="editUserRole" class="form-label">Role</label>
                <select id="editUserRole" name="role" class="form-input" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="lab_technician">Lab Technician</option>
                    <option value="cashier">Cashier</option>
                    <option value="inventory">Inventory</option>
                    <option value="pharmacy">Pharmacy</option>
                    <option value="billing">Billing</option>
                </select>
                <span class="error-text" id="editRoleError"></span>
            </div>
            
            <div id="editErrorMessage" class="error-message" style="display: none;"></div>
            
            <button type="submit" class="assign-btn">Update User</button>
        </form>
    </div>
</div>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/admin/modals/admin_editUser.blade.php ENDPATH**/ ?>