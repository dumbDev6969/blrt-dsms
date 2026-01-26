<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentProfile;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $isComplete = StudentProfile::where('user_id', $user->id)->exists();

        $allowedRoutes = [
            'student_profile.create',
            'livewire.update',
            'logout',
        ];
        // Redirect if the profile is incomplete
        if (!$isComplete && !$request->routeIs($allowedRoutes)) {
            return redirect()->route('student_profile.create');
        }




        return $next($request);
    }
}
