<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $freshUser = User::query()
                ->select(['id', 'username', 'full_name', 'role', 'phone', 'name', 'email'])
                ->find(Auth::id());

            if (! $freshUser) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Sesi akun tidak lagi valid.'], 401);
                }

                return redirect('/')->with('error', 'Akun Anda sudah dihapus atau dinonaktifkan.');
            }

            // Refresh in-memory authenticated user attributes so role/name updates take effect immediately.
            Auth::setUser($freshUser);
        }

        return $next($request);
    }
}
