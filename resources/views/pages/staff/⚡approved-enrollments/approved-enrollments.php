<?php

use Livewire\Component;
use App\Models\Enrollment;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\InstructorProfile;
new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    #[Computed]
    public function enrollmentCounts()
    {
        return [
            'total' => Enrollment::count(),
            'pending' => Enrollment::where('status', 'pending')->count(),
            'active' => Enrollment::where('status', 'active')->count(),
            'completed' => Enrollment::where('status', 'completed')->count(),
            'dropped' => Enrollment::where('status', 'dropped')->count(),
        ];
    }

    #[Computed]
    public function enrollments()
    {
        return Enrollment::query()
        ->with(['studentProfile.user', 'instructorProfile.user', 'course'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                // Allow to search the student name
                    ->orWhereHas('studentProfile.user', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    // Allow to search the instructor name
                    ->orWhereHas('instructorProfile.user', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%');
                    })
                    // Allow to search the course/code
                    ->orWhereHas('course', function ($sub) {
                        $sub->where('title', 'like', '%' . $this->search . '%')
                            ->orWhere('code', 'like', '%' . $this->search . '%');
                    });
            });
        })
        ->when($this->status !== 'all', function ($query) {
            $query->where('status', $this->status);
        })
        ->latest()
        ->paginate(10);
    }
};
