@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Profile</h1>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Picture and Basic Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ $user->profile_picture_url }}" 
                             alt="Profile Picture" 
                             class="rounded-circle img-fluid" 
                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #3b82f6;">
                    </div>
                    <h4 class="card-title">{{ $user->name }}</h4>
                    <p class="text-muted">{{ ucfirst(str_replace('-', ' ', $user->role)) }}</p>
                    <span class="badge bg-{{ $user->role === 'super-admin' ? 'danger' : ($user->role === 'hr-admin' ? 'warning' : ($user->role === 'department-admin' ? 'info' : 'secondary')) }} fs-6">
                        {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <p class="form-control-plaintext">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <p class="form-control-plaintext">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Department</label>
                                <p class="form-control-plaintext">{{ $user->department ?? 'Not assigned' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">IP Address</label>
                                <p class="form-control-plaintext">{{ $user->ip_address ?? 'Not restricted' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Member Since</label>
                                <p class="form-control-plaintext">{{ $user->created_at->format('F j, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <p class="form-control-plaintext">{{ $user->updated_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                            <h5 class="card-title">Attendance Records</h5>
                            <h3 class="text-primary">{{ $user->attendances->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-comments fa-2x text-success mb-2"></i>
                            <h5 class="card-title">Chat Messages</h5>
                            <h3 class="text-success">{{ $user->sentChats->count() + $user->receivedChats->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-calendar fa-2x text-warning mb-2"></i>
                            <h5 class="card-title">Days Active</h5>
                            <h3 class="text-warning">{{ $user->created_at->diffInDays(now()) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 