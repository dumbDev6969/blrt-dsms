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

            {{-- Guest Menu (Home, Services, About) --}}
            @guest
                <flux:navbar.item icon="home" href="/" :current="request()->is('/')" wire:navigate>
                    {{ __('Home') }}
                </flux:navbar.item>

                <flux:navbar.item icon="briefcase" href="/services" :current="request()->is('services')" wire:navigate>
                    {{ __('Services') }}
                </flux:navbar.item>

                <flux:navbar.item icon="information-circle" href="/about-us" :current="request()->is('about-us')"
                    wire:navigate>
                    {{ __('About Us') }}
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

        <x-desktop-user-menu />


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
                <flux:sidebar.group :heading="__('Menu')">
                    <flux:sidebar.item icon="home" href="/" :current="request()->is('/')" wire:navigate>
                        {{ __('Home') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="briefcase" href="/services" :current="request()->is('services')"
                        wire:navigate>
                        {{ __('Services') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="information-circle" href="/about-us" :current="request()->is('about-us')"
                        wire:navigate>
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
