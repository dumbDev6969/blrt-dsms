<?php

use Livewire\Component;
use App\Models\Vehicle;
use Carbon\Carbon;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function vehicles()
    {
        return Vehicle::orderBy('status')->paginate(3, ['*'], 'mPage');
    }

    public function attentionCount()
    {
        return Vehicle::where('status', 'maintenance')
            ->orWhere('next_maintenance_date', '<=', Carbon::now()->addDays(7))
            ->count();
    }
};
?>

<div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm flex flex-col">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg">
                <flux:icon icon="truck" class="size-5 text-slate-600 dark:text-slate-400" />
            </div>
            <flux:heading size="lg" weight="bold">Vehicles</flux:heading>
        </div>
        @if ($this->attentionCount() > 0)
            <flux:badge color="amber" variant="subtle" size="sm" class="font-bold tracking-tight">
                {{ $this->attentionCount() }} Action Required
            </flux:badge>
        @endif
    </div>

    @if ($this->vehicles()->total() > 0)
        <div class="space-y-4">
            <div class="flex justify-between items-center px-1">
                <flux:heading size="sm" weight="bold" class="text-slate-500 uppercase tracking-widest">Fleet</flux:heading>
                <flux:text size="xs" class="text-slate-400 font-bold tracking-tighter uppercase">Status</flux:text>
            </div>
            
            <div class="space-y-3">
                @foreach ($this->vehicles() as $vehicle)
                    @php
                        $needsAttention = $vehicle->status === 'maintenance' ||
                            ($vehicle->next_maintenance_date && $vehicle->next_maintenance_date <= now()->addDays(7));
                    @endphp
                    <div class="flex justify-between items-center p-3 rounded-xl bg-white dark:bg-slate-900 border {{ $needsAttention ? 'border-amber-200/50 dark:border-amber-800/30 hover:border-amber-300 dark:hover:border-amber-700' : 'border-slate-100 dark:border-slate-800' }} shadow-sm transition-all">
                        <div class="flex flex-col">
                            <flux:text size="sm" weight="bold" class="text-slate-900 dark:text-white">
                                {{ $vehicle->model }}
                            </flux:text>
                            <flux:text size="xs" class="text-slate-500 dark:text-slate-400 font-mono">
                                {{ $vehicle->plate_number }}
                            </flux:text>
                        </div>
                        <div class="text-right">
                            @if ($vehicle->status === 'maintenance')
                                <flux:badge size="sm" color="red" variant="subtle" class="font-bold">In Maintenance</flux:badge>
                            @elseif ($needsAttention)
                                <flux:badge size="sm" color="amber" variant="solid" class="font-bold">
                                    {{ $vehicle->next_maintenance_date?->format('M d') ?? 'NOW' }}
                                </flux:badge>
                            @else
                                <flux:badge size="sm" color="emerald" variant="subtle" class="font-bold">Available</flux:badge>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Footer --}}
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800 mt-2">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                    Pg {{ $this->vehicles()->currentPage() }} / {{ $this->vehicles()->lastPage() }}
                </div>
                <div class="flex gap-2">
                    <flux:button size="xs" icon="chevron-left" variant="subtle"
                        wire:click="previousPage('mPage')" :disabled="$this->vehicles()->onFirstPage()" />
                    <flux:button size="xs" icon="chevron-right" variant="subtle" wire:click="nextPage('mPage')"
                        :disabled="!$this->vehicles()->hasMorePages()" />
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col justify-center">
            <x-empty-state 
                variant="card" 
                icon="check-circle" 
                heading="No Vehicles" 
                message="No vehicles have been registered in the fleet yet." 
            />
        </div>
    @endif
</div>