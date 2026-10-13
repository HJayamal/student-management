<div class="modern-card">
    <div class="modern-card-header list-header">
        <div>
            <h3 class="modern-card-title">
                <i class="bi bi-people me-2"></i>
                Student List
            </h3>
            <small>Registered students</small>
        </div>
        <span style="background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; border-radius: 999px; padding: 8px 13px; font-size: 12px; font-weight: 600; white-space: nowrap;">
            {{ $students->total() }} Students
        </span>
    </div>

    <div class="modern-card-body">
        <div class="search-box">
            <label for="studentSearch" class="form-label">Search by Register No or Name</label>
            <input
                type="search"
                id="studentSearch"
                wire:model.live.debounce.300ms="search"
                class="form-control"
                placeholder="Enter registration number or student name">
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <label for="studentsPerPage" class="mb-0">Show</label>
                <select id="studentsPerPage" wire:model.live="perPage" class="form-select" style="width: auto; min-width: 80px;">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entries</span>
            </div>
            <small class="text-muted">
                Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries
            </small>
        </div>

        <div class="table-responsive student-table">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Image</th>
                    <th wire:click="sortBy('reg_no')" role="button">Reg No @if($sortField === 'reg_no') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th wire:click="sortBy('name')" role="button">Name @if($sortField === 'name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th wire:click="sortBy('gender')" role="button">Gender @if($sortField === 'gender') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th wire:click="sortBy('nic')" role="button">NIC @if($sortField === 'nic') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th wire:click="sortBy('dob')" role="button">DOB @if($sortField === 'dob') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th wire:click="sortBy('email')" role="button">Email @if($sortField === 'email') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th wire:click="sortBy('phone')" role="button">Phone @if($sortField === 'phone') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif</th>
                    <th class="action-column">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="text-center">
                            @if ($student->image)
                                <img src="{{ asset('storage/' . $student->image) }}" alt="Student Image" class="student-avatar">
                            @else
                                <div class="no-image">No Image</div>
                            @endif
                        </td>
                        <td><span class="fw-semibold">{{ $student->reg_no }}</span></td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->gender ?: '-' }}</td>
                        <td>{{ $student->nic ?: '-' }}</td>
                        <td>{{ $student->dob }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone }}</td>
                        <td class="action-cell">
                            <a href="{{ route('students.edit', $student) }}" class="btn btn-warning btn-sm action-btn">
                                <i class="bi bi-pencil-square"></i> Update
                            </a>
                            <form action="{{ route('students.destroy', $student) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm action-btn" onclick="return confirm('Delete this student?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center empty-state">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            <div class="empty-state-title">No students found</div>
                            <small>Register a student or change your search.</small>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $students->links() }}
        </div>
    </div>
</div>
