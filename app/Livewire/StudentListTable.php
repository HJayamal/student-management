<?php

namespace App\Livewire;

use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class StudentListTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowedFields = [
            'reg_no',
            'name',
            'gender',
            'nic',
            'dob',
            'email',
            'phone',
        ];

        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection =
                $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[On('student-created')]
    public function refreshStudents(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $student = Student::findOrFail($id);

        $imagePath = $student->image;


        $student->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $this->resetPage();

        session()->flash(
            'success',
            'Student deleted successfully.'
        );
    }

    public function render()
    {
        $query = Student::query();

        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($builder) use ($search) {
                $builder->where('reg_no', 'like', $search)
                    ->orWhere('name', 'like', $search);
            });
        }

        $allowedPageSizes = [5, 10, 25, 50, 100];
        $perPage = (int) $this->perPage;

        if (! in_array($perPage, $allowedPageSizes, true)) {
            $perPage = 10;
        }

        $students = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderByDesc('id')
            ->paginate($perPage);

        return view('livewire.student-list-table', [
            'students' => $students,
        ]);
    }
}
