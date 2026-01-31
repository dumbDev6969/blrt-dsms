<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentProfile;
use App\Models\InstructorProfile;
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

        // 1. Define routes that should always be accessible
        // "livewire.message" is often the route name for updates, checking both ensures compatibility
        $allowedRoutes = [
            'logout',
            'livewire.update', 
            'livewire.message',
            'livewire.upload-file', 
        ];

        
        if ($user->hasRole('Student')) {
            
            // Add the student onboarding route to allowed list
            $allowedRoutes[] = 'student_profile.create';

            // Check if profile exists
            $profileExists = StudentProfile::where('user_id', $user->id)->exists();

            if (! $profileExists && ! $request->routeIs($allowedRoutes)) {
                return redirect()->route('student_profile.create');
            }

        } elseif ($user->hasRole('Instructor')) {

            // Add the instructor onboarding route to allowed list
            $allowedRoutes[] = 'instructor_profile.create';

            // Check if profile exists
            $profileExists = InstructorProfile::where('user_id', $user->id)->exists();

            if (! $profileExists && ! $request->routeIs($allowedRoutes)) {
                return redirect()->route('instructor_profile.create');
            }
        }

        return $next($request);
    }
}
