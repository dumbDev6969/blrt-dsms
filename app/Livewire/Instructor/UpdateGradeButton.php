<?php

namespace App\Livewire\Instructor;

use Livewire\Component;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use App\Services\InstructorGradingService;

class UpdateGradeButton extends Component
{
    public Enrollment $enrollment;
    public $grade;
    public $result;
    public $remarks;

    public function mount(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
        $this->grade = $enrollment->final_grade;
        $this->result = $enrollment->final_result;
        $this->remarks = $enrollment->remarks;
    }

    public function submitGrade()
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $this->validate([
            'grade' => 'nullable|numeric|min:0|max:100',
            'result' => 'required|in:pass,fail',
            'remarks' => 'nullable|string',
        ]);

        app(InstructorGradingService::class)->submitGrade($this->enrollment->id, [
            'grade' => $this->grade,
            'result' => $this->result,
            'remarks' => $this->remarks,
        ]);

        $this->enrollment->refresh();
        session()->flash('success', 'Student grade submitted successfully.');

        // Dispatch event so parent components (like view-student) can refresh if needed
        $this->dispatch('grade-updated');

        \Flux\Flux::modal('score-' . $this->enrollment->id)->close();
    }

    public function render()
    {
        return view('livewire.instructor.update-grade-button');
    }
}
