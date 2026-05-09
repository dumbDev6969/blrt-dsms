<?php

use Livewire\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|email|max:255')]
    public string $email = '';

    public function subscribe(): void
    {
        $this->validate();

        // TODO: wire up to your mailing list / notification system
        // e.g. MailingList::subscribe($this->email);

        $this->email = '';
        session()->flash('footer_subscribed', true);
    }
};
?>

<div>
    {{-- Nothing worth having comes easy. - Theodore Roosevelt --}}
    <footer class="w-full bg-accent border-t border-white/10 pt-24 pb-12 rounded-2xl">
        <flux:container>
            {{-- Massive Brand Statement: White with low opacity --}}
            <div class="mb-20">
                <h2
                    class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tighter leading-none text-white opacity-20 select-none">
                    DRIVE BLRT.
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 mb-20">
                {{-- Brand & Newsletter --}}
                <div class="lg:col-span-5 space-y-8">
                    <div>
                        <flux:heading size="xl" class="mb-4 text-white">Brotherly Love Relief & Truth</flux:heading>
                        <flux:text class="max-w-sm text-base leading-relaxed text-white/80">
                            San Carlos City's most successful driving academy, dedicated to producing safety-conscious and
                            technically proficient drivers since 2021.
                        </flux:text>
                    </div>

                    <form wire:submit="subscribe" class="max-w-md space-y-4">
                        <flux:text size="sm" class="font-bold uppercase tracking-widest text-white/60">
                            Stay Updated
                        </flux:text>
                        <div class="flex gap-2">
                            {{-- Custom styled input to fit the solid background --}}
                            <flux:input wire:model="email" placeholder="email@example.com"
                                class="flex-1 text-white" />
                            <flux:button type="submit" variant="filled"
                                class="!bg-white !text-accent hover:!bg-zinc-100">
                                Join
                            </flux:button>
                        </div>
                    </form>
                </div>

                {{-- Links: Courses --}}
                <div class="lg:col-span-2 lg:col-start-7">
                    <flux:heading size="sm" class="mb-6 uppercase tracking-widest text-white/60">Programs
                    </flux:heading>
                    <nav class="flex flex-col gap-4">
                        <flux:link href="#" class="!text-white/80 hover:!text-white">Theoretical (TDC)</flux:link>
                        <flux:link href="#" class="!text-white/80 hover:!text-white">Practical (PDC)</flux:link>
                        <flux:link href="#" class="!text-white/80 hover:!text-white">License Assistance
                        </flux:link>
                        <flux:link href="#" class="!text-white/80 hover:!text-white">Fleet Gallery</flux:link>
                    </nav>
                </div>

                {{-- Links: Company --}}
                <div class="lg:col-span-2">
                    <flux:heading size="sm" class="mb-6 uppercase tracking-widest text-white/60">Academy
                    </flux:heading>
                    <nav class="flex flex-col gap-4">
                        <flux:link href="#" class="!text-white/80 hover:!text-white">About Us</flux:link>
                        <flux:link href="#" class="!text-white/80 hover:!text-white">Our Instructors</flux:link>
                        <flux:link href="#" class="!text-white/80 hover:!text-white">Locations</flux:link>
                        <flux:link href="#" class="!text-white/80 hover:!text-white">Careers</flux:link>
                    </nav>
                </div>

                {{-- Links: Social/Contact --}}
                <div class="lg:col-span-2">
                    <flux:heading size="sm" class="mb-6 uppercase tracking-widest text-white/60">Connect
                    </flux:heading>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-2 group">
                            <flux:icon name="phone" variant="mini" class="text-white/60 group-hover:text-white" />
                            <flux:text size="sm"
                                class="cursor-pointer text-white/80 group-hover:text-white transition-colors">
                                +63 912 345 6789
                            </flux:text>
                        </div>
                        <div class="flex items-center gap-2 group">
                            <flux:icon name="envelope" variant="mini" class="text-white/60 group-hover:text-white" />
                            <flux:text size="sm"
                                class="cursor-pointer text-white/80 group-hover:text-white transition-colors">
                                hello@blrtacademy.com
                            </flux:text>
                        </div>
                        <div class="pt-2">
                            <flux:button variant="ghost" size="sm" square aria-label="Facebook"
                                class="!text-white/80 hover:!text-accent hover:!bg-white">
                                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                                </svg>
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="pt-12 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-6">
                <flux:text size="sm" class="text-white/60">
                    &copy; {{ date('Y') }} BLRT Driving Academy. All rights reserved. LTO Accredited No. 12345-678.
                </flux:text>

                <div class="flex gap-8">
                    <flux:link href="#" size="sm" class="!text-white/60 hover:!text-white">Privacy Policy
                    </flux:link>
                    <flux:link href="#" size="sm" class="!text-white/60 hover:!text-white">Terms of Service
                    </flux:link>
                </div>
            </div>
        </flux:container>
    </footer>
</div>
