<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\RoleAssigned;

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
    
}
