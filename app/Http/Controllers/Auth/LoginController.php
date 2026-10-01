<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view("auth.login");
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        if (Auth::attempt($request->only("email", "password"), $request->filled("remember"))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                throw ValidationException::withMessages([
                    "email" => ["Your account has been deactivated."],
                ]);
            }

            $panelId = $user->isStudent() ? "student" : "admin";
            $fallback = route("filament.{$panelId}.pages.dashboard");

            $intended = redirect()->getIntendedUrl();

            if ($intended && $this->canAccessUrl($user, $panelId, $intended)) {
                return redirect()->to($intended);
            }

            return redirect()->to($fallback);
        }

        throw ValidationException::withMessages([
            "email" => [trans("auth.failed")],
        ]);
    }

    /**
     * A stored intended URL is only usable when it lives on the panel the
     * signed-in user is actually allowed to reach.
     */
    protected function canAccessUrl($user, string $panelId, string $url): bool
    {
        $panelPath = trim(parse_url($url, PHP_URL_PATH) ?? "", "/");

        return Str::startsWith($panelPath, $panelId . "/") || $panelPath === $panelId;
    }
}
