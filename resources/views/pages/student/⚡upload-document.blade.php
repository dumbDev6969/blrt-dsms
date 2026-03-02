<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Document;
new class extends Component {

    // TODO: Remove the document in the select tag if the user already uploaded it

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
                            <flux:select.option value="birth_cert">Birth Certificate</flux:select.option>
                            <flux:select.option value="medical">Medical Certificate</flux:select.option>
                            <flux:select.option value="adl_form">ADL Form</flux:select.option>
                            <flux:select.option value="valid_id">Valid ID</flux:select.option>
                            <flux:select.option value="marriage_contract">Marriage Contract</flux:select.option>
                            <flux:select.option value="tdc_certificate">TDC Certificate</flux:select.option>
                            <flux:select.option value="tin_id">TIN ID</flux:select.option>
                            <flux:select.option value="passport">Passport</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- DYNAMIC METADATA SECTION --}}

                    {{-- 1. Certificate / ID Number (Relevant for IDs, Licenses, Certs) --}}
                    @if (in_array($type, ['valid_id', 'tin_id', 'passport', 'tdc_certificate', 'medical', 'marriage_contract']))
                        <flux:input wire:model="metadata.cert_number" label="ID / Certificate Number"
                            placeholder="e.g. 123-456-789" />
                    @endif

                    
                    @if ($type === 'medical')
                        <div class="col-span-1 md:col-span-2">
                            <flux:input wire:model="metadata.clinic_name" label="Clinic Name"
                                placeholder="Name of clinic or hospital" />
                        </div>
                    @endif

                    
                    @if (in_array($type, ['passport', 'valid_id', 'medical', 'tdc_certificate']))
                        <flux:input wire:model="metadata.expiry" type="date" label="Expiry Date" />
                    @endif

                    
                    @if (in_array($type, ['birth_cert', 'marriage_contract', 'medical', 'passport', 'valid_id']))
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

        <p class="mt-6 text-center text-xs text-zinc-400">
            Your data is processed securely. Need help? <a href="#" class="text-accent hover:underline">Contact
                Support</a>
        </p>
    </div>
</div>
        </p>
    </div>
</div>
