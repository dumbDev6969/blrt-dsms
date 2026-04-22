<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Computed]
    public function currentEnrollment()
    {
        return Auth::user()
            ->studentProfile
            ?->enrollments()
            ->where('status', 'active')
            ->first();
    }

    #[Computed]
    public function completedEnrollments()
    {
        $studentProfile = Auth::user()->studentProfile;

        if (!$studentProfile) {
            return collect();
        }

        return $studentProfile->enrollments()
            ->where('status', 'completed')
            ->with('course')
            ->get();
    }
};
?>

<div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
    <div class="mb-6">
        <flux:heading size="lg" class="font-bold text-slate-900 dark:text-slate-100">Your Roadmap to a Driver's License</flux:heading>
        <flux:text size="sm" class="text-slate-500 dark:text-slate-400">Track your progress from TDC to your official license.</flux:text>
    </div>

    {{-- Roadmap Container --}}
    <div class="relative">
        {{-- Connecting Line --}}
        <div
            class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 dark:bg-slate-800 -translate-y-1/2 rounded-full hidden md:block">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">

            @php
                $tdcCompleted = $this->completedEnrollments->firstWhere(fn ($e) => $e->course->type === 'theoretical');
                $isTdcActive = optional($this->currentEnrollment)->course?->type === 'theoretical';
                $tdcOpacity = ($tdcCompleted || $isTdcActive) ? 'opacity-100' : 'opacity-60';
            @endphp
            {{-- Step 1: TDC --}}
            <div class="relative group {{ $tdcOpacity }}">
                <div
                    class="flex flex-col items-center text-center p-4 bg-white dark:bg-slate-900 rounded-xl border-2 {{ $isTdcActive ? 'border-[var(--color-accent)]' : 'border-slate-100 dark:border-slate-800' }} shadow-sm z-10 relative">
                    <div
                        class="flex items-center justify-center size-10 rounded-full {{ $tdcCompleted ? 'bg-green-50 text-green-600' : 'bg-blue-50 text-[var(--color-accent)]' }} mb-3">
                        <flux:icon icon="{{ $tdcCompleted ? 'check-circle' : 'book-open' }}" class="size-5" />
                    </div>
                    <flux:heading size="sm" class="font-bold text-slate-900 dark:text-slate-100">1. Theoretical (TDC)</flux:heading>
                    <flux:text size="xs" class="text-slate-500 mt-1">15-hr Seminar</flux:text>
                    
                    @if ($tdcCompleted)
                        <flux:badge color="{{ strtolower($tdcCompleted->final_result) === 'passed' ? 'green' : 'red' }}" size="sm" class="mt-2">
                            {{ $tdcCompleted->final_result }} - {{ (int)$tdcCompleted->final_grade }}%
                        </flux:badge>
                    @elseif ($isTdcActive)
                        <flux:badge color="blue" size="sm" class="mt-2">
                            You are here
                        </flux:badge>
                    @endif
                </div>
            </div>

            @php
                $pdcCompleted = $this->completedEnrollments->firstWhere(fn ($e) => $e->course->type === 'practical');
                $isPdcActive = optional($this->currentEnrollment)->course?->type === 'practical';
                $pdcOpacity = ($pdcCompleted || $isPdcActive) ? 'opacity-100' : 'opacity-60';
            @endphp
            {{-- Step 2: PDC --}}
            <div class="relative group {{ $pdcOpacity }}">
                <div
                    class="flex flex-col items-center text-center p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 {{ $isPdcActive ? 'border-2 border-[var(--color-accent)]' : '' }} z-10 relative">
                    <div
                        class="flex items-center justify-center size-10 rounded-full {{ $pdcCompleted ? 'bg-green-50 text-green-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} mb-3">
                        <flux:icon icon="{{ $pdcCompleted ? 'check-circle' : 'truck' }}" class="size-5" />
                    </div>
                    <flux:heading size="sm" class="font-semibold text-slate-700 dark:text-slate-300">2. Practical (PDC)</flux:heading>
                    <flux:text size="xs" class="text-slate-500 mt-1">8-hr Driving</flux:text>

                    @if ($pdcCompleted)
                        <flux:badge color="{{ strtolower($pdcCompleted->final_result) === 'passed' ? 'green' : 'red' }}" size="sm" class="mt-2">
                            {{ $pdcCompleted->final_result }} - {{ (int)$pdcCompleted->final_grade }}%
                        </flux:badge>
                    @elseif ($isPdcActive)
                        <flux:badge color="blue" size="sm" class="mt-2">
                            You are here
                        </flux:badge>
                    @endif
                </div>
            </div>

            @php
                $isDone = $pdcCompleted && strtolower($pdcCompleted->final_result) === 'passed';
                $doneOpacity = $isDone ? 'opacity-100' : 'opacity-60';
            @endphp
            {{-- Step 3: Done --}}
            <div class="relative group {{ $doneOpacity }}">
                <div
                    class="flex flex-col items-center text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 z-10 relative">
                    <div
                        class="flex items-center justify-center size-10 rounded-full {{ $isDone ? 'bg-green-50 text-green-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} mb-3">
                        <flux:icon icon="{{ $isDone ? 'check-badge' : 'star' }}" class="size-5" />
                    </div>
                    <flux:heading size="sm" class="font-semibold text-slate-700 dark:text-slate-300">3. {{ $isDone ? 'Ready for License' : 'Final License' }}</flux:heading>
                    <flux:text size="xs" class="text-slate-500 mt-1">{{ $isDone ? 'Process at LTO' : 'Complete all courses' }}</flux:text>
                    
                    @if ($isDone)
                        <flux:badge color="green" size="sm" class="mt-2 animate-pulse">
                            Ready!
                        </flux:badge>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>