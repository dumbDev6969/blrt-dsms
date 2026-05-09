<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>
            
            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                {{-- Academic Section --}}
                @php
                    $isAcademicVisible = auth()->user()->can('user.view') || auth()->user()->can('enrollment.review') || auth()->user()->can('instructor.view_own') || auth()->user()->can('student.view_own');
                @endphp

                @if($isAcademicVisible)
                    <flux:sidebar.group heading="{{ __('Academic') }}" class="mt-4">
                        {{-- Admin Academic --}}
                        @can('user.view')
                            <flux:sidebar.item icon="academic-cap" :href="route('admin.manage-courses')" :current="request()->routeIs('admin.manage-courses')" wire:navigate>
                                {{ __('Manage courses') }}
                            </flux:sidebar.item>
                        @endcan

                        {{-- Staff Academic --}}
                        @can('enrollment.review')
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('staff.manage-enrollments')" :current="request()->routeIs('staff.manage-enrollments')" wire:navigate>
                                {{ __('Manage enrollments') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="check-badge" :href="route('staff.approved-enrollments')" :current="request()->routeIs('staff.approved-enrollments')" wire:navigate>
                                {{ __('Approved enrollments') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="clock" :href="route('staff.waiting-list')" :current="request()->routeIs('staff.waiting-list')" wire:navigate>
                                {{ __('Waiting list') }}
                            </flux:sidebar.item>
                        @endcan

                        {{-- Instructor Academic --}}
                        @can('instructor.view_own')
                            <flux:sidebar.item icon="calendar" :href="route('instructor.my-schedule')" :current="request()->routeIs('instructor.my-schedule')" wire:navigate>
                                {{ __('My schedule') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="users" :href="route('instructor.my-students')" :current="request()->routeIs('instructor.my-students')" wire:navigate>
                                {{ __('My students') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="star" :href="route('instructor.performance-reviews')" :current="request()->routeIs('instructor.performance-reviews')" wire:navigate>
                                {{ __('Performance reviews') }}
                            </flux:sidebar.item>
                        @endcan

                        {{-- Student Academic --}}
                        @can('student.view_own')
                            <flux:sidebar.item icon="calendar" :href="route('student.my-schedule')" :current="request()->routeIs('student.my-schedule')" wire:navigate>
                                {{ __('My schedule') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="academic-cap" :href="route('student.academic-records')" :current="request()->routeIs('student.academic-records')" wire:navigate>
                                {{ __('Academic records') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif

                {{-- Management Section --}}
                @can('user.view')
                    <flux:sidebar.group heading="{{ __('Management') }}" class="mt-4">
                        <flux:sidebar.item icon="user-plus" :href="route('admin.pending-registrations')" :current="request()->routeIs('admin.pending-registrations')" wire:navigate>
                            {{ __('Pending registrations') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="truck" :href="route('admin.manage-vehicle')" :current="request()->routeIs('admin.manage-vehicle')" wire:navigate>
                            {{ __('Manage vehicles') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="star" :href="route('admin.instructor-performances')" :current="request()->routeIs('admin.instructor-performances')" wire:navigate>
                            {{ __('Instructor performances') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="user-group" :href="route('admin.manage-users')" :current="request()->routeIs('admin.manage-users')" wire:navigate>
                            {{ __('Manage users') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="building-office-2" :href="route('admin.accredited-clinics')" :current="request()->routeIs('admin.accredited-clinics')" wire:navigate>
                            {{ __('Accredited Clinics') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan
            </flux:sidebar.nav>

            <flux:spacer />

                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            
            
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        @if(auth()->user()->instructorProfile?->isPending())
            <div class="px-6 py-4">
                <flux:callout variant="warning" icon="clock">
                    <flux:callout.heading>Your instructor profile is pending approval</flux:callout.heading>
                    You cannot perform any actions until your profile has been reviewed and approved by the administrator.
                </flux:callout>
            </div>
        @endif

        {{ $slot }}

        {{-- PWA install-to-homescreen prompt --}}
        <x-pwa-install-prompt />

        @fluxScripts
    </body>
</html>
