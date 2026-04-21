<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\InstructorPerformanceService;
use App\Models\InstructorProfile;
use Illuminate\Support\Facades\Log;

class InstructorPerformanceCard extends Component
{
    use WithPagination;

    public $instructorId;
    public $courseId;
    public $courseTitle;
    public $courseCode;
    public $courseType;
    public $profileUrl;
    public $perPage = 3;
    public $isExpanded = false;

    public function mount($instructor = null, $courseTitle = null, $courseCode = null, $courseType = 'TDC', $profileUrl = null, $courseId = null)
    {
        $this->instructorId = $instructor?->id;
        $this->courseId = $courseId;
        $this->courseTitle = $courseTitle;
        $this->courseCode = $courseCode;
        $this->courseType = $courseType;
        $this->profileUrl = $profileUrl;
    }

    public function toggleExpanded()
    {
        $this->isExpanded = !$this->isExpanded;
        if ($this->isExpanded) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $service = app(InstructorPerformanceService::class);
        
        $summary = $service->getSummary($this->instructorId, $this->courseId);
        $reviews = $this->isExpanded 
            ? $service->getReviews($this->instructorId, $this->courseId, $this->perPage)
            : null;

        $instructor = $this->instructorId ? InstructorProfile::with('user')->find($this->instructorId) : null;

        return view('livewire.instructor-performance-card', [
            'summary' => $summary,
            'reviews' => $reviews,
            'instructor' => $instructor,
        ]);
    }
}
