<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use App\Models\InstructorProfile;

use App\Services\InstructorPerformanceService;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

new class extends Component {
    public $searchInstructor = '';

    #[Computed]
    public function pendingDocsCount()
    {
        return Document::where('status', 'pending')->count();
    }

    #[Computed]
    public function revenueData()
    {
        $now = Carbon::now();
        $thisMonth = Enrollment::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount_paid');

        $lastMonth = Enrollment::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->sum('amount_paid');

        $difference = $thisMonth - $lastMonth;
        $trend = $lastMonth > 0 ? ($difference / $lastMonth) * 100 : 0;

        return [
            'value' => $thisMonth,
            'trend' => number_format($trend, 1) . '%',
            'trend_color' => $trend >= 0 ? 'emerald' : 'rose',
            'subtext' => 'vs last month: ₱' . number_format($lastMonth, 2),
        ];
    }

    #[Computed]
    public function enrollmentStats()
    {
        $active = Enrollment::where('status', 'active')->count();
        $tdc = Enrollment::where('status', 'active')
            ->whereHas('course', function ($q) {
                $q->where('type', 'theoretical');
            })
            ->count();
        $pdc = Enrollment::where('status', 'active')
            ->whereHas('course', function ($q) {
                $q->whereIn('type', ['practical', 'comprehensive']);
            })
            ->count();

        return [
            'total' => $active,
            'tdc' => $tdc,
            'pdc' => $pdc,
        ];
    }

    #[Computed]
    public function pendingActions()
    {
        $forms = EnrollmentForm::where('status', 'submitted')->count();
        $docs = $this->pendingDocsCount;

        return [
            'total' => $forms + $docs,
            'forms' => $forms,
            'docs' => $docs,
        ];
    }

    #[Computed]
    public function passedStudentsCount()
    {
        $tdc = Enrollment::where('final_result', 'pass')
            ->whereHas('course', function ($query) {
                $query->where('type', 'theoretical');
            })
            ->count();

        $pdc = Enrollment::where('final_result', 'pass')
            ->whereHas('course', function ($query) {
                $query->whereIn('type', ['practical', 'comprehensive']);
            })
            ->count();

        $totalTdc = Enrollment::whereHas('course', function ($query) {
            $query->where('type', 'theoretical');
        })->count();

        $totalPdc = Enrollment::whereHas('course', function ($query) {
            $query->whereIn('type', ['practical', 'comprehensive']);
        })->count();

        return [
            'tdc' => $tdc,
            'pdc' => $pdc,
            'total_tdc' => $totalTdc,
            'total_pdc' => $totalPdc,
        ];
    }

    #[Computed]
    public function instructorsPerformances()
    {
        $service = app(InstructorPerformanceService::class);
        $query = InstructorProfile::with('user')->where('status', 'verified')->where('is_active', true);

        if (!empty($this->searchInstructor)) {
            return $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->searchInstructor . '%');
            })->take(4)->get();
        }

        $allInstructors = $query->get();
        $preview = collect();

        // 1. Find the best TDC instructor (theoretical)
        $tdcInstructor = $allInstructors->first(function ($instructor) {
            return $instructor->enrollments()->whereHas('course', fn($q) => $q->where('type', 'theoretical'))->exists();
        });

        if ($tdcInstructor) {
            $preview->push($tdcInstructor);
        }

        // 2. Find a different PDC instructor (practical/comprehensive)
        $pdcInstructor = $allInstructors->where('id', '!=', $tdcInstructor?->id)->first(function ($instructor) {
            return $instructor->enrollments()->whereHas('course', fn($q) => $q->whereIn('type', ['practical', 'comprehensive']))->exists();
        });

        if ($pdcInstructor) {
            $preview->push($pdcInstructor);
        }

        return $preview;
    }
    #[Computed]
    public function pendingDocuments()
    {
        return Document::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
    }


};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Admin Overview --}}
  
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Admin Dashboard</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • System Overview
            </flux:text>
        </div>
   

    {{-- CRITICAL ALERTS SECTION --}}
    <div class="space-y-3">
        {{-- Document Verification Alert --}}
        @if ($this->pendingDocsCount > 0)
            <flux:callout icon="document-text" variant="info" class="w-full">
                <div class="flex justify-between items-center w-full">
                    <div>
                        <flux:callout.heading>{{ $this->pendingDocsCount }} Documents Awaiting Verification
                        </flux:callout.heading>
                        <flux:callout.text>Medical certificates, IDs, and other compliance documents need review.
                        </flux:callout.text>
                    </div>
                    <flux:button size="sm" variant="ghost" href="{{ route('admin.pending-documents') }}">View Queue
                    </flux:button>
                </div>
            </flux:callout>
        @endif
    </div>
    {{-- SECTION 1: KEY PERFORMANCE INDICATORS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- KPI: Total Revenue (This Month) --}}
        <x-kpi-cards label="Monthly Revenue" value="₱{{ number_format($this->revenueData['value'], 2) }}"
            trend="{{ $this->revenueData['trend'] }}" trend-color="{{ $this->revenueData['trend_color'] }}"
            icon="banknotes" color="emerald" subtext="{{ $this->revenueData['subtext'] }}" />
        {{-- KPI: Active Enrollments --}}
        <x-kpi-cards label="Active Enrollments" value="{{ $this->enrollmentStats['total'] }}" trend="students"
            trend-color="zinc" icon="academic-cap" color="blue">
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">TDC: {{ $this->enrollmentStats['tdc'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">PDC: {{ $this->enrollmentStats['pdc'] }}</flux:text>
            </div>
        </x-kpi-cards>
        {{-- KPI: Pending Actions --}}
        <x-kpi-cards label="Pending Actions" value="{{ $this->pendingActions['total'] }}" trend="items"
            trend-color="zinc" icon="clock" color="amber">
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">Forms: {{ $this->pendingActions['forms'] }}
                </flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">Docs: {{ $this->pendingActions['docs'] }}</flux:text>
            </div>
        </x-kpi-cards>
        {{-- KPI: Passed Students --}}
        <x-kpi-cards label="Passed Students"
            value="{{ $this->passedStudentsCount['tdc'] + $this->passedStudentsCount['pdc'] }} / {{ $this->passedStudentsCount['total_tdc'] + $this->passedStudentsCount['total_pdc'] }}" trend="Total"
            trend-color="emerald" icon="check-badge" color="emerald">
            <div class="flex gap-2 mt-2">
                <flux:text color="emerald" size="xs">TDC: {{ $this->passedStudentsCount['tdc'] }} / {{ $this->passedStudentsCount['total_tdc'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text color="emerald" size="xs">PDC: {{ $this->passedStudentsCount['pdc'] }} / {{ $this->passedStudentsCount['total_pdc'] }}</flux:text>
            </div>
        </x-kpi-cards>
    </div>

    {{-- OPERATIONAL DASHBOARD --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{--  Trends & Charts --}}
        <div class="lg:col-span-2 space-y-6">
            <livewire:system-metrics />
            {{-- INSTRUCTOR PERFORMANCE SNAPSHOT --}}
            <div class="relative overflow-hidden rounded-2xl p-6 border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{-- Decorative Glow --}}
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative flex flex-col gap-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <flux:heading size="lg" weight="bold">Instructor Performance</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">Search and view instructor performance metrics</flux:text>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <x-live-search model="searchInstructor" placeholder="Search instructor..." class="w-full md:w-64" />
                            <flux:button size="sm" variant="ghost" icon="arrow-right" :href="route('admin.instructor-performances')" wire:navigate>All</flux:button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse ($this->instructorsPerformances as $instructor)
                            <livewire:instructor-performance-card 
                                :instructor="$instructor"
                                :profileUrl="route('admin.instructor.evaluations', $instructor->id)"
                                :key="'instructor-perf-' . $instructor->id"
                            />
                        @empty
                            <flux:text size="sm" class="text-center py-4 w-full">No active instructors found matching your search.</flux:text>
                        @endforelse
                    </div>
                </div>
            </div>
            </div>
        {{-- RIGHT COLUMN (1/3 width): Performance & Queue --}}
        <div class="space-y-6">
            {{-- DOCUMENT VERIFICATION QUEUE --}}
            <div
                wire:poll.15s
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" weight="bold">Document Queue</flux:heading>
                    <flux:badge color="amber" variant="subtle" size="sm">{{ $this->pendingDocsCount }} Pending
                    </flux:badge>
                </div>
                <div class="space-y-3">
                    @forelse ($this->pendingDocuments as $doc)
                        <div
                            class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <div class="p-2 bg-white dark:bg-slate-900 rounded shadow-sm">
                                <flux:icon icon="document-text" class="size-4 text-blue-600" />
                            </div>
                            <div class="flex-1">
                                <flux:heading size="xs" weight="semibold">{{ Str::headline($doc->type) }}</flux:heading>
                                <flux:text size="xs" class="text-slate-500">{{ $doc->user->name }}</flux:text>
                            </div>
                            <flux:button size="xs" variant="ghost" :href="route('admin.pending-documents', ['search' => $doc->user->name])" wire:navigate>View</flux:button>
                        </div>
                    @empty
                        <flux:text size="xs" class="text-slate-500 text-center py-4">No documents in queue.</flux:text>
                    @endforelse
                </div>
                <div class="mt-4">
                    <flux:button size="sm" variant="ghost" icon="arrow-right" class="w-full" :href="route('admin.pending-documents')" wire:navigate>
                        All Docs</flux:button>
                </div>
            </div>
            {{-- VEHICLES --}}
            <livewire:vehicles />
        </div>
    </div>
</div>
