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
    @if(auth()->user()->status === 'active')
        {{-- HEADER: Operational Overview --}}
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Staff Operations Dashboard</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • Daily Operational Oversight
            </flux:text>
        </div>

        {{-- SECTION 2: MASTER SCHEDULE OVERSIGHT --}}
        <livewire:master-schedule />
    @elseif(auth()->user()->status === 'pending')
        <div class="flex flex-col items-center justify-center h-full py-12 px-6 text-center bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-2xl">
            <div class="p-4 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full mb-6">
                <flux:icon icon="clock" class="size-12" />
            </div>
            <flux:heading size="xl" class="mb-2">Account Pending Verification</flux:heading>
            <flux:text class="max-w-md mx-auto text-slate-600 dark:text-slate-400">
                Your staff account is currently under review. Please wait until an administrator verifies your account before you can access staff operations.
            </flux:text>
        </div>
    @elseif(auth()->user()->status === 'rejected')
        <div class="flex flex-col items-center justify-center h-full py-12 px-6 text-center bg-red-50/50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-2xl">
            <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full mb-6">
                <flux:icon icon="x-circle" class="size-12" />
            </div>
            <flux:heading size="xl" class="mb-2">Account Not Approved</flux:heading>
            <flux:text class="max-w-md mx-auto text-slate-600 dark:text-slate-400">
                Unfortunately, your staff application has been rejected. Please contact an administrator for more information.
            </flux:text>
        </div>
    @endif
</div>
