<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Models\User;
use App\Notifications\ResetPasswordMail;
use App\Http\Controllers\LabtechController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AdminController;

// Home
Route::get('/', function () {
    return view('home');
});

// Login - clear transient success messages to avoid showing stale flash notices
Route::get('/login', function () {
    // Remove any transient resend or generic success messages so they don't show up unexpectedly
    session()->forget('resend_success');
    session()->forget('success');
    return view('login');
})->name('login');

// A simple helper route to clear transient messages and go to login
Route::get('/back-to-login', function () {
    session()->forget('resend_success');
    session()->forget('success');
    return redirect('/login');
})->name('back-to-login');

// Authentication
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

// Password Reset
Route::get('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', [UserController::class, 'resetPassword'])->name('reset-password');
Route::post('/reset-password/{token}', [UserController::class, 'updatePassword'])->name('update-password');
Route::post('/resend-email', [UserController::class, 'resendEmail'])->name('resend-email');
Route::get('/password-reset-email-sent', function() { return view('reset_password_email_sent'); })->name('password-reset-email-sent');
Route::get('/password-reset-success', [UserController::class, 'passwordResetSuccess'])->name('password-reset-success');

// No more pending role route needed

// Doctor Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/doctor/home', [App\Http\Controllers\DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    
    Route::get('/doctor/appointments', function () {
        return view('doctor.doctor_appointments');
    });

    // Patient Management Routes
    Route::get('/doctor/patients', [App\Http\Controllers\DoctorController::class, 'patients'])->name('doctor.patients');
    Route::get('/doctor/patients/{id}', [App\Http\Controllers\DoctorController::class, 'showPatient'])->name('doctor.patients.show');

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
    
    // show patients list
    Route::get('/nurse/patients', [PatientController::class, 'index'])->name('nurse.patients.index')->middleware('auth');

    Route::get('/nurse/schedule', function () {
        return view('nurse.nurse_schedule');
    });
    
    Route::get('/nurse/account', function () {
        return view('nurse.nurse_account');
    });

    // ensure add/store routes exist (if not already present)
    Route::get('/nurse/addPatients', [PatientController::class, 'create'])->name('nurse.addPatients.create')->middleware('auth');
    Route::post('/nurse/addPatients', [PatientController::class, 'store'])->name('nurse.addPatients.store')->middleware('auth');
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
Route::get('/admin/home', [App\Http\Controllers\AdminController::class, 'index'])->middleware('auth')->name('admin.home');
Route::post('/admin/users/create', [App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.createUser');

// Admin User Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/users/{id}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');
});

// User approval route removed - users are now assigned roles at creation

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/users', function () {
        $users = \App\Models\User::all();
        return view('admin.admin_users', compact('users'));
    });
    
    // Report Routes
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::post('/admin/reports/generate', [ReportController::class, 'generate'])->name('admin.reports.generate');
    Route::get('/admin/reports/{report}', [ReportController::class, 'show'])->name('admin.reports.show');
    Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy'])->name('admin.reports.destroy');
    Route::get('/admin/reports/{report}/export', [ReportController::class, 'export'])->name('admin.reports.export');
    
    Route::get('/admin/account', function () {
        return view('admin.admin_account');
    });
});

// ICD-10 Import Route
Route::post('/admin/icd10/import', [AdminController::class, 'importIcd10'])->name('admin.icd10.import');





