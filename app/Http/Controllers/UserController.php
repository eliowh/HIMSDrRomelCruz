<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function register(Request $request){
        $incomingFields = $request->validate([
            'name' => [
                'required',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z\s]+$/', // Only letters and spaces
                Rule::unique('users', 'name')
            ],
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/', // Only .com domains allowed
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'min:8',
                'max:20',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', // At least 1 uppercase, 1 lowercase, 1 number, 1 special char
            ],
        ], [
            'name.required' => 'Please enter your name.',
            'name.min' => 'Name must be 3-20 letters.',
            'name.max' => 'Name must be 3-20 letters.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'name.unique' => 'This name is already taken.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Email must end with .com and be valid.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Please enter a password.',
            'password.min' => 'Password must be 8-20 characters and strong.',
            'password.max' => 'Password must be 8-20 characters and strong.',
            'password.regex' => 'Password must have uppercase, lowercase, number, and special character.',
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);
        $incomingFields['role'] = 'pending';
        User::create($incomingFields);

        return redirect('/login')->with('popup', 'Registration successful! Please login.');
    }

    public function login(Request $request){
    $incomingFields = $request->validate([
        'loginemail' => ['required', 'email'],
        'loginpassword' => ['required','min:8', 'max:20'],
    ], [
        'loginemail.required' => 'Please enter your email address.',
        'loginemail.email' => 'Please enter a valid email address.',
        'loginpassword.required' => 'Please enter your password.',
        'loginpassword.min' => 'Password must be at least 8 characters.',
        'loginpassword.max' => 'Password must be no more than 20 characters.',
    ]);

    $user = User::where('email', $incomingFields['loginemail'])->first();
    if ($user && \Hash::check($request->input('loginpassword'), $user->password)) {
        $request->session()->regenerate();
        \Auth::login($user);
        if ($user->role === 'pending') {
            return redirect('/pending');
        } elseif ($user->role === 'admin') {
            return redirect('/admin/home');
        } else {
            return redirect('/');
        }
    } else {
        if (!$user) {
            return back()->withErrors(['loginemail' => 'This email is invalid.'])->onlyInput('loginemail');
        } else {
            return back()->withErrors(['loginpassword' => 'Incorrect password.'])->onlyInput('loginpassword');
        }
    }
}

    public function logout(){
        auth()->logout();
        return redirect('/login');
    }
}
