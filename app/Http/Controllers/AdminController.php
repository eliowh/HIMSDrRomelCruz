<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Notifications\RoleAssigned;
use App\Notifications\NewUserCredentials;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Icd10Import;

class AdminController extends Controller
{

    public function index()
    {
        $adminName = $this->getAdminName();
        return view('admin.admin_home', compact('adminName'));
    }
    public function getAdminName()
    {
        $adminName = auth()->user()->name ?? 'Admin';
        return $adminName;
    }
    // Removed pending user approval methods as they are no longer needed
    // Roles are now assigned directly at user creation
    
    public function showCreateUserForm()
    {
        return view('admin.create_user');
    }

    public function createUser(Request $request)
    {
        // Log request data for debugging
        \Log::info('Create user request', [
            'email' => $request->email,
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'wants_json' => $request->wantsJson(),
            'ajax' => $request->ajax()
        ]);
        
        // Validate inputs
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => [
                'required',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z\s]+$/', // Only letters and spaces
            ],
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/', // Only .com domains allowed
                'unique:users,email'
            ],
            'role' => ['required', 'in:doctor,nurse,lab_technician,cashier,admin']
        ], [
            'name.required' => 'Please enter a name.',
            'name.min' => 'Name must be 3-20 letters.',
            'name.max' => 'Name must be 3-20 letters.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Email must end with .com and be valid.',
            'email.unique' => 'This email is already registered.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Invalid role selected.'
        ]);
        
        if ($validator->fails()) {
            \Log::info('Validation failed', ['errors' => $validator->errors()]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('Content-Type') == 'application/json') {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }
        
        // Double-check if email exists (extra validation)
        if (User::where('email', $request->email)->exists()) {
            \Log::info('Email already exists', ['email' => $request->email]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('Content-Type') == 'application/json') {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'email' => ['This email is already registered.']
                    ]
                ], 422);
            }
            
            return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
        }
        
        // Generate password reset token
        $token = \Illuminate\Support\Str::random(60);
        
        // Log token for debugging
        \Log::info('Generated token for new user', [
            'email' => $request->email,
            'token' => $token
        ]);
        
        // Create user with a temporary password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Random temporary password
            'role' => $request->role
        ]);
        
        // Set the password reset token
        $user->password_reset_token = $token;
        $user->save();
        
        // Log user creation
        Report::log(
            'New User Created',
            Report::TYPE_USER_REGISTRATION,
            "Admin created new user: {$user->name} with role {$user->role}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'created_by' => auth()->user()->name,
                'created_by_id' => auth()->id(),
                'creation_time' => now()->toISOString()
            ],
            auth()->id()
        );
        
        // Double check the token was saved
        $savedUser = User::find($user->id);
        \Log::info('Saved user token check', [
            'email' => $savedUser->email,
            'token_saved' => $savedUser->password_reset_token
        ]);

        try {
            // Send notification with credentials
            try {
                $user->notify(new NewUserCredentials($token, $request->role));
                $notificationSent = true;
            } catch (\Exception $e) {
                \Log::error('Email notification failed: ' . $e->getMessage());
                $notificationSent = false;
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $notificationSent 
                        ? 'User created and notified successfully!'
                        : 'User created successfully but email notification failed. Please note their email and temporary password: ' . $tempPassword
                ]);
            }

            $message = $notificationSent 
                ? 'User created and notified successfully!'
                : 'User created successfully but email notification failed. Please note their email and temporary password: ' . $tempPassword;

            return redirect()->route('admin.home')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
        return redirect('/admin/users')->with('success', 'User created and notified successfully!');
    }

    /**
     * Get user data for editing
     */
    public function editUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at->format('M d, Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
    }

    /**
     * Update user information
     */
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Debug logging
            \Log::info('Update user request', [
                'user_id' => $id,
                'request_data' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);

            // Validate inputs
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => [
                    'required',
                    'min:3',
                    'max:20',
                    'regex:/^[a-zA-Z\s]+$/',
                    \Illuminate\Validation\Rule::unique('users', 'name')->ignore($user->id)
                ],
                'email' => [
                    'required',
                    'email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/',
                    \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)
                ],
                'role' => ['required', 'in:admin,doctor,nurse,lab_technician,cashier']
            ], [
                'name.required' => 'Please enter the user\'s name.',
                'name.min' => 'Name must be 3-20 letters.',
                'name.max' => 'Name must be 3-20 letters.',
                'name.regex' => 'Name can only contain letters and spaces.',
                'name.unique' => 'This name is already taken by another user.',
                'email.required' => 'Please enter the user\'s email address.',
                'email.email' => 'Please enter a valid email address.',
                'email.regex' => 'Email must end with .com and be valid.',
                'email.unique' => 'This email is already registered to another user.',
                'role.required' => 'Please select a role for the user.',
                'role.in' => 'Please select a valid role.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Store old data for logging
            $oldData = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ];

            // Update user
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->save();

            // Log user update
            Report::log(
                'User Updated',
                Report::TYPE_USER_ACTIVITY,
                "Admin updated user: {$user->name}",
                [
                    'user_id' => $user->id,
                    'updated_by' => auth()->user()->name,
                    'updated_by_id' => auth()->id(),
                    'old_data' => $oldData,
                    'new_data' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'update_time' => now()->toISOString()
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at->format('M d, Y')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('User update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of the current admin user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            // Store user data for logging before deletion
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString()
            ];

            // Delete the user
            $user->delete();

            // Log user deletion
            Report::log(
                'User Deleted',
                Report::TYPE_USER_ACTIVITY,
                "Admin deleted user: {$userData['name']}",
                [
                    'deleted_user' => $userData,
                    'deleted_by' => auth()->user()->name,
                    'deleted_by_id' => auth()->id(),
                    'deletion_time' => now()->toISOString()
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('User deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importIcd10(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new Icd10Import, $request->file('file'));

        return back()->with('success', 'ICD-10 data imported.');
    }
}
