<div id="addUserModal" class="addUserModal">
	<div class="addUserModalContent">
		<button onclick="closeAddUserModal()" class="addUserModalClose">&times;</button>
		<h2 class="sign">Create New User</h2>

		@if(session('success'))
			<div class="bg-green-100 text-green-800 p-3 rounded mb-4">
				{{ session('success') }}
			</div>
		@endif

		<form method="POST" action="{{ route('admin.createUser') }}">
			@csrf
			<div class="mb-3">
				<label class="fName">Full Name</label>
				<input type="text" name="name" class="nField" required>
			</div>

			<div class="mb-3">
				<label class="eMail">Email Address</label>
				<input type="email" name="email" class="eField" required>
			</div>

			<button type="submit" class="CreateUserBtn">
				Create User
			</button>
		</form>
	</div>
</div>
