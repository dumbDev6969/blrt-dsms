
<?php
use Livewire\Component;
use App\Models\Enrollment;
use App\Services\AssessmentAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

new class extends Component
{
    public Enrollment $enrollment;

    public function mount(Enrollment $enrollment)
    {
        // Authenticate that the enrollment belongs to the student
        if ($enrollment->student_id !== Auth::user()->studentProfile->id) {
            abort(403);
        }
        
        $this->enrollment = $enrollment;
    }

    #[Computed]
    public function assessmentAnalytics()
    {
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
        <div class="flex items-center gap-4">
             <flux:button href="{{ route('student.academic-records') }}" variant="subtle" icon="arrow-left" size="sm" wire:navigate />
             <div>
                <flux:heading size="xl" weight="bold" class="tracking-tight">Course Performance Analytics</flux:heading>
                <flux:text size="sm" class="text-slate-500 mt-1">Detailed breakdown of your performance for {{ $this->enrollment->course->title }}.</flux:text>
             </div>
        </div>
    </div>

    {{-- Enrollment Quick Info --}}
    <div class="p-6 md:p-8 rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative overflow-hidden group mb-10">
        <div class="absolute -right-20 -top-20 size-64 bg-blue-500/5 dark:bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-colors duration-500"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="size-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                    <flux:icon icon="academic-cap" class="size-8" />
                </div>
                <div>
                    <flux:heading size="lg" class="font-black text-slate-900 dark:text-white leading-none mb-1">{{ $this->enrollment->course->title }}</flux:heading>
                    <flux:text size="sm" class="font-mono text-blue-600 dark:text-blue-400 font-bold">{{ $this->enrollment->code }}</flux:text>
                </div>
            </div>

            <div class="flex flex-wrap justify-center md:justify-end gap-6">
                <div class="flex flex-col items-center md:items-end">
                    <flux:text size="xs" class="text-slate-400 uppercase font-black tracking-widest mb-1">Final Grade</flux:text>
                    <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400 font-black">{{ $this->enrollment->final_grade ?? 'Pass' }}</flux:heading>
                </div>
                <div class="h-10 w-px bg-slate-100 dark:bg-slate-800 hidden md:block"></div>
                <div class="flex flex-col items-center md:items-end">
                    <flux:text size="xs" class="text-slate-400 uppercase font-black tracking-widest mb-1">Completion Date</flux:text>
                    <flux:heading size="lg" class="text-slate-900 dark:text-white font-black">{{ $this->enrollment->updated_at->format('M d, Y') }}</flux:heading>
                </div>
                <div class="h-10 w-px bg-slate-100 dark:bg-slate-800 hidden md:block"></div>
                <div class="flex flex-col items-center md:items-end">
                    <flux:text size="xs" class="text-slate-400 uppercase font-black tracking-widest mb-1">Instructor</flux:text>
                    <flux:heading size="lg" class="text-slate-900 dark:text-white font-black">{{ $this->enrollment->instructorProfile->user->name }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Analytics Component --}}
    @if($this->assessmentAnalytics)
        <x-assessment-analytics :analytics="$this->assessmentAnalytics" />
    @else
        <x-empty-state 
            variant="card" 
            icon="chart-bar-square"
            heading="No detailed analytics found"
            message="Simplified completion records are available above, but detailed practical assessment data was not recorded for this specific course."
        />
    @endif
</div>
