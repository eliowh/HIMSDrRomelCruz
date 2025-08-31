<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Notifications\ResetPasswordMail;
use App\Http\Controllers\LabtechController;

// Home
Route::get('/', function () {
    return view('home');
});

// Login
Route::get('/login', function () {
    return view('login');
})->name('login');

// Register
Route::get('/register', function () {
    return view('register');
});

// Authentication
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

// Password Reset
Route::get('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', [UserController::class, 'resetPassword'])->name('reset-password');
Route::post('/reset-password/{token}', [UserController::class, 'updatePassword'])->name('update-password');
Route::post('/resend-email', [UserController::class, 'resendEmail'])->name('resend-email');
Route::get('/password-reset-success', [UserController::class, 'passwordResetSuccess'])->name('password-reset-success');

// Pending Role
Route::get('/pending', function () {
    return view('pending');
});

// Doctor Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/doctor/home', function () {
        return view('doctor.doctor_home');
    });
    
    Route::get('/doctor/appointments', function () {
        return view('doctor.doctor_appointments');
    });

    Route::get('/doctor/patients', function () {
        return view('doctor.doctor_patients');
    });

    Route::get('/doctor/schedule', function () {
        return view('doctor.doctor_schedule');
    });

    Route::get('/doctor/account', function () {
        return view('doctor.doctor_account');
    });
});

// Nurse Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/nurse/home', function () {
        return view('nurse.nurse_home');
    });
    
    Route::get('/nurse/appointments', function () {
        return view('nurse.nurse_appointments');
    });
    
    Route::get('/nurse/patients', function () {
        return view('nurse.nurse_patients');
    });
    
    Route::get('/nurse/schedule', function () {
        return view('nurse.nurse_schedule');
    });
    
    Route::get('/nurse/account', function () {
        return view('nurse.nurse_account');
    });
});

// Lab Technician Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/labtech/home', function () {
        return view('labtech.labtech_home');
    });
    
    Route::get('/labtech/orders', function () {
        return view('labtech.labtech_orders');
    });
    
    Route::get('/labtech/patients', function () {
        return view('labtech.labtech_patients');
    });
    
    Route::get('/labtech/account', function () {
        return view('labtech.labtech_account');
    });
});

// Cashier Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/cashier/home', function () {
        return view('cashier.cashier_home');
    });
    
    Route::get('/cashier/billing', function () {
        return view('cashier.cashier_billing');
    });
    
    Route::get('/cashier/transactions', function () {
        return view('cashier.cashier_transactions');
    });
    
    Route::get('/cashier/account', function () {
        return view('cashier.cashier_account');
    });
});

// Admin Routes
Route::get('/admin/home', [App\Http\Controllers\AdminController::class, 'index'])->middleware('auth');

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

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/users', function () {
        return view('admin.admin_users');
    });
    
    Route::get('/admin/reports', function () {
        return view('admin.admin_reports');
    });
    
    Route::get('/admin/account', function () {
        return view('admin.admin_account');
    });
});





