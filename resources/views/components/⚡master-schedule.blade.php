<?php

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\BookingSession;
use App\Models\InstructorProfile;
use App\Models\Vehicle;
use Livewire\Attributes\Computed;
use Illuminate\Support\Carbon;
use \Livewire\WithPagination;
new class extends Component
{
    use WithPagination;
    #[Computed]
    public function allTodaySessions()
    {
        return BookingSession::with(['enrollment.studentProfile.user', 'instructorProfile.user', 'vehicle'])
            ->whereDate('start_time', Carbon::today())
            ->orderBy('start_time', 'asc')
            ->get();
    }



    #[Computed]
    public function conflicts()
    {
        $sessions = $this->allTodaySessions();
        $conflicts = [];

        // Check for overlaps
        foreach ($sessions as $i => $s1) {
            foreach ($sessions as $j => $s2) {
                if ($i >= $j) continue; // avoid checking same pair twice or itself
                
                // Overlap condition: start1 < end2 && end1 > start2
                if ($s1->start_time < $s2->end_time && $s1->end_time > $s2->start_time) {
                    // Check if same instructor
                    if ($s1->instructor_id && $s1->instructor_id === $s2->instructor_id) {
                        $s1->has_conflict = true;
                        $s2->has_conflict = true;
                        $conflicts[] = [
                            'type' => 'instructor',
                            'name' => $s1->instructorProfile->user->name ?? 'Unknown',
                            'sessions' => [$s1, $s2]
                        ];
                    }
                    // Check if same vehicle
                    if ($s1->vehicle_id && $s1->vehicle_id === $s2->vehicle_id) {
                        $s1->has_conflict = true;
                        $s2->has_conflict = true;
                        $conflicts[] = [
                            'type' => 'vehicle',
                            'name' => $s1->vehicle->model . ' (' . $s1->vehicle->plate_number . ')',
                            'sessions' => [$s1, $s2]
                        ];
                    }
                }
            }
        }
        
        return collect($conflicts);
    }

    #[Computed]
    public function practicalSessions()
    {
        $paginator = BookingSession::with(['enrollment.studentProfile.user', 'instructorProfile.user', 'vehicle'])
            ->whereDate('start_time', Carbon::today())
            ->where('type', 'driving')
            ->orderBy('start_time', 'asc')
            ->paginate(5, ['*'], 'practicalPage');

        $conflictIds = $this->conflicts()->pluck('sessions')->flatten()->pluck('id')->toArray();
        
        $paginator->getCollection()->transform(function ($session) use ($conflictIds) {
            if (in_array($session->id, $conflictIds)) {
                $session->has_conflict = true;
            }
            return $session;
        });
        
        return $paginator;
    }

    #[Computed]
    public function theoreticalSessions()
    {
        $paginator = BookingSession::with(['enrollment.studentProfile.user', 'instructorProfile.user', 'vehicle'])
            ->whereDate('start_time', Carbon::today())
            ->where('type', 'lecture')
            ->orderBy('start_time', 'asc')
            ->paginate(5, ['*'], 'theoreticalPage');

        $conflictIds = $this->conflicts()->pluck('sessions')->flatten()->pluck('id')->toArray();
        
        $paginator->getCollection()->transform(function ($session) use ($conflictIds) {
            if (in_array($session->id, $conflictIds)) {
                $session->has_conflict = true;
            }
            return $session;
        });
        
        return $paginator;
    }
    
    #[Computed]
    public function activeInstructors()
    {
        return InstructorProfile::with('user')->where('is_active', true)->get();
    }
    
    #[Computed]
    public function availableVehicles()
    {
        // Assuming status 'active' or 'good_condition' for available
        return Vehicle::where('status', 'available')->get();
    }

    #[Computed]
    public function stats()
    {
        return [
            'total_sessions' => $this->allTodaySessions()->count(),
            'active_instructors' => InstructorProfile::where('is_active', true)->count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'conflicts_count' => $this->conflicts()->count(),
        ];
    }
};
?>

