<?php

namespace App\Services;
use App\Models\InstructorProfile;
use App\Models\BookingSession;
class InstructroAvailabilityService
{
    public function getAvailability($instructor_id)
    {
        $instructor = InstructorProfile::find($instructor_id);
        if (!$instructor) {
            return collect();
        }

        $weeklySchedule = $instructor->weekly_schedule;
        
        // get the booking session of the instructor
        $bookings = BookingSession::where('instructor_id', $instructor->id)
            ->select('start_time', 'end_time')
            ->get();

        $availableDates = collect();
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(30)->endOfDay();
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        // Loop through the next 30 days
        foreach ($period as $date) {
            $dayKeyLong = $date->format('l'); // Monday, Tuesday, ...
            $dayKeyShort = strtolower($date->format('D')); // mon, tue, ...

            $isAvailable = false;

            // Check new format (mon, tue, ... with active key)
            if (isset($weeklySchedule[$dayKeyShort])) {
                $dayData = $weeklySchedule[$dayKeyShort];
                if (is_array($dayData) && isset($dayData['active']) && $dayData['active']) {
                    $isAvailable = true;
                }
            }
            
            // Check old/factory format (Monday, Tuesday, ... as array of slots)
            if (!$isAvailable && isset($weeklySchedule[$dayKeyLong])) {
                $dayData = $weeklySchedule[$dayKeyLong];
                if (is_array($dayData) && !empty($dayData)) {
                    $isAvailable = true;
                }
            }

            if ($isAvailable) {
                $availableDates->push($date->format('Y-m-d'));
            }
        }

        return $availableDates;
    }
}
