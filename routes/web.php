<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BillingController;
use App\Models\User;
use App\Notifications\ResetPasswordMail;
use App\Http\Controllers\LabtechController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LabOrderController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PharmacyController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
| These routes are accessible without authentication
|
*/

// Home Page
Route::get('/', function () {
    return view('home');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
| Login, logout, password reset and related authentication routes
|
*/

// Login Routes
Route::get('/login', function () {
    // Remove any transient resend or generic success messages so they don't show up unexpectedly
    session()->forget('resend_success');
    session()->forget('success');
    return view('login');
})->name('login');

Route::post('/login', [UserController::class, 'login']);

// Helper route to clear transient messages and go to login
Route::get('/back-to-login', function () {
    session()->forget('resend_success');
    session()->forget('success');
    return redirect('/login');
})->name('back-to-login');

// Logout Route
Route::post('/logout', [UserController::class, 'logout']);

// Password Reset Routes
Route::get('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', [UserController::class, 'resetPassword'])->name('reset-password');

// Test mail configuration (remove in production)
Route::get('/test-mail', function() {
    try {
        \Log::info('Testing mail configuration...');
        \Mail::raw('This is a test email from Railway deployment.', function ($message) {
            $message->to('himsdemo111@gmail.com')
                    ->subject('Railway Mail Test');
        });
        return response()->json(['status' => 'Mail sent successfully']);
    } catch (\Exception $e) {
        \Log::error('Mail test failed: ' . $e->getMessage());
        return response()->json(['status' => 'Mail failed', 'error' => $e->getMessage()], 500);
    }
});
Route::post('/reset-password/{token}', [UserController::class, 'updatePassword'])->name('update-password');
Route::post('/resend-email', [UserController::class, 'resendEmail'])->name('resend-email');
Route::get('/password-reset-email-sent', function() { return view('reset_password_email_sent'); })->name('password-reset-email-sent');
Route::get('/password-reset-success', [UserController::class, 'passwordResetSuccess'])->name('password-reset-success');

/*
|--------------------------------------------------------------------------
| DOCTOR ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'doctor' role
| Includes dashboard, appointments, patient management, and schedule
|
*/

// Debug route - remove after fixing (outside middleware)
Route::get('/debug-chat-participants', function() {
    $room = App\Models\ChatRoom::first();
    if ($room) {
        $participants = $room->participants ?? [];
        $users = $room->getParticipantUsers();
        
        return response()->json([
            'room_id' => $room->id,
            'room_name' => $room->name,
            'participants_array' => $participants,
            'valid_users' => $users->map(function($u) {
                return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
            })->toArray(),
            'all_users_in_db' => App\Models\User::select('id', 'name', 'email')->get()->toArray()
        ]);
    }
    return response()->json(['error' => 'No chat rooms found']);
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/doctor/home', [App\Http\Controllers\DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    
    // Appointments
    Route::get('/doctor/appointments', function () {
        return view('doctor.doctor_appointments');
    });

    // Patient Management - same database access as nurses
    Route::get('/doctor/patients', [PatientController::class, 'doctorIndex'])->name('doctor.patients');
    Route::get('/doctor/patients/{id}', [App\Http\Controllers\DoctorController::class, 'showPatient'])->name('doctor.patients.show');
    Route::put('/doctor/patients/{patient_no}', [PatientController::class, 'update'])->name('doctor.patients.update');

    // Allow doctors to view patient medicines (read-only access)
    Route::get('/doctor/api/patients/{patientId}/medicines', [PharmacyController::class, 'getPatientMedicinesApi'])->name('api.patient.medicines.doctor');
    
    // Allow doctors to view patient lab results (read-only access)
    Route::get('/doctor/api/patients/{patientId}/lab-results', [LabOrderController::class, 'getPatientTestHistory'])->name('api.patient.lab-results.doctor');
    
    // Allow doctors to view patient admissions (read-only access)
    Route::get('/doctor/api/patients/{patientId}/admissions', [PatientController::class, 'getPatientAdmissionsApi'])->name('api.patient.admissions.doctor');
    
    // Allow doctors to view active admission (read-only access)
    Route::get('/doctor/api/patients/{patientId}/active-admission', [PatientController::class, 'getActiveAdmission'])->name('api.patient.active-admission.doctor');

    // Finalize admission (doctor sets final diagnosis)
    Route::post('/doctor/admissions/{admissionId}/finalize', [PatientController::class, 'finalizeAdmission'])->name('doctor.admissions.finalize');
    
    // Allow doctors to view lab result PDFs
    Route::get('/doctor/lab-orders/{orderId}/view-pdf', [LabOrderController::class, 'viewPdf'])->name('doctor.lab.viewPdf');
    
    // Lab Results page for doctors
    Route::get('/doctor/results', [LabOrderController::class, 'doctorResults'])->name('doctor.results');
    Route::post('/doctor/results/save-analysis', [LabOrderController::class, 'saveAnalysis'])->name('doctor.results.saveAnalysis');
    Route::get('/doctor/results/{labOrderId}/analysis-pdf', [LabOrderController::class, 'generateAnalysisPdf'])->name('doctor.results.analysis-pdf');

    // Chat/Messaging Routes (Legacy)
    // Doctors autosuggest (used by doctor edit forms)
    Route::get('/doctors/search', function (\Illuminate\Http\Request $request) {
        $q = $request->query('q', '');
        $matches = \DB::table('doctorslist')
            ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
            ->whereRaw('`COL 1` like ?', ["%{$q}%"])
            ->orderByRaw('`COL 1`')
            ->limit(50)
            ->get();
        return response()->json($matches);
    })->name('doctors.search.doctor');

    // Doctors validation endpoint (validates an exact name)
    Route::post('/doctors/validate', function (\Illuminate\Http\Request $request) {
        $name = trim((string)$request->input('name', ''));
        if (!$name) {
            return response()->json(['valid' => false]);
        }

        $match = \DB::table('doctorslist')
            ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
            ->whereRaw('LOWER(`COL 1`) = ?', [strtolower($name)])
            ->first();

        if (! $match) {
            $normalized = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $name));
            $match = \DB::table('doctorslist')
                ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
                ->whereRaw('LOWER(REGEXP_REPLACE(`COL 1`, "[^[:alnum:][:space:]]", "")) = ?', [$normalized])
                ->first();
        }

        return response()->json([
            'valid' => (bool)$match,
            'name' => $match ? $match->name : null,
            'type' => $match ? $match->type : null
        ]);
    })->name('doctors.validate.doctor');

    // Chat/Messaging Routes
    Route::get('/doctor/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/doctor/chat/{id}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/doctor/chat/create-for-patient', [App\Http\Controllers\ChatController::class, 'createOrGetForPatient'])->name('chat.createForPatient');
    Route::post('/doctor/chat/{id}/message', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::post('/doctor/chat/{id}/add-participant', [App\Http\Controllers\ChatController::class, 'addParticipant'])->name('chat.addParticipant');
    Route::delete('/doctor/chat/{id}/remove-participant', [App\Http\Controllers\ChatController::class, 'removeParticipant'])->name('chat.removeParticipant');
    Route::get('/doctor/chat/{id}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.getMessages');
    Route::patch('/doctor/chat/{id}/archive', [App\Http\Controllers\ChatController::class, 'archive'])->name('chat.archive');
    Route::get('/doctor/chat/attachment/{id}/download', [App\Http\Controllers\ChatController::class, 'downloadAttachment'])->name('chat.downloadAttachment');

    // Schedule
    Route::get('/doctor/schedule', function () {
        return view('doctor.doctor_schedule');
    });
    
    // Test route
    Route::get('/doctor/test', function () {
        return view('doctor.test');
    });
});

/*
|--------------------------------------------------------------------------
| NURSE ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'nurse' role
| Includes dashboard, appointments, patient management, and schedule
|
*/

Route::middleware(['auth', 'role:nurse'])->group(function () {
    // Dashboard
    Route::get('/nurse/home', function () {
        $patients = \App\Models\Patient::orderBy('created_at', 'desc')->get();
        return view('nurse.nurse_home', compact('patients'));
    });
    
    // Appointments
    Route::get('/nurse/appointments', function () {
        return view('nurse.nurse_appointments');
    });
    
    // Patient Management
    Route::get('/nurse/patients', [PatientController::class, 'index'])->name('nurse.patients.index');
    Route::get('/nurse/addPatients', [PatientController::class, 'create'])->name('nurse.addPatients.create');
    Route::post('/nurse/addPatients', [PatientController::class, 'store'])->name('nurse.addPatients.store');
    Route::put('/nurse/patients/{patient_no}', [PatientController::class, 'update'])->name('nurse.patients.update');

    // Schedule
    Route::get('/nurse/schedule', function () {
        return view('nurse.nurse_schedule');
    });
    
    // Doctors autosuggest (used by nurse forms)
    Route::get('/doctors/search', function (\Illuminate\Http\Request $request) {
        $q = $request->query('q', '');
        // doctorslist columns are named with spaces (e.g. 'COL 1', 'COL 2') - alias them to name/type for frontend
        $matches = \DB::table('doctorslist')
            ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
            ->whereRaw('`COL 1` like ?', ["%{$q}%"])
            ->orderByRaw('`COL 1`')
            ->limit(50)
            ->get();
        return response()->json($matches);
    })->name('doctors.search');

    // Doctors validation endpoint used by autosuggest (validates an exact name)
    Route::post('/doctors/validate', function (\Illuminate\Http\Request $request) {
        $name = trim((string)$request->input('name', ''));
        if (!$name) {
            return response()->json(['valid' => false]);
        }

        // 1) Try exact case-insensitive match
        $match = \DB::table('doctorslist')
            ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
            ->whereRaw('LOWER(`COL 1`) = ?', [strtolower($name)])
            ->first();

        // 2) If not found, try punctuation-insensitive normalized match
        if (! $match) {
            $normalized = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $name));
            $all = \DB::table('doctorslist')->selectRaw('`COL 1` as `name`, `COL 2` as `type`')->get();
            foreach ($all as $d) {
                $dnormalized = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $d->name));
                if ($dnormalized === $normalized) {
                    $match = $d;
                    break;
                }
            }
        }

        // 3) Fallback: if a single LIKE candidate exists, accept it
        if (! $match) {
            $candidates = \DB::table('doctorslist')
                ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
                ->whereRaw('`COL 1` like ?', ["%{$name}%"])
                ->limit(5)
                ->get();
            if ($candidates->count() === 1) {
                $match = $candidates->first();
            }
        }

        if ($match) {
            return response()->json(['valid' => true, 'type' => $match->type]);
        }

        return response()->json(['valid' => false]);
    })->name('doctors.validate');

    // Allow nurses to query stocks reference for medicine autosuggest in nurse modal
    Route::get('/nurse/pharmacy/stocks-reference', [PharmacyController::class, 'getStocksReference'])->name('nurse.pharmacy.stocks.reference');
    
    // Allow nurses to view patient medicines 
    Route::get('/api/patients/{patientId}/medicines', [PharmacyController::class, 'getPatientMedicinesApi'])->name('api.patient.medicines.nurse');
    
    // Allow nurses to view patient lab results
    Route::get('/api/patients/{patientId}/lab-results', [LabOrderController::class, 'getPatientTestHistory'])->name('api.patient.lab-results.nurse');
    
    // Allow nurses to view patient admissions
    Route::get('/api/patients/{patientId}/admissions', [PatientController::class, 'getPatientAdmissionsApi'])->name('api.patient.admissions.nurse');
    
    // Allow nurses to get active admission for lab/medicine requests
    Route::get('/api/patients/{patientId}/active-admission', [PatientController::class, 'getActiveAdmission'])->name('api.patient.active-admission.nurse');
    
    // Allow nurses to get current admission data for edit modal
    Route::get('/patients/{id}/current-admission', [PatientController::class, 'getCurrentAdmission'])->name('api.patient.current-admission.nurse');
    
    // Allow nurses to create new admissions
    Route::post('/nurse/admissions', [PatientController::class, 'createAdmission'])->name('nurse.admissions.create');
    
    // Allow nurses to discharge patients
    Route::post('/nurse/discharge-patient/{admissionId}', [PatientController::class, 'dischargePatient'])->name('nurse.discharge.patient');
    
    // Allow nurses to view lab result PDFs
    Route::get('/nurse/lab-orders/{orderId}/view-pdf', [LabOrderController::class, 'viewPdf'])->name('nurse.lab.viewPdf');
    
    // Medicine request history for nurses
    Route::get('/nurse/medicine-request-history', [PharmacyController::class, 'nurseRequestHistory'])->name('nurse.medicine.request.history');
    Route::get('/nurse/pharmacy-requests/{id}', [PharmacyController::class, 'showRequest'])->name('nurse.pharmacy.requests.show');
});

