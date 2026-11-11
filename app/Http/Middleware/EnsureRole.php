<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
            }

            return redirect('/')->with('open_login', true);
        }

        $freshUser = User::find(Auth::id());
        if (! $freshUser) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'Akun tidak ditemukan.'], 401)
                : redirect('/');
        }

        Auth::setUser($freshUser);

        if (! empty($roles) && ! in_array($freshUser->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman atau fitur ini.');
        }

        return $next($request);
    }
}
