<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\RoleAssigned;
use App\Notifications\NewUserCredentials;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

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
    public function userApproval()
    {
        $pendingUsers = User::where('role', 'pending')->get();
        return view('admin.admin_userapproval', compact('pendingUsers'));
    }

    public function assignRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $role = $request->input('role');
        $user->role = $role;
        $user->save();
        // Send notification
        $user->notify(new RoleAssigned($role));
        return redirect('/admin/userapproval')->with('success', 'Role assigned and user notified successfully!');
    }
    
    public function showCreateUserForm()
    {
        return view('admin.create_user');
    }

    public function createUser(Request $request)
    {
        // Validate inputs
        $request->validate([
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

        // Generate a secure temporary password
        $tempPassword = str_replace(['+', '/', '='], ['@', '$', '#'], base64_encode(random_bytes(8)));

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'role' => $request->role
        ]);

        try {
            // Send notification with credentials
            try {
                $user->notify(new NewUserCredentials($tempPassword, $request->role));
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
}
