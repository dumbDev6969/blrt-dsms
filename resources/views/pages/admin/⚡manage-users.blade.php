<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\User;
use Spatie\Permission\Models\Role;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $role = 'all';

    // Change Role
    public ?int $selectedUserId = null;
    public string $newRole = '';

    // Remove Role
    public ?int $removeRoleUserId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    #[Computed]
    public function roleCounts()
    {
        // Staff role may not exist yet, so we catch the exception
        try {
            $staffCount = User::role('Staff')->count();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            $staffCount = 0;
        }

        return [
            'total' => User::count(),
            'admin' => User::role('Admin')->count(),
            'instructor' => User::role('Instructor')->count(),
            'student' => User::role('Student')->count(),
            'staff' => $staffCount,
        ];
    }

    #[Computed]
    public function users()
    {
        $query = User::query()->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
            });
        });

        if ($this->role !== 'all') {
            try {
                $query->role(ucfirst($this->role));
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                $query->whereRaw('0 = 1');
            }
        }

        return $query->latest()->paginate(10);
    }

    public function selectUserForRoleChange(int $userId)
    {
        $this->selectedUserId = $userId;
        $user = User::find($userId);
        $this->newRole = $user?->getRoleNames()->first() ?? '';
    }

    public function changeRole()
    {
        $this->validate([
            'newRole' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $user->syncRoles([$this->newRole]);

        session()->flash('status', 'Role updated to ' . $this->newRole . ' for ' . $user->name . '.');
        $this->reset(['selectedUserId', 'newRole']);

        \Flux::modal('change-role')->close();
    }

    public function selectUserForRoleRemoval(int $userId)
    {
        $this->removeRoleUserId = $userId;
    }

    public function removeRole()
    {
        $user = User::findOrFail($this->removeRoleUserId);
        $user->syncRoles([]);

        session()->flash('status', 'All roles removed from ' . $user->name . '.');
        $this->reset(['removeRoleUserId']);

        \Flux::modal('confirm-remove-role')->close();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Manage Users</flux:heading>
            <flux:text>
                View registered users and manage their roles and permissions.
            </flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        {{-- Total Users --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Users</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="users" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->roleCounts['total'] }}</flux:heading>
                <flux:text>total</flux:text>
            </div>
        </div>

        {{-- Admins --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Admins</span>
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg dark:bg-indigo-900/20 dark:text-indigo-400">
                    <flux:icon icon="shield-check" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->roleCounts['admin'] }}</flux:heading>
                <flux:text>active</flux:text>
            </div>
        </div>

        {{-- Staff --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Staff</span>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg dark:bg-purple-900/20 dark:text-purple-400">
                    <flux:icon icon="identification" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->roleCounts['staff'] }}</flux:heading>
                <flux:text>active</flux:text>
            </div>
        </div>

        {{-- Instructors --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Instructors</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->roleCounts['instructor'] }}</flux:heading>
                <flux:text>active</flux:text>
            </div>
        </div>

        {{-- Students --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Students</span>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->roleCounts['student'] }}</flux:heading>
                <flux:text>active</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-5">

        {{-- Filter Tabs & Search --}}
        <div
            class="flex flex-col md:flex-row md:items-center justify-between p-1 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg w-full gap-2 md:gap-0">
            <div class="flex flex-wrap gap-1 p-1">
                <button wire:click="$set('role', 'all')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $role === 'all' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    All
                </button>
                <button wire:click="$set('role', 'admin')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $role === 'admin' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Admin
                </button>
                <button wire:click="$set('role', 'instructor')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $role === 'instructor' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Instructor
                </button>
                <button wire:click="$set('role', 'student')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $role === 'student' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Student
                </button>
                <button wire:click="$set('role', 'staff')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $role === 'staff' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Staff
                </button>
            </div>
            <div class="pr-1 w-full md:w-72">
                <x-live-search placeholder="Search users by name or email..." />
            </div>
        </div>

        {{-- Users List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div
                class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">User Directory</flux:heading>
                    <flux:text size="sm" class="mt-1">A complete list of all users in your system.</flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->users->total() }} Total
                </flux:badge>
            </div>

            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">User</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Role</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Joined</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse ($this->users as $user)
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-xs font-bold">
                                            {{ $user->initials() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                            <span class="text-xs text-zinc-500">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $userRole = $user->getRoleNames()->first();
                                        $roleColors = [
                                            'Admin' =>
                                                'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/50',
                                            'Instructor' =>
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
                                            'Student' =>
                                                'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800/50',
                                            'Staff' =>
                                                'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200 dark:border-purple-800/50',
                                        ];
                                        $colorClass =
                                            $roleColors[$userRole] ??
                                            'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700';
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium border {{ $colorClass }}">
                                        {{ $userRole ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-zinc-700 dark:text-zinc-300">
                                            {{ $user->created_at->format('M d, Y') }}
                                        </span>
                                        <span class="text-xs text-zinc-500">
                                            {{ $user->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            <flux:modal.trigger name="change-role">
                                                <flux:menu.item icon="shield-check"
                                                    wire:click="selectUserForRoleChange({{ $user->id }})">Change
                                                    Role</flux:menu.item>
                                            </flux:modal.trigger>
                                            <flux:menu.separator />
                                            <flux:modal.trigger name="confirm-remove-role">
                                                <flux:menu.item icon="minus-circle" variant="danger"
                                                    wire:click="selectUserForRoleRemoval({{ $user->id }})">Remove
                                                    Role</flux:menu.item>
                                            </flux:modal.trigger>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="py-12 text-center animate-in fade-in zoom-in-95 duration-300">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div
                                            class="flex items-center justify-center size-10 rounded-full bg-zinc-100/50 dark:bg-zinc-800/50 border border-zinc-200/50 dark:border-zinc-700/50 mb-3 shadow-sm">
                                            <flux:icon name="users"
                                                class="size-5 text-zinc-400 dark:text-zinc-500" />
                                        </div>
                                        <flux:heading>No users found</flux:heading>
                                        <flux:text class="mt-1 text-xs max-w-xs mx-auto">
                                            No users match the current filters or search criteria.
                                        </flux:text>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($this->users->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Change Role Modal --}}
    <flux:modal name="change-role" class="min-w-[22rem]">
        <form wire:submit.prevent="changeRole">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Change User Role</flux:heading>
                    <flux:text class="mt-2">
                        You're about to change this user's role.<br>
                        This will adjust their access and permissions.
                    </flux:text>
                </div>

                <flux:select wire:model="newRole" label="Role" placeholder="Select role..." required>
                    <flux:select.option value="Admin">Admin</flux:select.option>
                    <flux:select.option value="Instructor">Instructor</flux:select.option>
                    <flux:select.option value="Student">Student</flux:select.option>
                    <flux:select.option value="Staff">Staff</flux:select.option>
                </flux:select>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Update Role</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Confirm Remove Role Modal --}}
    <flux:modal name="confirm-remove-role" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove role?</flux:heading>
                <flux:text class="mt-2">
                    You're about to remove this user's role.<br>
                    They will lose all role-based permissions.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="removeRole" variant="danger">Remove Role</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
