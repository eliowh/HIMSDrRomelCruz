<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function register(Request $request){
    $incomingFields = $request->validate([
        'name' => ['required', 'min:3', 'max:20', Rule::unique('users', 'name')],
        'email' => ['required', 'email', Rule::unique('users', 'email')],
        'password' => ['required','min:8', 'max:20'],
    ]);

    $incomingFields['password'] = bcrypt($incomingFields['password']);
    User::create($incomingFields);

    return redirect('/login')->with('success', 'Registration successful! Please login.');
}

    public function login(Request $request){
         $incomingFields = $request->validate([
            'loginemail' => ['required', 'email',],
            'loginpassword' => ['required','min:8', 'max:20'],
        ]);
        if(auth()->attempt(['email' => $incomingFields['loginemail'], 'password' => $incomingFields['loginpassword']])) {
        $request->session()->regenerate();
        return redirect('/'); }
        return back()->withErrors(['loginemail' => 'Invalid credentials.'])->onlyInput('loginemail');
    }

    public function logout(){
        auth()->logout();
        return redirect('/login');
    }
}
