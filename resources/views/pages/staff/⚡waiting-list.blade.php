<?php

use App\Models\Enrollment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\InstructorProfile;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    public $selectedEnrollmentId = null;
    public $search = '';
    public $type = 'all';
    public $modalSearch = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function waitlistCounts()
    {
        return [
            'total' => Enrollment::where('status', 'waiting_list')->count(),
            'practical' => Enrollment::where('status', 'waiting_list')
                ->whereHas('course', fn($q) => $q->where('type', 'practical'))->count(),
            'theoretical' => Enrollment::where('status', 'waiting_list')
                ->whereHas('course', fn($q) => $q->where('type', 'theoretical'))->count(),
            'longest_wait' => (int) (Enrollment::where('status', 'waiting_list')
                ->oldest()->first()?->created_at?->diffInDays() ?? 0),
        ];
    }

    #[Computed]
    public function enrollments()
    {
        return Enrollment::where('status', 'waiting_list')
            ->with(['studentProfile.user', 'course'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('studentProfile.user', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type !== 'all', function ($query) {
                $query->whereHas('course', function ($q) {
                    $q->where('type', $this->type);
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function selectedEnrollment()
    {
        if (!$this->selectedEnrollmentId) return null;
        return Enrollment::with('course')->find($this->selectedEnrollmentId);
    }

    public function selectEnrollment($id)
    {
        $this->selectedEnrollmentId = $id;
        $this->modal('assign-instructor-modal')->show();
    }

    public function instructors()
    {
        return InstructorProfile::with(['user'])
            ->where('is_active', true)
            ->withCount(['enrollments as active_students_count' => function ($query) {
                $query->where('status', 'active');
            }])
            ->withAvg('instructorPerformances', 'rating')
            ->when($this->modalSearch, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->modalSearch . '%');
                });
            })
            ->orderBy('active_students_count', 'asc')
            ->get();
    }

    public function assignInstructor($instructorId)
    {
        if (!$this->selectedEnrollmentId) return;

        DB::transaction(function () use ($instructorId) {
            $enrollment = Enrollment::find($this->selectedEnrollmentId);
            $enrollment->update([
                'instructor_id' => $instructorId,
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]);
        });

        $this->selectedEnrollmentId = null;
        $this->modal('assign-instructor-modal')->close();
        
        Flux::toast('Instructor assigned successfully.', variant: 'success');
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">
    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Student Waiting List</flux:heading>
            <flux:text>Students who pending manual instructor assignment after approval.</flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Total in Waiting List</flux:text>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="user-group" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->waitlistCounts['total'] }}</flux:heading>
                <flux:text>students</flux:text>
            </div>
        </div>

        {{-- Practical --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Practical Requests</flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="truck" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->waitlistCounts['practical'] }}</flux:heading>
                <flux:text>active</flux:text>
            </div>
        </div>

        {{-- Theoretical --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Theoretical</flux:text>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->waitlistCounts['theoretical'] }}</flux:heading>
                <flux:text>pending</flux:text>
            </div>
        </div>

        {{-- Longest Wait --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Longest Wait</flux:text>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->waitlistCounts['longest_wait'] }}</flux:heading>
                <flux:text>days</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-5">
        {{-- Filter Tabs & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between p-1 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg w-full gap-2 md:gap-0">
            <div class="flex flex-wrap gap-1 p-1">
                <button wire:click="$set('type', 'all')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $type === 'all' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    All ({{ $this->waitlistCounts['total'] }})
                </button>
                <button wire:click="$set('type', 'practical')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $type === 'practical' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Practical ({{ $this->waitlistCounts['practical'] }})
                </button>
                <button wire:click="$set('type', 'theoretical')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $type === 'theoretical' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Theoretical ({{ $this->waitlistCounts['theoretical'] }})
                </button>
            </div>
            <div class="pr-1 w-full md:w-72">
                <x-live-search placeholder="Search name or ID..." />
            </div>
        </div>

        {{-- Table Container --}}
        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">Manual Assignment Required</flux:heading>
                    <flux:text size="sm" class="mt-1">Students matched manually based on schedule/skill overrides.</flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->enrollments->total() }} Students
                </flux:badge>
            </div>

            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Enrollment Code</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Student</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Course Details</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-center">Wait Duration</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse ($this->enrollments as $enrollment)
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <flux:text weight="bold" class="font-mono">{{ $enrollment->code }}</flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar size="sm" :src="$enrollment->studentProfile->user->avatar_url ?? ''" :initials="$enrollment->studentProfile->user->initials()" class="ring-2 ring-zinc-50 dark:ring-zinc-800" />
                                        <div class="flex flex-col">
                                            <flux:text weight="medium" class="text-zinc-900 dark:text-white">{{ $enrollment->studentProfile->user->name }}</flux:text>
                                            <flux:text size="xs" class="text-zinc-500">{{ $enrollment->studentProfile->user->email }}</flux:text>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm" weight="medium">{{ $enrollment->course->title }}</flux:text>
                                        <div class="flex gap-1.5 mt-0.5">
                                            <flux:badge size="xs" color="blue" variant="subtle" class="uppercase font-bold tracking-tighter text-[9px]">{{ $enrollment->course->type }}</flux:badge>
                                            @if($enrollment->course->type === 'practical')
                                                <flux:badge size="xs" color="zinc" variant="subtle" class="uppercase font-bold tracking-tighter text-[9px]">PDC</flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <flux:text size="sm" weight="bold" color="red">{{ (int) $enrollment->created_at->diffInDays() }} Days</flux:text>
                                        <flux:text size="xs" class="text-zinc-500">{{ $enrollment->created_at->diffForHumans() }}</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="sm" variant="primary" wire:click="selectEnrollment({{ $enrollment->id }})">Assign Instructor</flux:button>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state 
                                variant="table" 
                                :colspan="5"
                                icon="check-circle"
                                heading="All set! No students on the waiting list."
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($this->enrollments->hasPages())
                <div class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->enrollments->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL --}}
    <flux:modal name="assign-instructor-modal" class="md:min-w-[800px] p-0 overflow-hidden !bg-transparent border-0 shadow-none">
        <div class="flex flex-col h-[85vh] max-h-[750px] bg-white dark:bg-zinc-950 rounded-3xl overflow-hidden">
            {{-- Modal Header --}}
            <div class="relative p-8 bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-1">
                        <flux:heading size="xl" weight="bold" class="tracking-tight">Match Perfect Instructor</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Analyze workload, ratings, and availability for optimal student pairing.</flux:text>
                        
                        @if($this->selectedEnrollment)
                            <div class="mt-4 flex items-center gap-3 p-3 bg-zinc-900 dark:bg-emerald-900/30 text-white dark:text-emerald-100 rounded-xl shadow-sm border border-zinc-900 dark:border-emerald-800">
                                <div class="p-2 bg-white/10 dark:bg-emerald-500/20 rounded-lg">
                                    <flux:icon icon="academic-cap" class="size-5 text-zinc-100 dark:text-emerald-400" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-zinc-400 dark:text-emerald-400/80 uppercase font-black tracking-widest">Student Requires</span>
                                    <span class="text-sm font-bold leading-tight">{{ $this->selectedEnrollment->course->title }} <span class="opacity-75 font-normal ml-1 border-l border-zinc-700 dark:border-emerald-700/50 pl-2">{{ ucfirst($this->selectedEnrollment->course->type) }}</span></span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="w-full md:w-72 relative">
                        <flux:input wire:model.live.debounce.300ms="modalSearch" placeholder="Filter instructors..." icon="magnifying-glass" variant="filled" class="!rounded-2xl border-none bg-white dark:bg-zinc-900 shadow-sm" />
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-white dark:bg-zinc-950">
                @forelse ($this->instructors() as $instructor)
                    <div class="group relative p-5 rounded-[2rem] bg-white dark:bg-zinc-900/40 hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-all duration-500 hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-0.5">
                        @php
                            $isSkillMatch = false;
                            if ($this->selectedEnrollment) {
                                $type = $this->selectedEnrollment->course->type;
                                $title = strtolower($this->selectedEnrollment->course->title);
                                $capabilities = array_map('strtolower', array_merge((array)($instructor->skills ?? []), (array)($instructor->vehicle_types ?? [])));
                                
                                if ($type === 'theoretical' && in_array('theoretical', $capabilities)) {
                                    $isSkillMatch = true;
                                } elseif ($type === 'practical') {
                                    foreach ($capabilities as $cap) {
                                        if ($cap !== 'practical' && $cap !== 'theoretical' && strlen($cap) > 2) {
                                            if (str_contains($title, $cap) || str_contains($cap, 'motorcycle') && str_contains($title, 'motorcycle')) {
                                                $isSkillMatch = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        @endphp
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            {{-- Profile Info --}}
                            <div class="flex items-center gap-5 lg:w-1/3">
                                <div class="relative">
                                    <flux:avatar :src="$instructor->user->avatar_url ?? ''" size="xl" class="size-16 rounded-2xl ring-4 ring-white dark:ring-zinc-800 shadow-md" />
                                    <div class="absolute -bottom-1 -right-1 size-5 rounded-full border-4 border-white dark:border-zinc-900 bg-emerald-500"></div>
                                </div>
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <flux:heading size="md" weight="bold" class="truncate block">{{ $instructor->user->name }}</flux:heading>
                                        @if($instructor->status !== 'approved')
                                            <flux:badge size="xs" color="amber" variant="subtle" class="scale-90 origin-left border border-amber-500/20 shadow-sm">{{ ucfirst($instructor->status) }}</flux:badge>
                                        @endif
                                        @if(!$instructor->is_active)
                                            <flux:badge size="xs" color="rose" variant="subtle" class="scale-90 origin-left border border-rose-500/20 shadow-sm">Inactive</flux:badge>
                                        @endif
                                        @if($isSkillMatch)
                                            <flux:badge size="xs" color="emerald" variant="subtle" class="scale-90 origin-left border border-emerald-500/20 shadow-sm"><flux:icon icon="check-circle" class="size-3 mr-1" /> Skill Match</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text size="xs" class="text-zinc-400 truncate block">{{ $instructor->user->email }}</flux:text>
                                    
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="flex items-center gap-1 px-2 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-lg border border-amber-100 dark:border-amber-900/30">
                                            <flux:icon icon="star" class="size-3 fill-current" />
                                            <span class="text-[10px] font-black leading-none">{{ number_format($instructor->instructor_performances_avg_rating ?? 5.0, 1) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-900/30">
                                            <flux:icon icon="user-group" class="size-3" />
                                            <span class="text-[10px] font-black leading-none">{{ $instructor->active_students_count }} Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Availability Timeline & Skills --}}
                            <div class="flex-1 space-y-4">
                                {{-- Skills & Vehicles --}}
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($instructor->skills ?? [] as $skill)
                                        <div class="px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 rounded-full border border-zinc-200 dark:border-zinc-700/50">
                                            {{ $skill }}
                                        </div>
                                    @endforeach
                                    @foreach ($instructor->vehicle_types ?? [] as $vehicle)
                                        <div class="px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full border border-blue-200 dark:border-blue-800">
                                            {{ $vehicle }}
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Timeline Bar --}}
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <flux:text size="min" class="uppercase font-bold tracking-widest text-zinc-400">Weekly Performance Schedule</flux:text>
                                        <flux:text size="min" class="text-emerald-500 font-bold uppercase">{{ count((array)$instructor->weekly_schedule) }} Slots</flux:text>
                                    </div>
                                    <div class="flex gap-1">
                                        @foreach (['Monday' => 'M', 'Tuesday' => 'T', 'Wednesday' => 'W', 'Thursday' => 'T', 'Friday' => 'F', 'Saturday' => 'S', 'Sunday' => 'S'] as $dayName => $initial)
                                            @php
                                                $isActive = isset($instructor->weekly_schedule[$dayName]) || isset($instructor->weekly_schedule[strtolower(substr($dayName, 0, 3))]);
                                            @endphp
                                            <div class="flex-1 group/day relative">
                                                <div class="h-1.5 rounded-full transition-all duration-300 {{ $isActive ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-zinc-100 dark:bg-zinc-800' }}"></div>
                                                <span class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold {{ $isActive ? 'text-zinc-700 dark:text-zinc-200' : 'text-zinc-400/50' }}">{{ $initial }}</span>
                                                
                                                {{-- Tooltip highlight --}}
                                                <div class="absolute -top-1 left-0 right-0 py-4 opacity-0 group-hover/day:opacity-100 transition-opacity pointer-events-none text-center">
                                                    <div class="bg-zinc-900 text-white text-[8px] px-1.5 py-0.5 rounded shadow-lg">{{ $dayName }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="lg:pl-6 flex items-center justify-end">
                                <flux:button size="sm" variant="primary" wire:click="assignInstructor({{ $instructor->id }})" class="!rounded-2xl px-6 py-2.5 font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40">
                                    Assign Instructor
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        variant="card" 
                        icon="magnifying-glass"
                        heading="No instructors found"
                        message="Try adjusting your search filters or skill requirements."
                        wire-action="$set('modalSearch', '')"
                        wire-label="Clear Search"
                    />
                @endforelse
            </div>
            
            {{-- Modal Footer --}}
            <div class="p-4 px-8 bg-zinc-50 dark:bg-zinc-900/50 flex justify-between items-center">
                <flux:text size="xs" class="text-zinc-400">Total Available: <span class="font-bold text-zinc-700 dark:text-zinc-200">{{ count($this->instructors()) }}</span></flux:text>
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm" class="!rounded-xl">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>