<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendResetPasswordEmailJob;
use App\Jobs\SendVerificationEmailJob;
use App\Models\Addresses;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:10',
            'password' => 'required|string|min:8',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'phone' => $request->phone,
        ]);
        $user->addresses()->create([
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'zip_code' => $request->zip_code,
        ]);
        SendVerificationEmailJob::dispatch($user);

        return redirect()->route('login')->with('success', 'Account created! Please check your email to verify.');
    }


    public function verifyEmail($id)
    {
        $user = User::findOrFail($id);
        $user->markEmailAsVerified();
        return redirect('/login')->with('success', 'Email verified successfully!');
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $message = 'User not found with this email address';

            if ($request->is('api/*')) {
                return response()->json(['message' => $message], 404);
            }

            return back()->withErrors(['email' => $message])->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            $message = 'Incorrect password';

            if ($request->is('api/*')) {
                return response()->json(['message' => $message], 401);
            }

            return back()->withErrors(['password' => $message])->withInput();
        }
        if (!$user->hasVerifiedEmail()) {
            $message = 'Please verify your email before logging in.';

            if ($request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            return back()->withErrors(['email' => $message])->withInput();
        }


        Auth::login($user);

        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        }

        $successMessage = 'You are logged in successfully!';

        session(['api_token' => $token]);

        if ($user->role === 'admin') {
            return redirect()->route('admin')->with('success', $successMessage);
        } elseif ($user->role === 'customer') {
            return redirect()->route('customer')->with('success', $successMessage);
        } elseif ($user->role === 'staff') {
            return redirect()->route('staff')->with('success', $successMessage);
        } elseif ($user->role === 'agent') {
            return redirect()->route('agent')->with('success', $successMessage);
        }
    }



    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email not found'])->withInput();
        }

        $token = Str::random(32);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        SendResetPasswordEmailJob::dispatch($user, $token);

        return back()->with('success', 'Password reset link sent! Please check your email.');
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:6',
            'password_confirmation' => 'required',
            'token' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email not found'])->withInput();
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => 'Invalid token or email'])->withInput();
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password reset successfully!');
    }


    public function logout()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully!');

    }
}
