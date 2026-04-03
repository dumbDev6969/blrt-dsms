<?php

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\BookingSession;
use App\Services\AssessmentAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function enrollment()
    {
        return Auth::user()->studentProfile->enrollments()
            ->with(['course', 'instructorProfile.user'])
            ->where('status', 'active')
            ->first();
    }

    #[Computed]
    public function sessions()
    {
        if (!$this->enrollment) {
            return collect();
        }
        
        return $this->enrollment->bookingSessions()
            ->with(['instructorProfile.user', 'vehicle'])
            ->orderBy('start_time', 'asc')
            ->get();
    }

    #[Computed]
    public function assessmentAnalytics()
    {
        if (!$this->enrollment) {
            return null;
        }

        $assessment = $this->enrollment->assessments()
            ->where('assessment_type', 'practical')
            ->latest()
            ->first();

        if (!$assessment) {
            return null;
        }

        return app(AssessmentAnalyticsService::class)->generate($assessment);
    }
};
?>

<div>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" weight="bold" class="tracking-tight">My Schedule</flux:heading>
            <flux:text size="sm" class="text-slate-500 mt-1">Track your upcoming theoretical and practical training sessions.</flux:text>
        </div>
        @if ($this->enrollment)
            <flux:button href="{{ route('dashboard') }}" variant="subtle" icon="arrow-left" size="sm" wire:navigate>Back to Dashboard</flux:button>
        @endif
    </div>

    @if ($this->enrollment)
        {{-- Enrollment Overview Card --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="lg:col-span-2">
                <div class="p-6 md:p-8 rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative overflow-hidden group h-full flex flex-col transition-all hover:shadow-md">
                    {{-- Decorative Background Glow --}}
                    <div class="absolute -right-20 -top-20 size-64 bg-blue-500/10 dark:bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-colors duration-500"></div>
                    
                    <div class="relative z-10 flex-1 flex flex-col">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                                        <flux:icon icon="academic-cap" class="size-5" />
                                    </div>
                                    <flux:heading size="xl" class="font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $this->enrollment->course->title }}</flux:heading>
                                </div>
                                <flux:text size="sm" class="text-slate-500 flex items-center gap-2">
                                    Enrollment Code: 
                                    <span class="font-mono text-blue-700 dark:text-blue-400 font-bold px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 rounded border border-blue-100 dark:border-blue-800/50">{{ $this->enrollment->code }}</span>
                                </flux:text>
                            </div>
                            <flux:badge size="md" color="emerald" variant="subtle" class="capitalize font-semibold shrink-0">{{ $this->enrollment->status }}</flux:badge>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 bg-slate-50 dark:bg-slate-800/40 p-5 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <div class="flex flex-col">
                                <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold mb-1">Start Date</flux:text>
                                <flux:text size="sm" weight="semibold" class="text-slate-800 dark:text-slate-200">{{ $this->enrollment->start_date?->format('M d, Y') ?? 'N/A' }}</flux:text>
                            </div>
                            <div class="flex flex-col">
                                <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold mb-1">Target End</flux:text>
                                <flux:text size="sm" weight="semibold" class="text-amber-600 dark:text-amber-400">{{ $this->enrollment->target_completion_date?->format('M d, Y') ?? 'TBA' }}</flux:text>
                            </div>
                            <div class="flex flex-col">
                                <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold mb-1">TDC Progr</flux:text>
                                <div class="flex items-end gap-1.5">
                                    <flux:text size="sm" weight="semibold" class="text-slate-800 dark:text-slate-200 leading-none">{{ $this->enrollment->tdc_hours_completed }}</flux:text>
                                    <flux:text size="xs" class="text-slate-400 leading-none mb-0.5">/ {{ $this->enrollment->tdc_hours_required }} h</flux:text>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold mb-1">Payment</flux:text>
                                <div class="flex items-end gap-1.5">
                                    <flux:text size="sm" weight="semibold" class="text-slate-800 dark:text-slate-200 leading-none">{{ $this->enrollment->amount_paid }}</flux:text>
                                    <flux:text size="xs" class="text-slate-400 leading-none mb-0.5">/ {{ $this->enrollment->total_amount }}</flux:text>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                            <div class="flex items-center gap-4 w-full sm:w-auto flex-1">
                                <div class="flex flex-col shrink-0">
                                    <flux:text size="xs" class="text-slate-400 font-bold tracking-wider">OVERALL PROGRESS</flux:text>
                                    <flux:heading size="md" class="font-black text-blue-600 dark:text-blue-400">{{ number_format($this->enrollment->progress_percent, 0) }}%</flux:heading>
                                </div>
                                <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden self-end mb-1">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all duration-1000" style="width: {{ $this->enrollment->progress_percent }}%"></div>
                                </div>
                            </div>
                            
                            @if($this->enrollment->instructorProfile)
                                <div class="flex items-center gap-3 bg-white dark:bg-slate-900 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm shrink-0">
                                    <div class="size-9 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-xs font-black text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                        {{ substr($this->enrollment->instructorProfile->user->name, 0, 2) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <flux:text size="xs" class="text-slate-400 font-bold uppercase tracking-tighter">Assigned Instructor</flux:text>
                                        <flux:text size="sm" weight="bold" class="text-slate-900 dark:text-slate-100">{{ $this->enrollment->instructorProfile->user->name }}</flux:text>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="p-6 md:p-8 rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm h-full flex flex-col">
                    <flux:heading size="lg" weight="bold" class="mb-6 flex items-center gap-2 tracking-tight">
                         <div class="p-1.5 bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-md">
                             <flux:icon icon="check-badge" class="size-4" />
                         </div>
                         Module Status
                    </flux:heading>
                    
                    <div class="space-y-4 flex-1 flex flex-col justify-center">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 transition-colors hover:border-blue-200 dark:hover:border-blue-800">
                            <div class="flex items-center gap-3">
                                <flux:icon icon="book-open" class="size-5 text-indigo-400" />
                                <flux:text size="sm" weight="semibold">TDC Module</flux:text>
                            </div>
                            <flux:badge size="sm" :color="$this->enrollment->tdc_status === 'completed' ? 'emerald' : ($this->enrollment->tdc_status === 'in_progress' ? 'blue' : 'slate')" variant="subtle" class="capitalize font-medium">
                                {{ str_replace('_', ' ', $this->enrollment->tdc_status) }}
                            </flux:badge>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 transition-colors hover:border-purple-200 dark:hover:border-purple-800">
                            <div class="flex items-center gap-3">
                                <flux:icon icon="truck" class="size-5 text-purple-400" />
                                <flux:text size="sm" weight="semibold">PDC Module</flux:text>
                            </div>
                            <flux:badge size="sm" :color="$this->enrollment->pdc_status === 'completed' ? 'emerald' : ($this->enrollment->pdc_status === 'in_progress' ? 'blue' : 'slate')" variant="subtle" class="capitalize font-medium">
                                {{ str_replace('_', ' ', $this->enrollment->pdc_status) }}
                            </flux:badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>

 
        
        <x-assessment-analytics :analytics="$this->assessmentAnalytics" />


        {{-- Sessions Timeline Section --}}
        <div>
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6 border-b border-slate-200 dark:border-slate-800 pb-4">
                <div>
                    <flux:heading size="xl" weight="bold" class="tracking-tight">Training Itinerary</flux:heading>
                    <flux:text size="sm" class="text-slate-500 mt-1">Your detailed session plan chronologically ordered.</flux:text>
                </div>
                <flux:badge size="sm" color="zinc" class="w-max"><span class="font-bold">{{ $this->sessions->count() }}</span> &nbsp;Sessions Total</flux:badge>
            </div>
            
            @if ($this->sessions->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($this->sessions as $session)
                        @php
                            $isToday = $session->start_time->isToday();
                            $isPast = $session->start_time->isPast() && !$isToday;
                            
                            $borderClass = $isToday ? 'border-blue-500 ring-1 ring-blue-500 dark:ring-blue-400 shadow-md transform scale-[1.01]' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700';
                            $bgClass = 'bg-white dark:bg-slate-900';
                            $opacityClass = $isPast ? 'opacity-70 hover:opacity-100 grayscale-[0.3]' : '';
                            
                            $dateBgClass = $isToday ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-inner' : 'bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border border-slate-100 dark:border-slate-700';
                            $monthTextClass = $isToday ? 'text-blue-100' : 'text-slate-500 dark:text-slate-400';
                            $dayTextClass = $isToday ? 'text-white' : 'text-slate-800 dark:text-slate-100';
                            
                            $badgeColor = match($session->status) {
                                'completed' => 'emerald',
                                'in_progress' => 'blue',
                                'scheduled' => 'amber',
                                'cancelled' => 'red',
                                default => 'slate'
                            };
                            
                            $iconColor = $session->type === 'TDC' ? 'text-indigo-500' : 'text-purple-500';
                            $iconName = $session->type === 'TDC' ? 'book-open' : 'truck';
                        @endphp
                        
                        <div class="flex flex-col sm:flex-row gap-5 p-4 sm:p-5 rounded-2xl border transition-all duration-300 group relative overflow-hidden {{ $borderClass }} {{ $bgClass }} {{ $opacityClass }}">
                            @if($isToday)
                                <div class="absolute top-0 right-0 bg-blue-500/10 text-blue-700 dark:text-blue-300 text-[10px] font-black tracking-widest px-4 py-1.5 uppercase rounded-bl-xl z-10 border-b border-l border-blue-500/20 ">Today's Session</div>
                            @endif
                            
                            {{-- Date Block --}}
                            <div class="-z-10 shrink-0 flex sm:flex-col items-center justify-center gap-3 sm:gap-0 sm:min-w-[5.5rem] p-3 sm:py-5 rounded-xl transition-colors {{ $dateBgClass }}">
                                <flux:text size="xs" class="{{ $monthTextClass }} font-bold uppercase tracking-widest">{{ $session->start_time->format('M') }}</flux:text>
                                <flux:heading size="3xl" class="{{ $dayTextClass }} font-black leading-none sm:mt-1">{{ $session->start_time->format('d') }}</flux:heading>
                                <flux:text size="xs" class="{{ $monthTextClass }} font-medium mt-1 sm:mt-1.5 uppercase tracking-wide hidden sm:block">{{ $session->start_time->format('D') }}</flux:text>
                            </div>
                            
                            {{-- Details Block --}}
                            <div class="flex-1 flex flex-col justify-center space-y-3 pt-1">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <flux:icon :icon="$iconName" class="size-4 {{ $iconColor }}" />
                                            <flux:text size="sm" weight="bold" class="text-slate-900 dark:text-slate-100 uppercase tracking-wide">{{ $session->type }} <span class="text-slate-400">SESSION</span></flux:text>
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 font-medium font-mono text-sm pl-6">
                                            <flux:icon icon="clock" class="size-3.5" />
                                            <span>{{ $session->start_time->format('h:i A') }} <span class="text-slate-300 mx-1">—</span> {{ $session->end_time?->format('h:i A') ?? 'TBA' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <flux:badge size="sm" :color="$badgeColor" variant="subtle" class="capitalize shrink-0 px-2.5">
                                            {{ str_replace('_', ' ', $session->status) }}
                                        </flux:badge>
                                    </div>
                                </div>
                                
                                <div class="pt-4 mt-auto border-t border-slate-100 dark:border-slate-800/60 flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
                                    @if ($session->instructorProfile)
                                        <div class="flex items-center gap-2.5">
                                            <div class="size-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                {{ substr($session->instructorProfile->user?->name ?? '?', 0, 2) }}
                                            </div>
                                            <flux:text size="xs" weight="medium" class="text-slate-600 dark:text-slate-300">{{ $session->instructorProfile->user?->name ?? 'Pending Assignment' }}</flux:text>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 text-slate-400">
                                            <flux:icon icon="user-circle" class="size-4" />
                                            <flux:text size="xs" class="italic">Instructor pending</flux:text>
                                        </div>
                                    @endif
                                    
                                    @if($session->vehicle)
                                         <div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                                             <flux:icon icon="key" class="size-3 text-slate-400" />
                                             <flux:text size="xs" class="text-slate-500 font-bold tracking-wider font-mono">{{ $session->vehicle->plate_number ?? 'Auto' }}</flux:text>
                                         </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-20 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl shadow-sm mb-6 border border-slate-200 dark:border-slate-800">
                        <flux:icon icon="calendar-days" class="size-12 text-blue-400 dark:text-blue-500" />
                    </div>
                    <flux:heading size="xl" weight="bold">Your schedule is clear</flux:heading>
                    <flux:text size="sm" class="mt-3 text-slate-500 max-w-sm mx-auto">
                        There are no sessions scheduled at the moment. As you progress, your instructor will assign TDC and PDC schedules which will appear right here.
                    </flux:text>
                </div>
            @endif
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-[70vh] text-center px-4">
            <div class="size-24 rounded-[2rem] bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 flex items-center justify-center mb-8 border border-blue-100 dark:border-blue-800/50 shadow-sm">
                <flux:icon icon="academic-cap" class="size-12 text-blue-600 dark:text-blue-400" />
            </div>
            <flux:heading size="2xl" class="font-bold tracking-tight mb-2">No Active Enrollment Found</flux:heading>
            <flux:text class="text-slate-500 max-w-md mx-auto leading-relaxed">
                It looks like you don't have an active course enrollment right now. Head over to the dashboard to view available courses or check your application status.
            </flux:text>
            <div class="mt-10">
                <flux:button href="{{ route('dashboard') }}" variant="primary" class="rounded-full px-8 shadow-md shadow-blue-500/20">Return to Dashboard</flux:button>
            </div>
        </div>
    @endif
</div>