/*
|--------------------------------------------------------------------------
| LAB TECHNICIAN ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'lab_technician' role
| Includes dashboard, order management, and patient records
|
*/

Route::middleware(['auth', 'role:lab_technician'])->group(function () {
    // Dashboard
    Route::get('/labtech/home', function () {
        return view('labtech.labtech_home');
    });
    
    // Order Management
    Route::get('/labtech/orders', [LabOrderController::class, 'index'])->name('labtech.orders');
    Route::post('/labtech/orders/update-status/{id}', [LabOrderController::class, 'updateStatus'])->name('labtech.orders.updateStatus');
    Route::get('/labtech/orders/view/{id}', [LabOrderController::class, 'viewOrder'])->name('labtech.orders.view');
    Route::get('/labtech/orders/download-pdf/{id}', [LabOrderController::class, 'downloadPdf'])->name('labtech.orders.downloadPdf');
    Route::get('/labtech/orders/{id}/details', [LabOrderController::class, 'getOrderDetails'])->name('labtech.orders.details');
    Route::post('/labtech/orders/complete-with-pdf/{id}', [LabOrderController::class, 'completeWithPdf'])->name('labtech.orders.completeWithPdf');
    
    // Patient Management
    Route::get('/labtech/patients', [PatientController::class, 'labtechPatients'])->name('labtech.patients');
    Route::get('/labtech/patients/{patient}/test-history', [LabOrderController::class, 'getPatientTestHistory'])->name('labtech.patient.testHistory');
    Route::get('/labtech/orders/{orderId}/check-pdf', [LabOrderController::class, 'checkPdf'])->name('labtech.order.checkPdf');
    Route::get('/labtech/orders/{orderId}/view-pdf', [LabOrderController::class, 'viewPdf'])->name('labtech.order.viewPdf');
    // Dynamic lab result template endpoints
    Route::get('/labtech/lab-templates', [LabOrderController::class, 'listTemplates'])->name('labtech.lab.templates');
    Route::post('/labtech/orders/{orderId}/generate-template', [LabOrderController::class, 'generateResultPdf'])->name('labtech.orders.generateTemplate');
});

