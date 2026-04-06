@props(['enrollment'])

<flux:modal name="score-{{ $enrollment->id }}" class="md:w-[450px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $enrollment->status === 'completed' ? 'Update Official Grade' : 'Official Assessment Grade' }}</flux:heading>
            <flux:text class="mt-2">Provide or update the final evaluation result for <strong>{{ $enrollment->studentProfile->user->name ?? 'N/A' }}</strong>.</flux:text>
        </div>

        <div class="space-y-4">
            <flux:input wire:model="grades.{{ $enrollment->id }}" label="Numerical Score (%)" type="number" step="0.01" max="100" min="0" placeholder="e.g. 95.00" icon="chart-bar" />
            
            <flux:field>
                <flux:label>Final Evaluation Result</flux:label>
                <div class="grid grid-cols-2 gap-3 mt-2">
                    {{-- Pass Button --}}
                    <label class="relative flex items-center justify-between p-4 rounded-xl border-2 border-slate-100 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/10 dark:hover:bg-emerald-900/10 transition-all cursor-pointer group has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20">
                        <input type="radio" wire:model="results.{{ $enrollment->id }}" value="pass" class="sr-only" />
                        <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 rounded-lg group-hover:scale-110 transition-transform group-has-[:checked]:bg-emerald-500 group-has-[:checked]:text-white">
                                    <flux:icon icon="check" class="size-4" />
                                </div>
                                <span class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight group-has-[:checked]:text-emerald-700 dark:group-has-[:checked]:text-emerald-400">Passed</span>
                        </div>
                    </label>

                    {{-- Fail Button --}}
                    <label class="relative flex items-center justify-between p-4 rounded-xl border-2 border-slate-100 dark:border-slate-800 hover:border-red-500 hover:bg-red-50/10 dark:hover:bg-red-900/10 transition-all cursor-pointer group has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20">
                        <input type="radio" wire:model="results.{{ $enrollment->id }}" value="fail" class="sr-only" />
                        <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900/40 text-red-600 rounded-lg group-hover:scale-110 transition-transform group-has-[:checked]:bg-red-600 group-has-[:checked]:text-white">
                                    <flux:icon icon="x-mark" class="size-4" />
                                </div>
                                <span class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight group-has-[:checked]:text-red-700 dark:group-has-[:checked]:text-red-400">Failed</span>
                        </div>
                    </label>
                </div>
            </flux:field>

            <flux:textarea wire:model="remarks.{{ $enrollment->id }}" label="Instructor Remarks & Observations" placeholder="Detailed feedback on driving performance, technical skills, or specific areas for improvement..." rows="4" />
        </div>

        <div class="flex gap-3">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="button" variant="primary" wire:click="submitGrade({{ $enrollment->id }})" wire:loading.attr="disabled" wire:target="submitGrade({{ $enrollment->id }})">Submit Grade</flux:button>
        </div>
    </div>
</flux:modal>
