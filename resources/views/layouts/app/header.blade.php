<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="fixed top-0 left-0 right-0 z-20 backdrop-blur-md" >
        <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            {{-- Authenticated User Menu --}}
            @auth
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            @endauth

            {{-- Guest Menu (Home) --}}
            @guest
                <flux:navbar.item class="hidden xl:flex !bg-transparent !hover:bg-transparent mr-4 pointer-events-none">
                    <div class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full border border-blue-500/20 bg-blue-500/5 dark:bg-blue-400/10 shadow-sm">
                        <div class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-500"></span>
                        </div>
                        <span class="text-[11px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-[0.15em]">
                            Calm Learning, Calm Driving.
                        </span>
                    </div>
                </flux:navbar.item>

            
            @endguest
        </flux:navbar>

        <flux:spacer />

        <div class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            @guest
                <flux:button href="{{ route('login') }}" variant="ghost" wire:navigate>
                    {{ __('Log in') }}
                </flux:button>

                <flux:button href="{{ route('register') }}" variant="primary" wire:navigate>
                    {{ __('Register') }}
                </flux:button>
            @endguest
        </div>
        
        @auth
            <x-desktop-user-menu />
        @endauth
        


    </flux:header>

    <flux:sidebar collapsible="mobile" sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            {{-- Authenticated Mobile Menu --}}
            @auth
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endauth

            {{-- Guest Mobile Menu --}}
            @guest
                <div class="px-2 py-4 mb-2">
                    <div class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl border border-blue-500/20 bg-blue-500/5 dark:bg-blue-400/10">
                        <div class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-500"></span>
                        </div>
                        <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-[0.15em]">
                            Calm Learning, Calm Driving.
                        </span>
                    </div>
                </div>

                <flux:sidebar.group :heading="__('Menu')">
                    <flux:sidebar.item icon="home" href="{{ route('home') }}" :current="request()->is('/')" wire:navigate>
                        {{ __('Home') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="sparkles" href="/#courses" wire:navigate>
                        {{ __('Explore Courses') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="map" href="/#process" wire:navigate>
                        {{ __('How it Works') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="briefcase" href="{{ route('guest.services') }}" :current="request()->routeIs('guest.services')" wire:navigate>
                        {{ __('Services') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="information-circle" href="{{ route('guest.about') }}" :current="request()->routeIs('guest.about')" wire:navigate>
                        {{ __('About Us') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endguest
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            @guest
                <flux:sidebar.group>
                    <flux:sidebar.item href="{{ route('login') }}" wire:navigate>
                        {{ __('Log in') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item href="{{ route('register') }}" wire:navigate>
                        {{ __('Register') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endguest
        </flux:sidebar.nav>
    </flux:sidebar>

    {{ $slot }}

    @fluxScripts
    
    <flux:footer>
        <livewire:footer />
    </flux:footer>
</body>

</html>
