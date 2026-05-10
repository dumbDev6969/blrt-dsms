<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use App\Models\LtoClinic;

new class extends Component {


    use WithFileUploads;
    #[Validate('required')]
    public $type = '';

    #[Validate('required|file|mimes:pdf,jpg,png|max:5120')] 
    public $attachments = '';

    #[Validate('nullable|array')]
    public $metadata = [];

    public function save()
    {
        // Get the validated date
        $validated = $this->validate();

        // If it's a medical document, fetch the clinic name from the selected ID for the metadata
        if ($validated['type'] === 'medical' && isset($validated['metadata']['clinic_id'])) {
            $clinic = LtoClinic::find($validated['metadata']['clinic_id']);
            if ($clinic) {
                $validated['metadata']['clinic_name'] = $clinic->clinic_name;
            }
        }

        // Path where the document will be stored
        $path = $this->attachments->store('private_documents', 'local');


        // Save to db
        Document::create([
            'user_id' => Auth::user()->id,
            'type' => $validated['type'],
            'status' => 'pending',
            'file_path' => $path,
            'metadata' => $validated['metadata']
        ]);

        // Reset form
        $this->reset();

        session()->flash('status', 'Document uploaded successfully.');
        
        // Redirect to student dashboard
        return $this->redirect(route('dashboard'), navigate: true);
    }

    #[Computed]
    public function availableDocumentTypes()
    {
        $profile = Auth::user()->studentProfile;
        
        $requiredTypes = ['medical', 'adl_form', 'valid_id'];
        
        if ($profile) {
            if ($profile->nationality === 'foreigner') {
                $requiredTypes[] = 'passport';
            } else {
                $requiredTypes[] = 'birth_cert';
            }

            if (strtolower($profile->civil_status) === 'married' && strtolower($profile->sex) === 'female') {
                $requiredTypes[] = 'marriage_contract';
            }
        } else {
            $requiredTypes[] = 'birth_cert'; // Default
        }

        $allAllowedTypes = array_unique(array_merge($requiredTypes, ['tin_id', 'tdc_certificate', 'marriage_contract']));
        $uploadedTypes = Document::where('user_id', Auth::user()->id)->pluck('type')->toArray();
        $availableTypes = array_diff($allAllowedTypes, $uploadedTypes);

        $typeLabels = [
            'birth_cert' => 'Birth Certificate',
            'medical' => 'Medical Certificate',
            'adl_form' => 'ADL Form',
            'valid_id' => 'Valid ID',
            'marriage_contract' => 'Marriage Contract',
            'tdc_certificate' => 'TDC Certificate',
            'tin_id' => 'TIN ID',
            'passport' => 'Passport',
        ];

        return array_intersect_key($typeLabels, array_flip($availableTypes));
    }

    #[Computed]
    public function clinics()
    {
        return LtoClinic::where('is_active', true)->get();
    }

};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        <div class="mb-8 text-center md:text-left">
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                Upload Documents
            </flux:heading>
            <flux:subheading class="mt-2 text-zinc-500 dark:text-zinc-400">
                Please provide the required documents to proceed with your enrollment.
            </flux:subheading>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form wire:submit.prevent="save" class="space-y-8">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div class="col-span-1 md:col-span-2">
                        <flux:select wire:model.live="type" label="Document Type" placeholder="Select document type..."
                            required>
                            @foreach ($this->availableDocumentTypes as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- DYNAMIC METADATA SECTION --}}

                    {{-- 1. Certificate / ID Number (Relevant for IDs, Licenses, Certs) --}}
                    @if (in_array($type, ['valid_id', 'tin_id', 'passport', 'medical', 'tdc_certificate', 'marriage_contract']))
                        <flux:input wire:model="metadata.cert_number" label="ID / Certificate Number"
                            placeholder="e.g. 123-456-789" />
                    @endif

                    
                    @if ($type === 'medical')
                        <div class="col-span-1 md:col-span-2">
                            <flux:select wire:model="metadata.clinic_id" label="Accredited Clinic" placeholder="Select a clinic..." required>
                                @foreach ($this->clinics as $clinic)
                                    <flux:select.option value="{{ $clinic->id }}">
                                        {{ $clinic->clinic_name }} ({{ $clinic->accreditation_number }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    
                    @if (in_array($type, ['passport', 'valid_id', 'medical']))
                        <flux:input wire:model="metadata.expiry" type="date" label="Expiry Date" />
                    @endif

                    @if (in_array($type, ['birth_cert', 'medical', 'passport', 'valid_id', 'tdc_certificate', 'marriage_contract']))
                        <flux:input wire:model="metadata.issue_date" type="date" label="Date Issued" />
                    @endif

                </div>

                <hr class="border-zinc-100 dark:border-zinc-800" />

                <div>
                    <flux:input type="file" wire:model="attachments" label="Document File"
                        description="Supported formats: PDF, JPG, PNG (Max 5MB)" accept=".pdf,.jpg,.jpeg,.png" />

                    {{-- Show preview only if file is uploaded --}}
                    @if ($attachments)
                        <div class="mt-3 text-sm text-green-600 dark:text-green-400 flex items-center gap-2">
                            <flux:icon.check-circle class="size-4" />
                            <span>File ready for upload</span>
                        </div>
                    @endif

                    @if ($attachments)
                        <img class="mt-3 w-full h-auto rounded-lg border border-zinc-200 dark:border-zinc-800" src="{{ $attachments->temporaryUrl() }}" alt="Document preview">
                    @endif
                </div>

                <div class="flex items-center justify-end pt-4">
                    <flux:button variant="primary" type="submit" class="w-full md:w-auto">
                        Submit Document
                    </flux:button>
                </div>
            </form>
        </div>

    </div>
</div>
