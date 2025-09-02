<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\RoleAssigned;
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
        // validate inputs
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        // create user with a dummy password (user will reset it)
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make('temporary123'), // user won't use this
        ]);

        // generate password reset token
        $token = Password::createToken($user);

        // build reset link
        $resetLink = url("/password/reset/$token?email=" . urlencode($user->email));

        // send email to user
        Mail::send('emails.new_user', [
            'resetLink' => $resetLink,
            'userName'  => $user->name,
        ], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Your account has been created');
        });

        return redirect('/admin/userapproval')->with('success', 'User created and notified successfully!');
    }
}