/*
|--------------------------------------------------------------------------
| LAB ORDER ROUTES (FOR NURSES)
|--------------------------------------------------------------------------
| Lab order creation routes accessible by nurses
|
*/

Route::middleware(['auth', 'role:nurse'])->group(function () {
    Route::post('/lab-orders', [LabOrderController::class, 'store'])->name('lab-orders.store');
    // Nurse -> Pharmacy medicine request endpoint (submit request to pharmacy)
    Route::post('/nurse/pharmacy-orders', [App\Http\Controllers\PharmacyController::class, 'storeNurseRequest'])->name('nurse.pharmacy.store');
});

/*
|--------------------------------------------------------------------------
| CASHIER ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'cashier' role
| Includes dashboard, billing, and transactions
|
*/

Route::middleware(['auth', 'role:cashier'])->group(function () {
    // Dashboard
    Route::get('/cashier/home', [App\Http\Controllers\CashierController::class, 'home']);
    
    // Billing Management
    Route::get('/cashier/billing', [App\Http\Controllers\CashierController::class, 'billing']);
    
    // Billing View
    Route::get('/cashier/billing/{id}/view', [App\Http\Controllers\CashierController::class, 'viewBilling']);
    
    // Payment Processing (unpaid functionality removed for security)
    Route::post('/cashier/billing/{id}/mark-as-paid', [App\Http\Controllers\CashierController::class, 'markAsPaid']);
    
    // Receipt Management
    Route::get('/cashier/billing/{id}/receipt', [App\Http\Controllers\CashierController::class, 'viewReceipt'])->name('cashier.billing.receipt');
    Route::get('/cashier/billing/{id}/receipt/download', [App\Http\Controllers\CashierController::class, 'downloadReceipt'])->name('cashier.billing.receipt.download');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'admin' role
| Includes dashboard, user management, room management, patient records, and reports
|
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/admin/home', function () {
        return view('admin.admin_home');
    })->name('admin.home');
    
    // User Management
    Route::post('/admin/users/create', [App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.createUser');
    Route::get('/admin/users/{id}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // User List with Search and Filtering
    Route::get('/admin/users', function (\Illuminate\Http\Request $request) {
        $search = $request->get('search');
        $role = $request->get('role');
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort column
        $allowedSortColumns = ['name', 'email', 'role', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';
        
        $query = \App\Models\User::orderBy($sortBy, $sortDirection);
        
        // Apply search and role filters
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        if ($role) {
            $query->where('role', $role);
        }
        
        // Apply pagination for all results (with or without search/filter)
        $users = $query->paginate(10);
        
        // Preserve search and filter parameters in pagination links
        $users->appends(request()->query());
        
        return view('admin.admin_users', compact('users'));
    });
    
    // Room Management
    Route::get('/admin/rooms', [AdminController::class, 'rooms'])->name('admin.rooms');
    Route::post('/admin/rooms/create', [AdminController::class, 'createRoom'])->name('admin.rooms.create');
    Route::post('/admin/rooms/edit', [AdminController::class, 'editRoom'])->name('admin.rooms.edit');
    Route::put('/admin/rooms/update', [AdminController::class, 'updateRoom'])->name('admin.rooms.update');
    
    // Patient Records Management
    Route::get('/admin/patients', [AdminController::class, 'patients'])->name('admin.patients');
    Route::get('/admin/patients/{id}/details', [AdminController::class, 'getPatientDetails'])->name('admin.patients.details');
    Route::post('/admin/patients/{id}/update', [AdminController::class, 'updatePatient'])->name('admin.patients.update');
    Route::patch('/admin/patients/{id}/status', [AdminController::class, 'updatePatientStatus'])->name('admin.patients.update-status');
    
    // Report Management
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::post('/admin/reports/generate', [ReportController::class, 'generate'])->name('admin.reports.generate');
    Route::get('/admin/reports/{report}', [ReportController::class, 'show'])->name('admin.reports.show');
    Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy'])->name('admin.reports.destroy');
    Route::get('/admin/reports/{report}/export', [ReportController::class, 'export'])->name('admin.reports.export');
    
    // ICD-10 Data Import
    Route::post('/admin/icd10/import', [AdminController::class, 'importIcd10'])->name('admin.icd10.import');
});

/*
|--------------------------------------------------------------------------
| INVENTORY ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'inventory' role
| Includes dashboard, stock management, order management, and reports
|
*/

Route::middleware(['auth', 'role:inventory'])->group(function () {
    // Dashboard
    Route::get('/inventory/home', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.home');
    
    // Stock Management
    Route::get('/inventory/stocks', [App\Http\Controllers\InventoryController::class, 'stocks'])->name('inventory.stocks');
    Route::get('/inventory/stocks/search', [App\Http\Controllers\InventoryController::class, 'search'])->name('inventory.stocks.search');
    Route::post('/inventory/stocks/add', [App\Http\Controllers\InventoryController::class, 'addStock'])->name('inventory.stocks.add');
    Route::delete('/inventory/stocks/{id}', [App\Http\Controllers\InventoryController::class, 'deleteStock'])->name('inventory.stocks.delete');
    Route::patch('/inventory/stocks/{id}', [App\Http\Controllers\InventoryController::class, 'updateStock'])->name('inventory.stocks.update');
    
    // Order Management
    Route::get('/inventory/orders', [App\Http\Controllers\InventoryController::class, 'orders'])->name('inventory.orders');
    Route::post('/inventory/orders/{id}/update-status', [App\Http\Controllers\InventoryController::class, 'updateOrderStatus'])->name('inventory.orders.updateStatus');
    
    // API Routes for stocks reference lookup
    Route::get('/inventory/stocks-reference', [App\Http\Controllers\InventoryController::class, 'getStocksReference'])->name('inventory.stocks.reference');
    Route::get('/inventory/stocks-reference/{itemCode}', [App\Http\Controllers\InventoryController::class, 'getStockByItemCode'])->name('inventory.stocks.item');
    
    // Reports
    Route::get('/inventory/reports', [App\Http\Controllers\InventoryController::class, 'reports'])->name('inventory.reports');
    
    Route::post('/inventory/stocks/add-from-order', [App\Http\Controllers\InventoryController::class, 'addStockFromOrder'])->name('inventory.stocks.addFromOrder');
});

/*
|--------------------------------------------------------------------------
| PHARMACY ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'pharmacy' role
| Includes dashboard and order management
|
*/

Route::middleware(['auth', 'role:pharmacy'])->group(function () {
    // Dashboard
    Route::get('/pharmacy/home', [App\Http\Controllers\PharmacyController::class, 'home'])->name('pharmacy.home');
    
    // Order Management
    Route::get('/pharmacy/orders', [App\Http\Controllers\PharmacyController::class, 'orders'])->name('pharmacy.orders');
    Route::post('/pharmacy/orders', [App\Http\Controllers\PharmacyController::class, 'storeOrder'])->name('pharmacy.orders.store');
    Route::put('/pharmacy/orders/{id}', [App\Http\Controllers\PharmacyController::class, 'updateOrder'])->name('pharmacy.orders.update');
    Route::delete('/pharmacy/orders/{id}', [App\Http\Controllers\PharmacyController::class, 'deleteOrder'])->name('pharmacy.orders.delete');
    Route::post('/pharmacy/orders/{id}/cancel', [App\Http\Controllers\PharmacyController::class, 'cancelOrder'])->name('pharmacy.orders.cancel');
    
    // API Routes for dropdown population
    Route::get('/pharmacy/stocks-reference', [App\Http\Controllers\PharmacyController::class, 'getStocksReference'])->name('pharmacy.stocks.reference');
    Route::get('/pharmacy/stocks-reference/{itemCode}', [App\Http\Controllers\PharmacyController::class, 'getStockByItemCode'])->name('pharmacy.stocks.item');
    
    // Nurse-submitted medicine requests (new tab)
    Route::get('/pharmacy/requests', [App\Http\Controllers\PharmacyController::class, 'nurseRequests'])->name('pharmacy.requests');
    Route::get('/pharmacy/requests/{id}', [App\Http\Controllers\PharmacyController::class, 'showRequest'])->name('pharmacy.requests.show');
    Route::post('/pharmacy/requests/{id}/dispense', [App\Http\Controllers\PharmacyController::class, 'dispenseRequest'])->name('pharmacy.requests.dispense');
    Route::post('/pharmacy/requests/{id}/cancel', [App\Http\Controllers\PharmacyController::class, 'cancelRequest'])->name('pharmacy.requests.cancel');
    
    // Patient medicines (dispensed medicines)
    Route::get('/pharmacy/patient-medicines', [App\Http\Controllers\PharmacyController::class, 'patientMedicines'])->name('pharmacy.patient.medicines');
    Route::get('/pharmacy/patient-medicines/{patientId}', [App\Http\Controllers\PharmacyController::class, 'patientMedicinesByPatient'])->name('pharmacy.patient.medicines.by.patient');
    
    // API route moved to nurse group since nurses access patient data
    
    // Stocks management (list stocks reference)
    Route::get('/pharmacy/stocks', [App\Http\Controllers\PharmacyController::class, 'stocks'])->name('pharmacy.stocks');
    Route::get('/pharmacy/stockspharmacy', [App\Http\Controllers\PharmacyController::class, 'stocksPharmacy'])->name('pharmacy.stockspharmacy');
});

/*
|--------------------------------------------------------------------------
| BILLING ROUTES
|--------------------------------------------------------------------------
| Routes accessible only by users with 'billing' role
| Includes dashboard, invoice management, and payment processing
|
*/

Route::middleware(['auth', 'role:billing'])->group(function () {
    // Dashboard - Patient Billing List
    Route::get('/billing/home', [App\Http\Controllers\BillingController::class, 'index'])->name('billing.dashboard');
    
    // New Billing Creation
    Route::get('/billing/billing-create', [App\Http\Controllers\BillingController::class, 'create'])->name('billing.create');
    Route::post('/billing/billing-create', [App\Http\Controllers\BillingController::class, 'store'])->name('billing.store');
    
    // AJAX endpoints for billing (MUST come before wildcard routes)
    Route::get('/billing/search-patients', [App\Http\Controllers\BillingController::class, 'searchPatients'])->name('billing.search.patients');
    Route::post('/billing/check-philhealth', [App\Http\Controllers\BillingController::class, 'checkPhilhealth'])->name('billing.check.philhealth');
    Route::get('/billing/icd-rates', [App\Http\Controllers\BillingController::class, 'getIcdRates'])->name('billing.icd.rates');
    Route::get('/billing/patient-services/{patient_id}', [App\Http\Controllers\BillingController::class, 'getPatientServices'])->name('billing.patient.services');
    Route::get('/billing/patient-admissions/{patient_id}', [App\Http\Controllers\BillingController::class, 'getPatientAdmissions'])->name('billing.patient.admissions');
    
    // Billing Status Management
    Route::post('/billing/{billing}/mark-as-paid', [App\Http\Controllers\BillingController::class, 'markAsPaid'])->name('billing.mark.paid');
    
    // Individual Billing Management (wildcard routes come LAST)
    Route::get('/billing/{billing}', [App\Http\Controllers\BillingController::class, 'show'])->name('billing.show');
    Route::get('/billing/{billing}/edit', [App\Http\Controllers\BillingController::class, 'edit'])->name('billing.edit');
    Route::put('/billing/{billing}', [App\Http\Controllers\BillingController::class, 'update'])->name('billing.update');
    // Delete functionality removed for security - preventing billing theft and data loss
    Route::get('/billing/{billing}/receipt', [App\Http\Controllers\BillingController::class, 'exportReceipt'])->name('billing.export.receipt');
});

/*
|--------------------------------------------------------------------------
| API ROUTES (AJAX ENDPOINTS)
|--------------------------------------------------------------------------
| These routes provide AJAX endpoints for live search and data retrieval
| Used by various components throughout the application
|
*/

// ICD-10 Search API
Route::get('/icd10/search', [App\Http\Controllers\Icd10Controller::class, 'search'])->name('icd10.search');

// Test endpoint for ICD-10 debugging (remove after debugging)
Route::get('/icd10/test-json', function () {
    return response()->json([
        ['code' => 'A00', 'description' => 'Cholera'],
        ['code' => 'B01', 'description' => 'Varicella']
    ]);
})->name('icd10.testJson');

// Procedure Search API
Route::get('/procedures/search', [App\Http\Controllers\ProcedureController::class, 'search'])->name('procedures.search');
Route::get('/procedures/category', [App\Http\Controllers\ProcedureController::class, 'getByCategory'])->name('procedures.category');

// Room Search API
Route::get('/rooms/search', [App\Http\Controllers\RoomController::class, 'search'])->name('rooms.search');

// Doctors Search API (public route for both nurses and doctors)
Route::get('/doctors/search', function (\Illuminate\Http\Request $request) {
    $q = $request->query('q', '');
    $matches = \DB::table('doctorslist')
        ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
        ->whereRaw('`COL 1` like ?', ["%{$q}%"])
        ->orderByRaw('`COL 1`')
        ->limit(50)
        ->get();
    return response()->json($matches);
})->name('doctors.search.public');

// Validation APIs
Route::post('/icd10/validate', [App\Http\Controllers\Icd10Controller::class, 'validate'])->name('icd10.validate');
Route::post('/rooms/validate', [App\Http\Controllers\RoomController::class, 'validate'])->name('rooms.validate');

// Doctors validation endpoint (public route for both nurses and doctors)
Route::post('/doctors/validate', function (\Illuminate\Http\Request $request) {
    $name = trim((string)$request->input('name', ''));
    if (!$name) {
        return response()->json(['valid' => false]);
    }

    $match = \DB::table('doctorslist')
        ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
        ->whereRaw('LOWER(`COL 1`) = ?', [strtolower($name)])
        ->first();

    if (! $match) {
        $normalized = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $name));
        $match = \DB::table('doctorslist')
            ->selectRaw('`COL 1` as `name`, `COL 2` as `type`')
            ->whereRaw('LOWER(REGEXP_REPLACE(`COL 1`, "[^[:alnum:][:space:]]", "")) = ?', [$normalized])
            ->first();
    }

    return response()->json([
        'valid' => (bool)$match,
        'name' => $match ? $match->name : null,
        'type' => $match ? $match->type : null
    ]);
})->name('doctors.validate.public');

/*
|--------------------------------------------------------------------------
| ASSET FALLBACK ROUTES (FOR RAILWAY DEPLOYMENT)
|--------------------------------------------------------------------------
| These routes serve static assets when the web server fails to serve them
| This is a fallback for Railway deployment issues with static files
|
*/

// Fallback route for CSS files
Route::get('/css/{file}', function ($file) {
    $path = public_path('css/' . $file);
    
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
    
    abort(404);
})->where('file', '.*\.css$');

// Fallback route for nested CSS files
Route::get('/css/{folder}/{file}', function ($folder, $file) {
    $path = public_path('css/' . $folder . '/' . $file);
    
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
    
    abort(404);
})->where('file', '.*\.css$');

// Fallback route for JS files
Route::get('/js/{file}', function ($file) {
    $path = public_path('js/' . $file);
    
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
    
    abort(404);
})->where('file', '.*\.js$');





