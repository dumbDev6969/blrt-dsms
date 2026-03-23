<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\User;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public Document $document;
    public User $user;
    public $selectedDocumentId;

    public function mount(Document $document)
    {
        $this->document = $document;
        $this->user = $document->user;
        $this->selectedDocumentId = $document->id;
    }

    #[Computed]
    public function selectedDocument()
    {
        return Document::find($this->selectedDocumentId);
    }

    #[Computed]
    public function userDocuments()
    {
        return $this->user->documents()->latest()->get();
    }

    public function selectDocument($id)
    {
        $this->selectedDocumentId = $id;
    }

    // Reject Document
    public function reject(Document $document)
    {
        $document->update([
            'status' => 'rejected',
        ]);

        session()->flash('status', 'Document rejected successfully.');
    }

    // Accept Document
    public function verify(Document $document)
    {
        $document->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
        ]);

        session()->flash('status', 'Document verified successfully.');
    }

    // Back to Pending
    public function backToPending(Document $document)
    {
        $document->update([
            'status' => 'pending',
            'verified_by' => null,
        ]);

        session()->flash('status', 'Document moved back to pending.');
    }
};
?>

<div class="flex flex-col gap-6 p-1">
    {{-- Header / Breadcrumbs --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:button icon="chevron-left" variant="ghost" size="sm" :href="url()->previous()" />
            <div>
                <flux:heading size="xl" class="font-bold">Review Document</flux:heading>
                <flux:text>Reviewing submissions from <span
                        class="font-medium text-slate-900 dark:text-white">{{ $user->name }}</span></flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($this->selectedDocument->status === 'pending')
                <flux:button variant="danger" icon="x-mark" wire:click="reject({{ $this->selectedDocument->id }})">
                    Reject</flux:button>
                <flux:button variant="primary" icon="check" wire:click="verify({{ $this->selectedDocument->id }})">
                    Verify Document</flux:button>
            @elseif($this->selectedDocument->status === 'verified')
                <flux:badge color="emerald" icon="check-circle">Verified</flux:badge>
                <flux:button variant="ghost" size="sm"
                    wire:click="backToPending({{ $this->selectedDocument->id }})">Back to Pending</flux:button>
            @else
                <flux:badge color="red" icon="exclamation-triangle">Rejected</flux:badge>
                <flux:button variant="ghost" size="sm"
                    wire:click="backToPending({{ $this->selectedDocument->id }})">Back to Pending</flux:button>
            @endif
        </div>
    </div>

    <x-callout />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Sidebar: User Info & Document List --}}
        <div class="lg:col-span-4 flex flex-col gap-6">
            {{-- User Profile Card --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="flex items-center justify-center size-12 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 text-lg font-bold">
                        {{ $user->initials() }}
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $user->name }}</flux:heading>
                        <flux:text size="sm">{{ $user->email }}</flux:text>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contact
                            Number</span>
                        <span class="text-sm font-medium">{{ $user->studentProfile->contact_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">LTMS Client
                            ID</span>
                        <span class="text-sm font-medium">{{ $user->studentProfile->ltms_client_id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</span>
                        <span class="text-sm">{{ $user->studentProfile->address ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Documents List --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <flux:heading size="sm" class="font-bold">Submitted Documents</flux:heading>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($this->userDocuments as $doc)
                        <button wire:click="selectDocument({{ $doc->id }})"
                            class="w-full p-4 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex items-center justify-between {{ $selectedDocumentId === $doc->id ? 'bg-blue-50/50 dark:bg-blue-900/10 border-l-4 border-blue-500' : '' }}">
                            <div class="flex flex-col">
                                <span
                                    class="text-sm font-medium {{ $selectedDocumentId === $doc->id ? 'text-blue-600 dark:text-blue-400' : '' }}">
                                    {{ str($doc->type)->headline() }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    Uploaded {{ $doc->created_at->diffForHumans() }}
                                </span>
                            </div>

                            @if ($doc->status === 'verified')
                                <flux:icon icon="check-circle" class="size-4 text-emerald-500" variant="solid" />
                            @elseif($doc->status === 'rejected')
                                <flux:icon icon="exclamation-triangle" class="size-4 text-red-500" variant="solid" />
                            @else
                                <flux:icon icon="clock" class="size-4 text-amber-500" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Metadata Info --}}
            @if ($this->selectedDocument->metadata)
                <div
                    class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                    <flux:heading size="sm" class="mb-4 font-bold">Document Details</flux:heading>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($this->selectedDocument->metadata as $key => $value)
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ str($key)->headline() }}</span>
                                <span class="text-sm font-medium">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Main: Document Preview --}}
        <div class="lg:col-span-8">
            <div
                class="h-full min-h-[600px] flex flex-col rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div
                    class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="eye" class="size-4 text-slate-400" />
                        <flux:heading size="sm" class="font-bold">Preview:
                            {{ str($this->selectedDocument->type)->headline() }}</flux:heading>
                    </div>
                    <flux:button size="xs" variant="ghost" icon="arrow-down-tray"
                        :href="route('admin.document.serve', $this->selectedDocument->id)" target="_blank">
                        Open Original
                    </flux:button>
                </div>

                <div class="flex-1 bg-slate-100 dark:bg-slate-950 flex items-center justify-center p-4 relative">
                    {{-- Loading state --}}
                    <div wire:loading wire:target="selectDocument"
                        class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 z-10">
                        <flux:icon icon="arrow-path" class="size-8 text-blue-500 animate-spin" />
                    </div>

                    @php
                        $extension = pathinfo($this->selectedDocument->file_path, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp']);
                        $isPdf = strtolower($extension) === 'pdf';
                    @endphp

                    @if ($isImage)
                        <img src="{{ route('admin.document.serve', $this->selectedDocument->id) }}"
                            alt="{{ $this->selectedDocument->type }}"
                            class="max-w-full max-h-[700px] rounded-lg shadow-lg object-contain" />
                    @elseif($isPdf)
                        <iframe src="{{ route('admin.document.serve', $this->selectedDocument->id) }}"
                            class="w-full h-full min-h-[700px] rounded-lg shadow-sm border-0"></iframe>
                    @else
                        <div class="text-center p-8">
                            <flux:icon icon="document-text" class="size-16 text-slate-300 mx-auto mb-4" />
                            <flux:text>This file type cannot be previewed. Please download it to view.</flux:text>
                            <flux:button class="mt-4" icon="arrow-down-tray"
                                :href="route('admin.document.serve', $this->selectedDocument->id)" target="_blank">
                                Download File
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
