<?php

use Livewire\Component;
use App\Models\EnrollmentForm;
use Livewire\Attributes\Computed;

new class extends Component {
    #[Computed]
    public function enrollmentStatus()
    {
        return [
            'pending' => EnrollmentForm::where('status', 'submitted')->count(),
            'approved' => EnrollmentForm::where('status', 'approved')->count(),
            'rejected' => EnrollmentForm::where('status', 'rejected')->count(),
        ];
    }

    
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Operational Overview --}}
    
    <div>
        <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Staff Operations Dashboard
        </flux:heading>
        <flux:text>
            {{ now()->format('l, F j, Y') }} • Daily Operational Oversight
        </flux:text>
    </div>
    

    {{-- SECTION 1: ENROLLMENT STATUS CARDS --}}
    

    {{-- SECTION 2: MASTER SCHEDULE OVERSIGHT --}}
   
     <livewire:master-schedule />
    
</div>
