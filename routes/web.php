<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Models\User;
use App\Notifications\ResetPasswordMail;
use App\Http\Controllers\LabtechController;
use App\Http\Controllers\LabOrderController;
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
Route::middleware(['auth', 'role:doctor'])->group(function () {
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
Route::middleware(['auth', 'role:nurse'])->group(function () {
    Route::get('/nurse/home', function () {
        return view('nurse.nurse_home');
    });
    
    Route::get('/nurse/appointments', function () {
        return view('nurse.nurse_appointments');
    });
    
    // show patients list
    Route::get('/nurse/patients', [PatientController::class, 'index'])->name('nurse.patients.index');
    
    // Patient edit functionality (nurse) - update by patient_no
    // Delete functionality has been removed from nurse interface as requested
    Route::put('/nurse/patients/{patient_no}', [PatientController::class, 'update'])->name('nurse.patients.update');

    Route::get('/nurse/schedule', function () {
        return view('nurse.nurse_schedule');
    });
    
    Route::get('/nurse/account', function () {
        return view('nurse.nurse_account');
    });
    // Send password reset email for current user from account page
    Route::post('/account/send-reset-email', [App\Http\Controllers\UserController::class, 'sendAccountResetEmail'])->name('account.sendResetEmail');

    // ensure add/store routes exist (if not already present)
    Route::get('/nurse/addPatients', [PatientController::class, 'create'])->name('nurse.addPatients.create');
    Route::post('/nurse/addPatients', [PatientController::class, 'store'])->name('nurse.addPatients.store');
});

// Lab Technician Routes
Route::middleware(['auth', 'role:lab_technician'])->group(function () {
    Route::get('/labtech/home', function () {
        return view('labtech.labtech_home');
    });
    
    Route::get('/labtech/orders', [LabOrderController::class, 'index'])->name('labtech.orders');
    Route::post('/labtech/orders/update-status/{id}', [LabOrderController::class, 'updateStatus'])->name('labtech.orders.updateStatus');
    
    // Returns order details with PDF URL
    Route::get('/labtech/orders/view/{id}', [LabOrderController::class, 'viewOrder'])->name('labtech.orders.view');
    Route::get('/labtech/orders/download-pdf/{id}', [LabOrderController::class, 'downloadPdf'])->name('labtech.orders.downloadPdf');
    
    Route::get('/labtech/patients', [PatientController::class, 'labtechPatients'])->name('labtech.patients');
    Route::get('/labtech/patients/{patient}/test-history', [LabOrderController::class, 'getPatientTestHistory'])->name('labtech.patient.testHistory');
    
    Route::get('/labtech/account', function () {
        return view('labtech.labtech_account');
    });
});

// Lab Order Routes (accessible by nurses)
Route::middleware(['auth', 'role:nurse'])->group(function () {
    Route::post('/lab-orders', [LabOrderController::class, 'store'])->name('lab-orders.store');
});

// Cashier Routes
Route::middleware(['auth', 'role:cashier'])->group(function () {
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
Route::get('/admin/home', [App\Http\Controllers\AdminController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.home');
Route::post('/admin/users/create', [App\Http\Controllers\AdminController::class, 'createUser'])->middleware(['auth', 'role:admin'])->name('admin.createUser');

// Admin User Management Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users/{id}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');
});

// Inventory Routes
Route::middleware(['auth', 'role:inventory'])->group(function () {
    Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/stocks', [App\Http\Controllers\InventoryController::class, 'stocks'])->name('inventory.stocks');
    Route::get('/inventory/stocks/search', [App\Http\Controllers\InventoryController::class, 'search'])->name('inventory.stocks.search');
    Route::post('/inventory/stocks/add', [App\Http\Controllers\InventoryController::class, 'addStock'])->name('inventory.stocks.add');
    Route::delete('/inventory/stocks/{id}', [App\Http\Controllers\InventoryController::class, 'deleteStock'])->name('inventory.stocks.delete');
    Route::get('/inventory/orders', [App\Http\Controllers\InventoryController::class, 'orders'])->name('inventory.orders');
    Route::get('/inventory/reports', [App\Http\Controllers\InventoryController::class, 'reports'])->name('inventory.reports');
    Route::get('/inventory/account', [App\Http\Controllers\InventoryController::class, 'account'])->name('inventory.account');
});

// User approval route removed - users are now assigned roles at creation

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', function () {
        $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(10);
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
Route::post('/admin/icd10/import', [AdminController::class, 'importIcd10'])->middleware(['auth', 'role:admin'])->name('admin.icd10.import');

// ICD-10 live search endpoint (AJAX)
Route::get('/icd10/search', [App\Http\Controllers\Icd10Controller::class, 'search'])->name('icd10.search');

// Procedure search endpoints (AJAX)
Route::get('/procedures/search', [App\Http\Controllers\ProcedureController::class, 'search'])->name('procedures.search');
Route::get('/procedures/category', [App\Http\Controllers\ProcedureController::class, 'getByCategory'])->name('procedures.category');

// Temporary test JSON route for debugging the autocomplete (remove after debugging)
Route::get('/icd10/test-json', function () {
    return response()->json([
        ['code' => 'A00', 'description' => 'Cholera'],
        ['code' => 'B01', 'description' => 'Varicella']
    ]);
})->name('icd10.testJson');

// Room live search endpoint (AJAX) - returns [{name,price}]
Route::get('/rooms/search', [App\Http\Controllers\RoomController::class, 'search'])->name('rooms.search');





