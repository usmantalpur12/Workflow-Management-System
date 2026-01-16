@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="container-fluid mt-5">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Create New Department</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Department Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="hr_admin_id" class="form-label">HR Admin</label>
                    <select class="form-select @error('hr_admin_id') is-invalid @enderror" id="hr_admin_id" name="hr_admin_id" required>
                        <option value="">Select HR Admin</option>
                        @foreach($hrAdmins as $hrAdmin)
                            <option value="{{ $hrAdmin->id }}" {{ old('hr_admin_id') == $hrAdmin->id ? 'selected' : '' }}>
                                {{ $hrAdmin->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('hr_admin_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
