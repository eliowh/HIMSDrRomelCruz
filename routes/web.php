
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Notifications\ResetPasswordMail;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
});


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

Route::get('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', [UserController::class, 'resetPassword'])->name('reset-password');
Route::post('/reset-password/{token}', [UserController::class, 'updatePassword'])->name('update-password');
Route::post('/resend-email', [UserController::class, 'resendEmail'])->name('resend-email');
Route::get('/password-reset-success', [UserController::class, 'passwordResetSuccess'])->name('password-reset-success');

Route::get('/pending', function () {
    return view('pending');
});

Route::get('/doctor/home', function () {
    return view('doctor.doctor_home');
})->middleware('auth');


Route::get('/admin/userapproval', function () {
    $pendingUsers = User::where('role', 'pending')->get();
    return view('admin.admin_userapproval', compact('pendingUsers'));
})->middleware('auth');

Route::post('/admin/assign-role/{user}', function ($userId) {
    $user = User::findOrFail($userId);
    $role = request('role');
    $user->role = $role;
    $user->save();
    return redirect('/admin/userapproval')->with('success', 'Role assigned successfully!');
})->middleware('auth');


Route::get('/admin/home', [App\Http\Controllers\AdminController::class, 'index'])->middleware('auth');

Route::get('/test-notification', function () {
    $user = User::where('email', 'cafekam545@pacfut.com')->first();
    $notification = new RoleAssigned('doctor');
    $user->notify($notification);
    return 'Notification sent!';
});

Route::get('/test-reset-password', function () {
    $user = User::where('email', 'cafekam545@pacfut.com')->first();
    $notification = new ResetPasswordMail($user, 'reset-token');
    $user->notify($notification);
    return 'Reset password notification sent!';
});

