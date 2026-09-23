<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view("auth.register");
    }

    public function register(Request $request)
    {
        $request->validate([
            "first_name" => "required|string|max:255",
            "last_name" => "required|string|max:255",
            "email" => "required|email|max:255|unique:users",
            "phone" => "nullable|string|max:20",
            "password" => ["required", "confirmed", Password::min(8)],
            "role" => "required|in:student,teacher",
        ]);

        $user = User::create([
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "name" => $request->first_name . " " . $request->last_name,
            "email" => $request->email,
            "phone" => $request->phone,
            "role" => $request->role,
            "is_active" => true,
            "email_verified_at" => now(),
            "password" => Hash::make($request->password),
        ]);

        if ($request->role === "student") {
            $user->assignRole("student");
        } elseif ($request->role === "teacher") {
            $user->assignRole("teacher");
        }

        Auth::login($user);

        if ($user->hasRole("student")) {
            return redirect()->route("student.dashboard");
        }

        return redirect()->route("filament.admin.pages.dashboard");
    }
}
