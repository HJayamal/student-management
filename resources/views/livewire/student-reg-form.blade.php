<div class="modern-card">
    <div class="modern-card-header register-header">
        <div>
            <h3 class="modern-card-title"><i class="bi bi-person-plus me-2"></i>Student Register</h3>
            <small>Add a new student</small>
        </div>
    </div>

    <div class="modern-card-body">
        @if (session()->has('success'))
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Please fix the following:</div>
                @foreach ($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form wire:submit="submit">
            <div class="mb-3">
                <label class="form-label">Register No</label>
                <input type="text" wire:model="reg_no" class="form-control" placeholder="Enter register number">
                @error('reg_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" wire:model="first_name" class="form-control" placeholder="Enter first name">
                    @error('first_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" wire:model="last_name" class="form-control" placeholder="Enter last name">
                    @error('last_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select wire:model="gender" class="form-select">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                @error('gender') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">NIC</label>
                <input type="text" wire:model="nic" class="form-control" placeholder="Enter NIC number">
                @error('nic') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" wire:model="email" class="form-control" placeholder="student@example.com">
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Phone No</label>
                <input type="text" wire:model="phone" class="form-control" placeholder="Enter phone number">
                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" wire:model="dob" class="form-control">
                @error('dob') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" wire:model="password" class="form-control" placeholder="Minimum 6 characters">
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Student Image</label>
                <input type="file" wire:model="image" class="form-control" accept=".jpg,.jpeg,.png">
                <small class="text-muted">JPG, JPEG or PNG (max 2 MB)</small>
                @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div wire:loading wire:target="image" class="text-primary small mt-1">Uploading image...</div>
                @if ($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" alt="Image Preview" style="width:90px;height:90px;object-fit:cover;border-radius:10px;">
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea wire:model="address" class="form-control" rows="3" placeholder="Enter student address"></textarea>
                @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="register-btn" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit"><i class="bi bi-person-plus me-2"></i>Register Student</span>
                <span wire:loading wire:target="submit">Saving...</span>
            </button>
        </form>
    </div>
</div>
