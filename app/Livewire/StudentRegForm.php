<?php

namespace App\Livewire;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentRegForm extends Component
{
    use WithFileUploads;

    public $reg_no = '';
    public $first_name = '';
    public $last_name = '';
    public $gender = '';
    public $nic = '';
    public $email = '';
    public $phone = '';
    public $dob = '';
    public $password = '';
    public $address = '';
    public $image;

    public function submit()
    {
        $validated = $this->validate([
            'reg_no' => 'required|string|max:255|unique:students,reg_no',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|max:20',
            'nic' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'dob' => 'required|date',
            'password' => 'required|string|min:6',
            'address' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = $this->image
            ? $this->image->store('students', 'public')
            : null;

        Student::create([
            'reg_no' => $this->reg_no,
            'name' => trim($this->first_name . ' ' . $this->last_name),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'nic' => $this->nic,
            'email' => $this->email,
            'phone' => $this->phone,
            'dob' => $this->dob,
            'password' => Hash::make($this->password),
            'address' => $this->address,
            'image' => $imagePath,
        ]);

        $this->reset();

        $this->dispatch('student-created');

        session()->flash('success', 'Student registered successfully!');
    }

    public function render()
    {
        return view('livewire.student-reg-form');
    }
}
