@extends('layouts.app')

@section('content')
<div class="cati-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage {{ $department->name }} Team for {{ $project->name }}</h1>
            <div class="text-muted">As HR Admin of {{ $department->name }}, you can manage team members for this project</div>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <form action="{{ route('projects.assign-department-users.update', [$project, $department]) }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Current Members -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Current Team Members</h5>
                        <p class="card-text text-muted small mb-0">{{ $project->usersByDepartment($department)->count() }} members assigned</p>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @forelse($project->usersByDepartment($department)->get() as $user)
                                @if($user && is_object($user))
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <img src="{{ $user->profile_picture_url ?? asset('images/default-avatar.png') }}" alt="{{ $user->name ?? 'Unknown' }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                        {{ $user->name ?? 'Unknown User' }}
                                        <span class="badge bg-{{ ($user->role ?? '') === 'hr-admin' ? 'warning' : 'primary' }} ms-2">{{ ucfirst($user->role ?? 'user') }}</span>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}" checked>
                                    </div>
                                </div>
                                @endif
                            @empty
                                <div class="list-group-item text-muted">No team members assigned yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Department Users -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Department Users</h5>
                        <p class="card-text text-muted small mb-0">{{ $department->users()->count() }} users in department</p>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @forelse($department->users as $user)
                                @if($user && is_object($user) && !$project->usersByDepartment($department)->get()->contains('id', $user->id))
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <img src="{{ $user->profile_picture_url ?? asset('images/default-avatar.png') }}" alt="{{ $user->name ?? 'Unknown' }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                            {{ $user->name ?? 'Unknown User' }}
                                            <span class="badge bg-{{ ($user->role ?? '') === 'hr-admin' ? 'warning' : 'primary' }} ms-2">{{ ucfirst($user->role ?? 'user') }}</span>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}">
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="list-group-item text-muted">No users available in department</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-warning"><i class="fas fa-users me-2"></i>Update Team</button>
        </div>
    </form>
</div>
@endsection
