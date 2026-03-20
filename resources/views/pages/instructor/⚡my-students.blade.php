<?php

use Livewire\Component;
use App\Models\Enrollment;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use App\Models\BookingSession;
use App\Models\Course;
new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $selectedEnrollmentId = null;
    public $activeSessionExists = false;
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function showDetails($id)
    {
        $this->selectedEnrollmentId = $id;
        $this->dispatch('modal-opened', name: 'student-details');
    }

    #[Computed]
    public function selectedEnrollment()
    {
        if (!$this->selectedEnrollmentId) {
            return null;
        }
        return Enrollment::with(['studentProfile.user', 'course'])->find($this->selectedEnrollmentId);
    }

    #[Computed]
    public function enrollmentCounts()
    {
        $instructorId = Auth::user()->instructorProfile->id;
        return [
            'total' => Enrollment::where('instructor_id', $instructorId)->count(),
            'pending' => Enrollment::where('instructor_id', $instructorId)->where('status', 'pending')->count(),
            'active' => Enrollment::where('instructor_id', $instructorId)->where('status', 'active')->count(),
            'completed' => Enrollment::where('instructor_id', $instructorId)->where('status', 'completed')->count(),
            'dropped' => Enrollment::where('instructor_id', $instructorId)->where('status', 'dropped')->count(),
        ];
    }

    #[Computed]
    public function enrollments()
    {
        return Enrollment::query()
            ->with(['studentProfile.user', 'course'])
            ->where('instructor_id', Auth::user()->instructorProfile->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('studentProfile.user', function ($sub) {
                            $sub->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('course', function ($sub) {
                            $sub->where('title', 'like', '%' . $this->search . '%')->orWhere('code', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->latest()
            ->paginate(12); // Changed to 12 for better grid alignment (3x4)
    }

    public function beginTDC()
    {
        $instructorId = Auth::user()->instructorProfile->id;
        $now = now();

        // Get the TDC Course ID
        $courseId = Course::where('type', 'theoretical')->value('id');

        // Fetch active enrollments for this instructor/course
        $enrollments = Enrollment::where('instructor_id', $instructorId)->where('status', 'active')->where('course_id', $courseId)->get();

        \DB::transaction(function () use ($enrollments, $instructorId, $now) {
            foreach ($enrollments as $enrollment) {
                // Get the ssession that is not yet completed or cancelled
                $hasActiveSession = BookingSession::where('enrollment_id', $enrollment->id)
                    ->whereIn('status', ['scheduled']) 
                    ->whereNull('end_time')
                    ->exists();

                if (!$hasActiveSession) {
                    BookingSession::create([
                        'enrollment_id' => $enrollment->id,
                        'instructor_id' => $instructorId,
                        'start_time' => $now,
                        'type' => 'lecture',
                        'status' => 'scheduled',
                    ]);
                }
            }
        });

        session()->flash('success', 'TDC Session started.');
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- Callout Alert --}}
    @if (session('status'))
        <flux:callout icon="check-circle" variant="success"
            class="shadow-sm fixed top-5 w-[90%] md:w-5xl left-1/2 -translate-x-1/2 z-50" x-data="{ visible: true }"
            x-show="visible">
            <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">My Students</flux:heading>
            <flux:text>Manage your assigned students and track their course progress.</flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Total Assigned
                </flux:text>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="users" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['total'] }}</flux:heading>
                <flux:text>students</flux:text>
            </div>
        </div>

        {{-- Active --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Currently Active
                </flux:text>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="check-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['active'] }}</flux:heading>
                <flux:text>learning</flux:text>
            </div>
        </div>

        {{-- Pending --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Pending Start
                </flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['pending'] }}</flux:heading>
                <flux:text>waiting</flux:text>
            </div>
        </div>

        {{-- Completed --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Finished
                </flux:text>
                <div class="p-2 bg-zinc-50 text-zinc-600 rounded-lg dark:bg-zinc-900/20 dark:text-zinc-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['completed'] }}</flux:heading>
                <flux:text>graduated</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-4 sm:gap-5">
        {{-- Filter Tabs & Search --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between p-1 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg w-full gap-3 lg:gap-0">
            <div class="flex flex-wrap gap-1 p-1 w-full lg:w-auto">
                @foreach (['all', 'active', 'pending', 'completed', 'dropped'] as $tab)
                    <button wire:click="$set('status', '{{ $tab }}')"
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-md transition-colors capitalize {{ $status === $tab ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                        {{ $tab }} <span
                            class="hidden sm:inline">({{ $this->enrollmentCounts[$tab] ?? $this->enrollmentCounts['total'] }})</span>
                    </button>
                @endforeach
            </div>
            <div class="px-2 pb-2 lg:p-0 w-full lg:w-72">
                <flux:input placeholder="Search code or student..." icon="magnifying-glass"
                    wire:model.live.debounce.500ms="search" class="w-full" />
            </div>
        </div>

        {{-- Enrollment List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden flex flex-col">
            <div
                class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">My Students List</flux:heading>
                    <flux:text size="sm" class="mt-1">View and track progress of students assigned to you.
                    </flux:text>
                </div>
                <flux:button variant="primary" size="sm" icon="play" wire:click="beginTDC"
                    wire:loading.attr="disabled" wire:target="beginTDC" :disabled="$activeSessionExists">
                    Begin TDC sessions
                </flux:button>
            </div>


            {{-- Enrollment Cards Grid --}}
            <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 bg-slate-50/30 dark:bg-slate-900/50"
                wire:transition>
                @forelse ($this->enrollments as $enrollment)
                    <div
                        class="group flex flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-300 overflow-hidden">

                        {{-- Card Header: Student & Course --}}
                        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex justify-between items-start gap-2 mb-3 sm:mb-4">
                                <div class="flex gap-3 items-center min-w-0">
                                    <div
                                        class="h-9 w-9 sm:h-10 sm:w-10 rounded-full shrink-0 bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold border border-blue-200 dark:border-blue-800">
                                        {{ substr($enrollment->studentProfile->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <flux:text weight="bold"
                                            class="text-sm sm:text-base text-slate-900 dark:text-white leading-tight truncate">
                                            {{ $enrollment->studentProfile->user->name ?? 'N/A' }}
                                        </flux:text>
                                        <flux:text size="xs" class="text-slate-500 mt-0.5 truncate">
                                            {{ $enrollment->code }}
                                        </flux:text>
                                    </div>
                                </div>
                                @php
                                    $statusConfig = [
                                        'pending' => ['color' => 'amber', 'label' => 'Pending'],
                                        'active' => ['color' => 'emerald', 'label' => 'Active'],
                                        'completed' => ['color' => 'blue', 'label' => 'Completed'],
                                        'dropped' => ['color' => 'red', 'label' => 'Dropped'],
                                    ];
                                    $config = $statusConfig[$enrollment->status] ?? [
                                        'color' => 'zinc',
                                        'label' => $enrollment->status,
                                    ];
                                @endphp
                                <flux:badge :color="$config['color']" variant="subtle" size="xs"
                                    class="capitalize tracking-wide shrink-0">
                                    {{ $config['label'] }}
                                </flux:badge>
                            </div>

                            <div class="flex flex-col gap-1">
                                <flux:text size="sm" weight="semibold"
                                    class="text-slate-800 dark:text-slate-200 line-clamp-2 sm:line-clamp-1">
                                    {{ $enrollment->course->title ?? 'N/A' }}
                                </flux:text>
                            </div>
                        </div>

                        {{-- Card Body: Progress & Stats --}}
                        <div class="p-4 sm:p-5 flex-1 flex flex-col gap-4 sm:gap-5">

                            {{-- Overall Progress --}}
                            <div class="space-y-1.5 sm:space-y-2">
                                <div class="flex justify-between items-center text-xs sm:text-sm">
                                    <flux:text class="text-slate-600 dark:text-slate-400">Course Progress</flux:text>
                                    <flux:text weight="bold"
                                        class="{{ $enrollment->progress_percent == 100 ? 'text-emerald-600' : 'text-blue-600' }}">
                                        {{ $enrollment->progress_percent }}%
                                    </flux:text>
                                </div>
                                <div
                                    class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 sm:h-2 overflow-hidden">
                                    <div class="{{ $enrollment->progress_percent == 100 ? 'bg-emerald-500' : 'bg-blue-500' }} h-full rounded-full transition-all duration-500"
                                        style="width: {{ $enrollment->progress_percent }}%"></div>
                                </div>
                            </div>

                            {{-- Dynamic Modules --}}
                            <div class="grid grid-cols-1 gap-2 sm:gap-3">
                                {{-- TDC Module --}}
                                @if ($enrollment->tdc_hours_required > 0)
                                    <div
                                        class="flex items-center justify-between p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/50">
                                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                            <div
                                                class="p-1.5 sm:p-2 bg-amber-50 dark:bg-amber-900/10 text-amber-600 dark:text-amber-400 rounded-lg shrink-0">
                                                <flux:icon icon="book-open" class="size-3.5 sm:size-4" />
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <flux:text size="xs" weight="semibold"
                                                    class="text-slate-700 dark:text-slate-300 truncate">Theory (TDC)
                                                </flux:text>
                                                <flux:text size="xs" class="text-slate-500 truncate">
                                                    {{ $enrollment->tdc_hours_completed }}/{{ $enrollment->tdc_hours_required }}
                                                    hrs
                                                </flux:text>
                                            </div>
                                        </div>
                                        @php
                                            $moduleConfig = [
                                                'not_started' => ['color' => 'zinc', 'icon' => 'clock'],
                                                'in_progress' => ['color' => 'blue', 'icon' => 'arrow-path'],
                                                'completed' => ['color' => 'emerald', 'icon' => 'check-circle'],
                                            ];
                                            $tdcConf =
                                                $moduleConfig[$enrollment->tdc_status] ?? $moduleConfig['not_started'];
                                        @endphp
                                        <flux:badge :color="$tdcConf['color']" variant="subtle" size="xs"
                                            class="shrink-0 hidden sm:inline-flex">
                                            <flux:icon :icon="$tdcConf['icon']" class="size-3 mr-1" />
                                            {{ str_replace('_', ' ', $enrollment->tdc_status) }}
                                        </flux:badge>
                                    </div>
                                @endif

                                {{-- PDC Module --}}
                                @if ($enrollment->pdc_hours_required > 0)
                                    <div
                                        class="flex items-center justify-between p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/50">
                                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                            <div
                                                class="p-1.5 sm:p-2 bg-indigo-50 dark:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400 rounded-lg shrink-0">
                                                <flux:icon icon="truck" class="size-3.5 sm:size-4" />
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <flux:text size="xs" weight="semibold"
                                                    class="text-slate-700 dark:text-slate-300 truncate">Practical (PDC)
                                                </flux:text>
                                                <flux:text size="xs" class="text-slate-500 truncate">
                                                    {{ $enrollment->pdc_hours_completed }}/{{ $enrollment->pdc_hours_required }}
                                                    hrs
                                                </flux:text>
                                            </div>
                                        </div>
                                        @php
                                            $pdcConf =
                                                $moduleConfig[$enrollment->pdc_status] ?? $moduleConfig['not_started'];
                                        @endphp
                                        <flux:badge :color="$pdcConf['color']" variant="subtle" size="xs"
                                            class="shrink-0 hidden sm:inline-flex">
                                            <flux:icon :icon="$pdcConf['icon']" class="size-3 mr-1" />
                                            {{ str_replace('_', ' ', $enrollment->pdc_status) }}
                                        </flux:badge>
                                    </div>
                                @endif
                            </div>

                            {{-- Timeline & Grades --}}
                            <div
                                class="grid grid-cols-2 gap-2 sm:gap-4 mt-auto pt-3 sm:pt-4 border-t border-slate-100 dark:border-slate-800/50">
                                <div class="flex flex-col">
                                    <flux:text size="xs"
                                        class="text-slate-400 uppercase tracking-wider font-semibold mb-0.5 sm:mb-1">
                                        Target Date</flux:text>
                                    <flux:text size="xs" sm:size="sm" weight="medium"
                                        class="text-slate-700 dark:text-slate-300">
                                        {{ $enrollment->target_completion_date ? $enrollment->target_completion_date->format('M d, y') : 'Not Set' }}
                                    </flux:text>
                                </div>
                                <div class="flex flex-col items-end">
                                    <flux:text size="xs"
                                        class="text-slate-400 uppercase tracking-wider font-semibold mb-0.5 sm:mb-1">
                                        Final Grade</flux:text>
                                    <flux:text size="xs" sm:size="sm" weight="bold"
                                        class="{{ $enrollment->final_grade ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                        {{ $enrollment->final_grade ?? '---' }}
                                    </flux:text>
                                </div>
                            </div>

                        </div>

                        {{-- Card Footer: Finances & Actions --}}
                        <div
                            class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/20">

                            {{-- Financial Context --}}
                            <div
                                class="px-4 sm:px-5 py-2.5 sm:py-3 flex justify-between items-center border-b border-slate-100 dark:border-slate-800/50">
                                <flux:text size="xs" class="text-slate-500">Paid:
                                    ₱{{ number_format($enrollment->amount_paid, 0) }}</flux:text>
                                <flux:text size="xs" class="text-slate-500 font-medium">
                                    Bal: <span
                                        class="{{ $enrollment->balance > 0 ? 'text-red-500 font-bold' : 'text-emerald-500 font-bold' }}">₱{{ number_format($enrollment->balance, 0) }}</span>
                                </flux:text>
                            </div>

                            {{-- Actions --}}
                            <div class="px-4 sm:px-5 py-3 flex flex-col sm:flex-row gap-2">
                                <flux:button variant="primary" size="sm" class="w-full sm:flex-1 justify-center"
                                    icon="eye" :href="route('instructor.student.show', $enrollment)" wire:navigate>
                                    View enrollment
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($this->enrollments->hasPages())
                <div
                    class="px-4 sm:px-6 py-3 sm:py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                    {{ $this->enrollments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