<div class="space-y-6" wire:poll.visible>
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg" weight="bold">Master Schedule Insight</flux:heading>
            <flux:text size="sm">Comprehensive view of all active trainings, instructors, and vehicles</flux:text>
        </div>
        <flux:button variant="ghost" icon="calendar" size="sm">Full Calendar</flux:button>
    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-cards
            label="Total Sessions Today"
            :value="$this->stats['total_sessions']"
            icon="calendar-days"
            color="emerald"
        />
        <x-kpi-cards
            label="Active Instructors"
            :value="$this->stats['active_instructors']"
            icon="users"
            color="blue"
        />
        <x-kpi-cards
            label="Available Vehicles"
            :value="$this->stats['available_vehicles']"
            icon="truck"
            color="amber"
        />
        <x-kpi-cards
            label="Scheduling Conflicts"
            :value="$this->stats['conflicts_count']"
            icon="exclamation-triangle"
            color="{{ $this->stats['conflicts_count'] > 0 ? 'red' : 'zinc' }}"
        />
    </div>
    
    {{-- Conflicts Alert --}}
    @if ($this->stats['conflicts_count'] > 0)
        <flux:callout variant="danger" icon="exclamation-triangle">
            <flux:callout.heading>Scheduling Conflicts Detected</flux:callout.heading>
            <div class="mt-2 space-y-2">
                @foreach ($this->conflicts() as $conflict)
                    <div class="flex items-center gap-2">
                        <flux:badge color="red" size="sm" variant="subtle" class="uppercase">{{ $conflict['type'] }}</flux:badge>
                        <flux:text size="sm">
                            <flux:text weight="bold" as="span">{{ $conflict['name'] }}</flux:text> is double-booked at 
                            <flux:text weight="bold" as="span">{{ $conflict['sessions'][0]->start_time->format('h:i A') }} - {{ $conflict['sessions'][0]->end_time->format('h:i A') }}</flux:text>
                        </flux:text>
                    </div>
                @endforeach
            </div>
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Scheduled Classes --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Practical Lessons --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" weight="semibold">Practical Lessons (PDC)</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">Time</th>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">Student & Instructor</th>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">Vehicle</th>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($this->practicalSessions() as $session)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors {{ isset($session->has_conflict) && $session->has_conflict ? 'bg-red-50/30 dark:bg-red-900/10' : '' }}">
                                    <td class="px-4 py-4">
                                        <flux:text size="xs" weight="bold" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700 {{ isset($session->has_conflict) && $session->has_conflict ? 'border-red-200 dark:border-red-800 text-red-600 dark:text-red-400' : '' }}">
                                            {{ $session->start_time ? $session->start_time->format('h:i A') : 'N/A' }} - {{ $session->end_time ? $session->end_time->format('h:i A') : 'N/A' }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <flux:text weight="medium" size="sm">{{ $session->enrollment->studentProfile->user->name ?? 'N/A' }}</flux:text>
                                            <flux:text size="xs" class="text-slate-500">Ins: {{ $session->instructorProfile->user->name ?? 'Unassigned' }}</flux:text>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <flux:text size="sm">{{ $session->vehicle->model ?? 'N/A' }}</flux:text>
                                        <flux:text size="xs" class="text-slate-500 uppercase tracking-wider">{{ $session->vehicle->plate_number ?? 'N/A' }}</flux:text>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        @php
                                            $statusColor = isset($session->has_conflict) && $session->has_conflict ? 'red' : match($session->status) {
                                                'completed' => 'emerald',
                                                'scheduled' => 'blue',
                                                'cancelled' => 'red',
                                                default => 'zinc',
                                            };
                                        @endphp
                                        <flux:badge :color="$statusColor" size="sm" variant="subtle">{{ isset($session->has_conflict) && $session->has_conflict ? 'Conflict' : ucfirst($session->status) }}</flux:badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <flux:text size="sm" class="text-slate-500">No practical lessons scheduled today.</flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($this->practicalSessions->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                        {{ $this->practicalSessions->links() }}
                    </div>
                @endif
            </div>

            {{-- Theoretical Classes --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" weight="semibold">Theoretical Classes (TDC)</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">Time</th>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">Student & Instructor</th>
                                <th class="px-4 py-3 font-semibold text-zinc-900 dark:text-white text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($this->theoreticalSessions() as $session)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors {{ isset($session->has_conflict) && $session->has_conflict ? 'bg-red-50/30 dark:bg-red-900/10' : '' }}">
                                    <td class="px-4 py-4">
                                        <flux:text size="xs" weight="bold" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700 {{ isset($session->has_conflict) && $session->has_conflict ? 'border-red-200 dark:border-red-800 text-red-600 dark:text-red-400' : '' }}">
                                            {{ $session->start_time ? $session->start_time->format('h:i A') : 'N/A' }} - {{ $session->end_time ? $session->end_time->format('h:i A') : 'N/A' }}
                                        </flux:text>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <flux:text weight="medium" size="sm">{{ $session->enrollment->studentProfile->user->name ?? 'N/A' }}</flux:text>
                                            <flux:text size="xs" class="text-slate-500">Ins: {{ $session->instructorProfile->user->name ?? 'Unassigned' }}</flux:text>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        @php
                                            $statusColor = isset($session->has_conflict) && $session->has_conflict ? 'red' : match($session->status) {
                                                'completed' => 'emerald',
                                                'scheduled' => 'blue',
                                                'cancelled' => 'red',
                                                default => 'zinc',
                                            };
                                        @endphp
                                        <flux:badge :color="$statusColor" size="sm" variant="subtle">{{ isset($session->has_conflict) && $session->has_conflict ? 'Conflict' : ucfirst($session->status) }}</flux:badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <flux:text size="sm" class="text-slate-500">No theoretical classes scheduled today.</flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($this->theoreticalSessions->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                        {{ $this->theoreticalSessions->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- Right Column: Availability & Resources --}}
        <div class="space-y-6">
            
            {{-- Instructor Availability --}}
            <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" weight="bold">Active Instructors</flux:heading>
                    <flux:badge size="sm" color="blue">{{ $this->activeInstructors()->count() }}</flux:badge>
                </div>
                <div class="space-y-3">
                    @forelse ($this->activeInstructors() as $instructor)
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-bold">
                                {{ collect(explode(' ', $instructor->user->name ?? 'N A'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                            </div>
                            <div class="flex-1">
                                <flux:text size="sm" weight="medium">{{ $instructor->user->name ?? 'Unknown' }}</flux:text>
                                <flux:text size="xs" class="text-slate-500">Valid License</flux:text>
                            </div>
                        </div>
                    @empty
                        <flux:text size="sm" class="text-slate-500">No active instructors available.</flux:text>
                    @endforelse
                </div>
            </div>

            {{-- Vehicle Availability --}}
            <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" weight="bold">Available Vehicles</flux:heading>
                    <flux:badge size="sm" color="amber">{{ $this->availableVehicles()->count() }}</flux:badge>
                </div>
                <div class="space-y-3">
                    @forelse ($this->availableVehicles() as $vehicle)
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-zinc-100 text-zinc-500 rounded-lg dark:bg-zinc-800 dark:text-zinc-400">
                                <flux:icon icon="truck" class="size-5" />
                            </div>
                            <div class="flex-1">
                                <flux:text size="sm" weight="medium">{{ $vehicle->model }}</flux:text>
                                <flux:text size="xs" class="text-slate-500 uppercase">{{ $vehicle->plate_number }}</flux:text>
                            </div>
                            <flux:badge size="sm" color="emerald" variant="subtle">Optimal</flux:badge>
                        </div>
                    @empty
                        <flux:text size="sm" class="text-slate-500">No vehicles currently available.</flux:text>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>