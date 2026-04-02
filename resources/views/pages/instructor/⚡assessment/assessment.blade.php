<div class="flex h-full w-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100 p-4 sm:p-6 max-w-5xl mx-auto">
    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-2">
            <flux:badge color="amber" variant="subtle" size="xs" class="uppercase">Official BLRT Assessment Form</flux:badge>
        </div>
        <div class="flex items-center gap-5">
            <div class="h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-700 dark:text-blue-300 font-black text-2xl border-2 border-blue-200 dark:border-blue-800">
                {{ substr($enrollment->studentProfile->user->name ?? 'S', 0, 1) }}
            </div>
            <div>
                <flux:heading size="xl" class="text-2xl font-black tracking-tight">{{ $enrollment->studentProfile->user->name }}</flux:heading>
                <flux:text class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="font-bold text-slate-500">{{ $enrollment->code }}</span>
                    <span class="hidden sm:inline text-slate-300">|</span>
                    <span class="font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide text-xs">Vehicle: {{ $enrollment->enrollmentForm->vehicle_category ?? 'N/A' }}</span>
                </flux:text>
            </div>
        </div>
        <div class="flex flex-col items-end gap-1">
            <flux:text size="xs" class="font-bold uppercase tracking-widest text-slate-400">Date of Assessment</flux:text>
            <flux:heading size="lg" class="tabular-nums font-mono">{{ $bookingSession->start_time->format('M d, Y') }}</flux:heading>
            <flux:text size="xs" class="text-slate-500 tabular-nums">Session: {{ $bookingSession->start_time->format('h:i A') }} - Current</flux:text>
        </div>
    </div>

    {{-- PROGRESS DASHBOARD --}}
    <div class="p-6 bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-900/20 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex-1 w-full">
                <div class="flex justify-between mb-2">
                    <flux:heading size="md" class="text-blue-800 dark:text-blue-300 font-bold uppercase tracking-tight">Cumulative PDC Progress</flux:heading>
                    <flux:text size="sm" class="font-bold text-blue-700 dark:text-blue-400">{{ $this->currentProgress['completed'] }} / {{ $this->currentProgress['required'] }} Hours</flux:text>
                </div>
                {{-- Custom Progress Bar --}}
                <div class="w-full h-4 bg-blue-200 dark:bg-blue-800/40 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 dark:bg-blue-500 transition-all duration-1000 shadow-lg shadow-blue-500/30" style="width: {{ $this->currentProgress['percent'] }}%"></div>
                </div>
                <flux:text size="xs" class="mt-2 text-blue-600/70 dark:text-blue-400/60 leading-tight">Hours update automatically after each session and feed into this cumulative assessment form.</flux:text>
            </div>
            
            @if(count($this->sessionHistory) > 0)
                <div class="flex flex-wrap gap-2 md:justify-end">
                    @foreach($this->sessionHistory as $session)
                        <div class="px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-blue-200/50 dark:border-blue-800/50 shadow-sm flex flex-col items-center min-w-[80px]">
                            <flux:text size="xs" class="font-bold text-slate-400 uppercase tracking-tighter">Session {{ $loop->iteration }}</flux:text>
                            <flux:text size="sm" class="font-black text-blue-600">{{ round($session->start_time->diffInMinutes($session->end_time) / 60, 1) }}h</flux:text>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- IMPORTANT NOTE --}}
    <div class="p-5 bg-amber-50 dark:bg-amber-900/10 border-l-4 border-amber-500 rounded-r-xl shadow-sm">
        <div class="flex gap-4">
            <flux:icon icon="information-circle" class="size-6 text-amber-600 dark:text-amber-400" />
            <div>
                <flux:heading size="md" class="text-amber-800 dark:text-amber-300 font-bold mb-1">Important Note for Passing:</flux:heading>
                <flux:text size="sm" class="text-amber-700 dark:text-amber-400 font-medium leading-relaxed">
                    To pass, you must not have **any errors** in <span class="font-black">PRE-DRIVE CHECKLIST</span>, **zero marks** in <span class="font-black">CRITICAL DRIVING ERROR</span>, and **no more than 15 Errors/Poor** marks for the Road Assessment.
                </flux:text>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- 2. PRE DRIVING CHECKLIST (Checked = Error) --}}
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <flux:heading size="lg" class="font-black uppercase tracking-tight text-slate-800 dark:text-slate-200">2. Pre Driving Checklist</flux:heading>
                    <div class="px-3 py-1 rounded-full {{ $pre_drive_errors > 0 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }} text-xs font-bold uppercase">
                        {{ $pre_drive_errors }} Errors
                    </div>
                </div>
                <div class="space-y-3">
                    @foreach($this->preDriveLabels as $key => $label)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer">
                            <flux:checkbox wire:model.live="pre_drive_checklist.{{ $key }}" />
                            <span class="text-sm font-medium leading-tight pt-0.5">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 1. IMMEDIATE FAILS (Checked = Error) --}}
            <div class="p-6 bg-red-50/50 dark:bg-red-900/10 rounded-2xl border border-red-100 dark:border-red-900/20 shadow-sm">
                <div class="flex justify-between items-center mb-6 text-red-800 dark:text-red-400">
                    <flux:heading size="lg" class="font-black uppercase tracking-tight">1. Immediate Fails</flux:heading>
                    <div class="px-3 py-1 bg-red-600 text-white rounded-full text-xs font-bold uppercase">
                        {{ $immediate_fail_count }} Marks
                    </div>
                </div>
                <div class="space-y-3">
                    @foreach($this->immediateFailLabels as $key => $label)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-red-200/50 bg-white/50 dark:bg-slate-900/50 hover:bg-red-100 dark:hover:bg-red-900/30 transition-all cursor-pointer">
                            <flux:checkbox wire:model.live="immediate_fails.{{ $key }}" />
                            <span class="text-sm font-bold leading-tight pt-0.5">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. DRIVING SKILLS (Ratings 1-3) --}}
        <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <flux:heading size="lg" class="font-black uppercase tracking-tight text-slate-800 dark:text-slate-200 mb-2">3. Driving Skills</flux:heading>
            <flux:text size="xs" class="font-bold uppercase tracking-widest text-slate-400 block mb-6">1 = Poor / 2 = Fair / 3 = Good</flux:text>

            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-6 text-xs font-black uppercase text-slate-500 tracking-wider">Assessment Items</th>
                            <th class="py-3 px-6 text-center text-xs font-black uppercase text-red-500 tracking-wider w-20">1</th>
                            <th class="py-3 px-6 text-center text-xs font-black uppercase text-amber-500 tracking-wider w-20">2</th>
                            <th class="py-3 px-6 text-center text-xs font-black uppercase text-emerald-500 tracking-wider w-20">3</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($this->drivingSkillLabels as $key => $label)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-4 px-6 text-sm font-semibold text-slate-700 dark:text-slate-300 leading-tight">{{ $label }}</td>
                                @foreach([1, 2, 3] as $val)
                                    <td class="py-4 px-6 text-center">
                                        <input type="radio" wire:model.live="driving_skills.{{ $key }}" value="{{ $val }}" name="ds_{{ $key }}"
                                            class="size-6 cursor-pointer text-blue-600 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-700">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. OBSERVANCE TO TRAFFIC RULES (Ratings 1-3) --}}
        <div class="px-6 py-8 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <flux:heading size="lg" class="font-black uppercase tracking-tight text-slate-800 dark:text-slate-200 mb-2">4. Observance to Traffic Rules</flux:heading>
            <flux:text size="xs" class="font-bold uppercase tracking-widest text-slate-400 block mb-6">1 = Poor / 2 = Fair / 3 = Good</flux:text>

            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-indigo-50/50 dark:bg-indigo-900/10">
                            <th class="py-3 px-6 text-xs font-black uppercase text-indigo-500 tracking-wider">Traffic Rules Alignment</th>
                            <th class="py-3 px-6 text-center text-xs font-black uppercase text-red-500 tracking-wider w-20">1</th>
                            <th class="py-3 px-6 text-center text-xs font-black uppercase text-amber-500 tracking-wider w-20">2</th>
                            <th class="py-3 px-6 text-center text-xs font-black uppercase text-emerald-500 tracking-wider w-20">3</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($this->trafficRuleLabels as $key => $label)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-4 px-6 text-sm font-semibold text-slate-700 dark:text-slate-300 leading-tight">{{ $label }}</td>
                                @foreach([1, 2, 3] as $val)
                                    <td class="py-4 px-6 text-center">
                                        <input type="radio" wire:model.live="traffic_rules.{{ $key }}" value="{{ $val }}" name="tr_{{ $key }}"
                                            class="size-6 cursor-pointer text-indigo-600 focus:ring-indigo-500 dark:bg-slate-800 dark:border-slate-700">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- OVERALL ASSESSMENT --}}
        <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black {{ $total_poor_marks > 15 || $pre_drive_errors > 0 || $immediate_fail_count > 0 ? 'text-red-500' : 'text-blue-500' }}">{{ $total_poor_marks }}</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">/ 15 Poor Marks Allowed</span>
                </div>
            </div>
            
            <flux:heading size="lg" class="font-black uppercase tracking-tight text-slate-800 dark:text-slate-200 mb-6">4. Overall Assessment</flux:heading>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    @foreach([
                        '1' => 'Slow Learner',
                        '2' => 'Fast Learner',
                        '3' => 'Accompanied by Hesitancy',
                        '4' => 'Recommended for Additional Actual Driving'
                    ] as $val => $label)
                        <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-100 dark:border-slate-800 {{ $learner_type === $val ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : 'bg-slate-50/50 dark:bg-slate-800/30' }} transition-all cursor-pointer group">
                            <input type="radio" wire:model.live="learner_type" value="{{ $val }}" class="size-5 text-blue-600 border-slate-300">
                            <span class="text-sm font-bold {{ $learner_type === $val ? 'text-blue-700 dark:text-blue-300' : 'text-slate-600 dark:text-slate-400' }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="space-y-6">
                    <flux:field>
                        <flux:label class="font-black uppercase text-xs tracking-widest">5. Kilometers Driven (This Session)</flux:label>
                        <flux:input type="number" step="0.1" min="0" wire:model="session_kms_driven" placeholder="e.g. 5.5" />
                        <flux:error name="session_kms_driven" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-black uppercase text-xs tracking-widest">6. Remarks and Observations</flux:label>
                        <flux:textarea wire:model="instructor_remarks" placeholder="Enter session remarks..." rows="5" />
                    </flux:field>
                </div>
            </div>
        </div>

        {{-- FINAL SIGN-OFF (STYLIZED PREVIEW) --}}
        <div class="p-8 bg-white dark:bg-slate-900 rounded-3xl border-8 {{ $is_passed === true ? 'border-emerald-500 dark:border-emerald-600' : ($is_passed === false ? 'border-red-500' : 'border-slate-200 dark:border-slate-800') }} shadow-2xl transition-all duration-500">
            <div class="flex flex-col md:flex-row gap-8 items-center text-center md:text-left">
                <div class="flex-1">
                    <div class="inline-flex px-3 py-1 rounded-full {{ $is_passed === true ? 'bg-emerald-100 text-emerald-700' : ($is_passed === false ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500') }} text-xs font-black uppercase tracking-widest mb-4">
                        Current Result
                    </div>
                    <flux:heading size="xl" class="text-4xl font-black {{ $is_passed === true ? 'text-emerald-700 dark:text-emerald-400' : ($is_passed === false ? 'text-red-700' : 'text-slate-400') }}">
                        {{ $is_passed === true ? 'PASSED' : ($is_passed === false ? 'FAILED' : 'IN PROGRESS') }}
                    </flux:heading>
                    
                    <div class="mt-4 flex items-baseline gap-2">
                        <flux:text size="sm" class="font-bold text-slate-500 uppercase tracking-wider">Calculated Score:</flux:text>
                        <span class="text-2xl font-black {{ $this->finalScore >= 75 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $this->finalScore }}<span class="text-lg opacity-50">%</span>
                        </span>
                    </div>
                    
                    <flux:text class="mt-2 font-medium text-slate-500">Final result and score will be locked and recorded upon **Complete Assessment**.</flux:text>
                </div>

                <div class="w-full md:w-auto flex flex-col gap-3">
                    <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                         <button type="button" wire:click="$set('is_passed', true)" 
                            class="flex-1 px-8 py-3 rounded-xl font-black text-lg transition-all {{ $is_passed === true ? 'bg-emerald-500 text-white shadow-lg' : 'text-slate-500 opacity-50' }}">
                            PASS
                        </button>
                        <button type="button" wire:click="$set('is_passed', false)" 
                            class="flex-1 px-8 py-3 rounded-xl font-black text-lg transition-all {{ $is_passed === false ? 'bg-red-500 text-white shadow-lg' : 'text-slate-500 opacity-50' }}">
                            FAIL
                        </button>
                    </div>
                </div>
            </div>

            @if($is_passed === false)
                <div class="mt-8 p-4 bg-red-50 dark:bg-red-900/10 rounded-2xl border border-red-100 dark:border-red-900/20">
                    <flux:field>
                        <flux:label class="text-red-700 dark:text-red-400 font-bold">Reason for failure (Required)</flux:label>
                        <flux:textarea wire:model="failure_reason" placeholder="State the specific reason for failure..." />
                    </flux:field>
                </div>
            @endif

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Button 1: Partial Save (Day 1) --}}
                <div class="flex flex-col gap-2">
                    <flux:button variant="subtle"  class="h-20 rounded-2xl font-black uppercase tracking-widest text-lg border-2" wire:click="saveAndEndSession">
                        <flux:icon icon="arrow-right-start-on-rectangle" class="mr-2" />
                        Save & End Session
                    </flux:button>
                    <flux:text size="xs" class="text-center text-slate-500">Save progress and log {{ round($bookingSession->start_time->diffInMinutes(now()) / 60, 1) }}h for today.</flux:text>
                </div>

                {{-- Button 2: Final Completion (Day 2) --}}
                <div class="flex flex-col gap-2">
                    <flux:button variant="primary" class="h-20 rounded-2xl font-black uppercase tracking-widest text-lg shadow-xl shadow-blue-500/30" wire:click="completeAssessment">
                        <flux:icon icon="check-badge" class="mr-2" />
                        Complete Assessment
                    </flux:button>
                    <flux:text size="xs" class="text-center text-blue-600 font-bold uppercase tracking-tighter">Final sign-off with PASS/FAIL result</flux:text>
                </div>
            </div>
            
            <div class="mt-6 flex justify-center">
                <a href="{{ route('instructor.my-students') }}" wire:navigate class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors underline decoration-1 underline-offset-4">Discard changes and return to student list</a>
            </div>
        </div>
    </div>
</div>