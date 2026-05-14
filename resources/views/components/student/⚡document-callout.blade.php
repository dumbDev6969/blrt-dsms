<?php

use Livewire\Component;
use App\Models\Document;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    // Check if the student uploaded at least one document
    #[Computed]
    public function hasDocument()
    {
        return Document::where('user_id', Auth::user()->id)->exists();
    }

    #[Computed]
    public function requiredDocumentTypes()
    {
        $profile = Auth::user()->studentProfile;

        $types = ['medical', 'adl_form', 'valid_id'];

        if ($profile) {
            if ($profile->nationality === 'foreigner') {
                $types[] = 'passport';
            } else {
                $types[] = 'birth_cert';
            }

            if (strtolower($profile->sex) === 'female' && strtolower($profile->civil_status) === 'married') {
                $types[] = 'marriage_contract';
            }
        } else {
            $types[] = 'birth_cert'; // Default
        }

        return $types;
    }

    #[Computed]
    public function isComplete()
    {
        $requiredTypes = $this->requiredDocumentTypes;
        
        $verifiedCount = Document::where('user_id', Auth::user()->id)
            ->whereIn('type', $requiredTypes)
            ->where('status', 'verified')
            ->distinct('type')
            ->count('type');
            
        return count($requiredTypes) > 0 && $verifiedCount >= count($requiredTypes);
    }
};
?>
<div>
@if (!$this->isComplete)
<flux:callout icon="exclamation-triangle" variant="warning" class="w-full">
    @if ($this->hasDocument)
        {{-- STATE 2: INCOMPLETE (User has started, but not finished) --}}
        <flux:callout.heading class="text-yellow-600">
            Your documents are incomplete
        </flux:callout.heading>
        <flux:callout.text>
            Please upload the remaining documents and wait for verification to unlock Practical Driving Courses (PDC).
        </flux:callout.text>
    @else
        {{-- STATE 3: EMPTY (User hasn't started) --}}
        <flux:callout.heading>
            You haven't uploaded documents yet
        </flux:callout.heading>
        <flux:callout.text>
            Upload your documents to unlock Practical Driving Courses (PDC). You can still enroll in Theoretical courses (TDC) while waiting.
        </flux:callout.text>
    @endif

    <x-slot name="actions">
        <flux:button size="sm" href="{{ route('document.upload') }}" wire:navigate>
            Upload Documents
        </flux:button>
    </x-slot>
</flux:callout>
@endif
</div>