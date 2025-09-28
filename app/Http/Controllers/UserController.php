<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Notifications\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
        // This method is now deprecated since users can't register themselves
        // Only admins can create accounts
        
        return redirect('/login')->with('error', 'Self-registration is disabled. Please contact the administrator for an account.');
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
        
        // Log successful login
        Report::log(
            'User Login',
            Report::TYPE_LOGIN_REPORT,
            "User {$user->name} ({$user->role}) logged in successfully",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'login_time' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ],
            $user->id
        );
        
        if (!$user->role) {
            auth()->logout();
            return redirect('/login')->with('error', 'Your account has not yet been assigned a role. Please contact an administrator.');
        } elseif ($user->role === 'admin') {
            return redirect('/admin/home');
        } elseif ($user->role === 'doctor') {
            return redirect('/doctor/home');
        } elseif ($user->role === 'nurse') {
            return redirect('/nurse/home');
        } elseif ($user->role === 'lab_technician') {
            return redirect('/labtech/home');
        } elseif ($user->role === 'cashier') {
            return redirect('/cashier/home');
        } elseif ($user->role === 'inventory') {
            return redirect('/inventory/home');
        } elseif ($user->role === 'pharmacy') {
            return redirect('/pharmacy/home');
        } elseif ($user->role === 'billing') {
            return redirect('/billing/home');
        } else {
            return redirect('/');
        }
    } else {
        // Log failed login attempt
        $failedEmail = $incomingFields['loginemail'];
        Report::log(
            'Failed Login Attempt',
            Report::TYPE_LOGIN_REPORT,
            "Failed login attempt for email: {$failedEmail}",
            [
                'attempted_email' => $failedEmail,
                'failure_reason' => !$user ? 'Email not found' : 'Invalid password',
                'attempt_time' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );
        
        if (!$user) {
            return back()->withErrors(['loginemail' => 'This email is invalid.'])->onlyInput('loginemail');
        } else {
            return back()->withErrors(['loginpassword' => 'Incorrect password.'])->onlyInput('loginpassword');
        }
    }
}

    public function forgotPassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $incomingFields = $request->validate([
                'email' => ['required', 'email'],
            ], [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
            ]);

            $email = $request->input('email');
            $request->session()->put('email', $email);

            $user = User::where('email', $email)->first();
            if ($user) {
                $token = Str::random(60);
                $user->password_reset_token = $token;
                $user->save();
                $user->notify(new ResetPasswordMail($user, $token));
                return view('reset_password_email_sent'); // Return the new view
            } else {
                return redirect()->back()->withInput()->withErrors(['email' => 'Sorry, we couldn\'t find an account with that email address. Please try again!']);
            }
        }
        return view('forgotPassword');
    }

    public function resendEmail(Request $request)
    {
        $email = $request->session()->get('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'This email is not registered.']);
        }

        $token = $user->password_reset_token;

        // Resend the password reset email
        $user->notify(new ResetPasswordMail($user, $token));

    // Set a session variable to track the last resend time (persistent for countdown init)
    $request->session()->put('last_resend_time', time());

    // Flash a transient success message under a specific key so it won't appear as a generic success elsewhere
    $request->session()->flash('resend_success', 'Email resent successfully!');

    // Redirect (Post-Redirect-Get) back to the email sent page to avoid duplicate form submit and persistent session keys
    return redirect()->route('password-reset-email-sent');
    }

    /**
     * Send a password reset email for the currently authenticated user.
     */
    public function sendAccountResetEmail(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect('/login');
        }

        // generate token and persist
        $token = Str::random(60);
        $user->password_reset_token = $token;
        $user->save();

        try {
            $user->notify(new ResetPasswordMail($user, $token));
        } catch (\Exception $e) {
            \Log::error('Failed to send reset password email for user '.$user->email.': '.$e->getMessage());
            return redirect()->back()->with('status_error', 'Failed to send reset email. Please contact the administrator.');
        }

        // store email in session for potential resend
        $request->session()->put('email', $user->email);

        return redirect()->route('password-reset-email-sent');
    }

    public function resetPassword(Request $request, $token)
    {
        \Log::info('Password reset attempt', ['token' => $token]);
        
        // Get the user by token
        $user = User::where('password_reset_token', $token)->first();
        
        // Debug log
        if ($user) {
            \Log::info('User found for password reset', ['email' => $user->email, 'token' => $token]);
        } else {
            \Log::warning('No user found for password reset token', ['token' => $token]);
            
            // Check if any users have tokens for debugging
            $usersWithTokens = User::whereNotNull('password_reset_token')->get(['id', 'email', 'password_reset_token']);
            \Log::info('Users with tokens', ['users' => $usersWithTokens]);
        }

        // If the user exists, show the password reset form
        if ($user) {
            return view('reset_password', ['user' => $user, 'token' => $token]);
        } else {
            // If the user doesn't exist, show an error message
            return redirect('/login')->with('error', 'Invalid or expired password reset link. Please contact support.');
        }
    }

    public function updatePassword(Request $request, $token)
    {
        $incomingFields = $request->validate([
            'password' => [
                'required',
                'min:8',
                'max:20',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', // At least 1 uppercase, 1 lowercase, 1 number, 1 special char
            ],
            'password_confirmation' => [
                'required',
                'same:password', // Must match the password field
            ],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Password must be 8-20 characters and strong.',
            'password.max' => 'Password must be 8-20 characters and strong.',
            'password.regex' => 'Password must have uppercase, lowercase, number, and special character.',
            'password_confirmation.required' => 'Please confirm your new password.',
            'password_confirmation.same' => 'Passwords do not match.',
        ]);

        $user = User::where('password_reset_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid password reset token');
        }

        $user->password = bcrypt($incomingFields['password']);
        $user->password_reset_token = null;
        $user->save();

        return redirect()->route('password-reset-success')->with('success', 'Password updated successfully!');
    }

    public function passwordResetSuccess()
    {
        return view('password_reset_success');
    }

    public function logout(){
        // Clear the session and logout
        auth()->logout();
        
        // Invalidate the session
        request()->session()->invalidate();
        
        // Regenerate the CSRF token
        request()->session()->regenerateToken();
        
        // Redirect to logout success page
        return view('logout_success');
    }

}
