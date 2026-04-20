<?php

use Livewire\Component;
use App\Models\SystemMetric;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\BookingSession;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

new class extends Component
{
    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return [
                'percentage' => $current > 0 ? 100 : 0,
                'is_positive' => $current > 0,
                'is_neutral' => $current == 0
            ];
        }

        $difference = $current - $previous;
        $percentage = ($difference / $previous) * 100;

        return [
            'percentage' => round(abs($percentage), 1),
            'is_positive' => $difference > 0,
            'is_neutral' => $difference == 0
        ];
    }

    #[Computed]
    public function studentMetrics()
    {
        $currentMonth = User::role('Student')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonth = User::role('Student')->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        
        $trend = $this->calculateTrend($currentMonth, $lastMonth);

        return [
            'value' => $currentMonth,
            'trend' => $trend['percentage'] . '%',
            'is_positive' => $trend['is_positive'],
            'is_neutral' => $trend['is_neutral'],
            'label' => 'New Students',
            'icon' => 'user-plus',
            'classes' => [
                'bg-light' => 'bg-blue-500/10',
                'text' => 'text-blue-600 dark:text-blue-400',
                'border-hover' => 'hover:border-blue-500/30',
                'glow' => 'bg-blue-500/5',
                'glow-hover' => 'group-hover:bg-blue-500/10'
            ]
        ];
    }

    #[Computed]
    public function revenueMetrics()
    {
        $currentMonth = Enrollment::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount_paid');
        $lastMonth = Enrollment::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->sum('amount_paid');
        
        $trend = $this->calculateTrend($currentMonth, $lastMonth);

        return [
            'value' => '₱' . number_format($currentMonth, 2),
            'trend' => $trend['percentage'] . '%',
            'is_positive' => $trend['is_positive'],
            'is_neutral' => $trend['is_neutral'],
            'label' => 'Total Revenue',
            'icon' => 'banknotes',
            'classes' => [
                'bg-light' => 'bg-emerald-500/10',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'border-hover' => 'hover:border-emerald-500/30',
                'glow' => 'bg-emerald-500/5',
                'glow-hover' => 'group-hover:bg-emerald-500/10'
            ]
        ];
    }

    #[Computed]
    public function completionMetrics()
    {
        $currentMonth = Enrollment::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $lastMonth = Enrollment::where('status', 'completed')
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->count();
        
        $trend = $this->calculateTrend($currentMonth, $lastMonth);

        return [
            'value' => $currentMonth,
            'trend' => $trend['percentage'] . '%',
            'is_positive' => $trend['is_positive'],
            'is_neutral' => $trend['is_neutral'],
            'label' => 'Completed Courses',
            'icon' => 'check-badge',
            'classes' => [
                'bg-light' => 'bg-purple-500/10',
                'text' => 'text-purple-600 dark:text-purple-400',
                'border-hover' => 'hover:border-purple-500/30',
                'glow' => 'bg-purple-500/5',
                'glow-hover' => 'group-hover:bg-purple-500/10'
            ]
        ];
    }

    #[Computed]
    public function bookingMetrics()
    {
        $currentMonth = BookingSession::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonth = BookingSession::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        
        $trend = $this->calculateTrend($currentMonth, $lastMonth);

        return [
            'value' => $currentMonth,
            'trend' => $trend['percentage'] . '%',
            'is_positive' => $trend['is_positive'],
            'is_neutral' => $trend['is_neutral'],
            'label' => 'Total Bookings',
            'icon' => 'calendar',
            'classes' => [
                'bg-light' => 'bg-amber-500/10',
                'text' => 'text-amber-600 dark:text-amber-400',
                'border-hover' => 'hover:border-amber-500/30',
                'glow' => 'bg-amber-500/5',
                'glow-hover' => 'group-hover:bg-amber-500/10'
            ]
        ];
    }
};
?>

<div class="space-y-6" wire:poll.15s.visible>
    {{-- Analytical Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg" weight="bold">System Analytics</flux:heading>
            <flux:text size="xs" class="text-slate-500 mt-1">Month-over-month performance overview</flux:text>
        </div>
        <flux:badge color="zinc" variant="subtle" size="sm" class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Live Updates
        </flux:badge>
    </div>

    {{-- Analytical Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([$this->studentMetrics, $this->revenueMetrics, $this->completionMetrics, $this->bookingMetrics] as $metric)
            <div class="group relative rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-5 shadow-sm transition-all hover:shadow-md {{ $metric['classes']['border-hover'] }} overflow-hidden">
                {{-- Background Decoration --}}
                <div class="absolute -right-4 -top-4 size-24 {{ $metric['classes']['glow'] }} rounded-full blur-2xl {{ $metric['classes']['glow-hover'] }} transition-colors"></div>
                
                <div class="relative flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="p-2.5 rounded-xl {{ $metric['classes']['bg-light'] }} {{ $metric['classes']['text'] }}">
                            <flux:icon icon="{{ $metric['icon'] }}" class="size-5" />
                        </div>
                        
                        @if(!$metric['is_neutral'])
                            <flux:badge size="sm" :color="$metric['is_positive'] ? 'emerald' : 'rose'" variant="subtle" class="flex items-center gap-1 font-bold">
                                <flux:icon icon="{{ $metric['is_positive'] ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                                {{ $metric['trend'] }}
                            </flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc" variant="subtle" class="font-bold">
                                0%
                            </flux:badge>
                        @endif
                    </div>

                    <div>
                        <flux:text size="xs" weight="medium" class="text-slate-500 uppercase tracking-wider mb-1">{{ $metric['label'] }}</flux:text>
                        <div class="flex items-baseline gap-2">
                            <flux:heading size="xl" weight="black" class="tracking-tight">{{ $metric['value'] }}</flux:heading>
                        </div>
                        <flux:text size="xs" class="text-slate-400 mt-1 flex items-center gap-1">
                            @if($metric['is_neutral'])
                                Same as last month
                            @else
                                {{ $metric['trend'] }} {{ $metric['is_positive'] ? 'increase' : 'decrease' }} since last month
                            @endif
                        </flux:text>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>