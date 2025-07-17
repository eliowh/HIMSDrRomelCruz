
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\User;

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

Route::get('/pending', function () {
    return view('pending');
});

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



