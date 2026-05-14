<?php

use Livewire\Component;
use App\Models\Enrollment;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\InstructorProfile;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';

    // Edit modal properties
    public $editingEnrollmentId = null;
    public $editInstructorId = '';
    public $editStartDate = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function openEditModal($enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->editingEnrollmentId = $enrollment->id;
        $this->editInstructorId = $enrollment->instructor_id ?? '';
        $this->editStartDate = $enrollment->start_date?->format('Y-m-d') ?? '';

        Flux::modal('edit-enrollment-modal')->show();
    }

    public function saveEnrollment()
    {
        $this->validate([
            'editInstructorId' => 'nullable|exists:instructor_profiles,id',
            'editStartDate' => 'nullable|date',
        ]);

        $enrollment = Enrollment::findOrFail($this->editingEnrollmentId);
        $enrollment->update([
            'instructor_id' => $this->editInstructorId ?: null,
            'start_date' => $this->editStartDate ?: null,
        ]);

        Flux::modal('edit-enrollment-modal')->close();

        $this->reset(['editingEnrollmentId', 'editInstructorId', 'editStartDate']);

        session()->flash('status', 'Enrollment updated successfully.');
    }

    #[Computed]
    public function instructors()
    {
        return InstructorProfile::with('user')
            ->whereHas('user')
            ->get();
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
