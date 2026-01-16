@extends('layouts.app')

@section('content')
<div class="cati-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">{{ $project->name }}</h1>
            <div class="text-muted">
                <span class="me-3">Status: <span class="badge bg-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in-progress' ? 'warning' : 'primary') }}">{{ ucfirst($project->status) }}</span></span>
                <span>Created by: {{ $project->creator ? $project->creator->name : 'Unknown' }}</span>
            </div>
        </div>
        <div>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary me-2"><i class="fas fa-edit me-2"></i>Edit Project</a>
            @if(Auth::user()->role === 'super-admin')
                <a href="{{ route('projects.assign-departments', $project) }}" class="btn btn-warning me-2"><i class="fas fa-building me-2"></i>Manage Departments</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Project Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Project Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> {{ $project->start_date->format('M d, Y') }}</p>
                            <p><strong>End Date:</strong> {{ $project->end_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created:</strong> {{ $project->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Last Updated:</strong> {{ $project->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <p class="mt-3">{{ $project->description }}</p>
                </div>
            </div>

            <!-- Department Breakdown -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Department Breakdown</h5>
                    @foreach($project->departments as $department)
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-building me-2"></i>{{ $department->name }}
                                    <span class="badge bg-info ms-2">{{ $project->usersByDepartment($department)->count() }} Members</span>
                                </div>
                                @if(Auth::user()->role === 'hr-admin' && Auth::user()->department_id === $department->id)
                                    <a href="{{ route('projects.assign-department-users', [$project, $department]) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-users me-2"></i>Manage Members
                                    </a>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Member</th>
                                                <th>Role</th>
                                                <th>Assigned By</th>
                                                <th>Assigned On</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($project->usersByDepartment($department)->get() as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->role }}</td>
                                                    <td>
                                                        @if($user->pivot->assigned_by)
                                                            @php
                                                                $assignedByUser = \App\Models\User::find($user->pivot->assigned_by);
                                                            @endphp
                                                            {{ $assignedByUser ? $assignedByUser->name : 'User not found' }}
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->pivot->created_at->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Project Timeline -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Project Timeline</h5>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $project->progress }}%" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $project->progress }}%
                        </div>
                    </div>
                    <div class="mt-3">
                        <p><strong>Start Date:</strong> {{ $project->start_date->format('M d, Y') }}</p>
                        <p><strong>End Date:</strong> {{ $project->end_date->format('M d, Y') }}</p>
                        <p><strong>Duration:</strong> {{ $project->start_date->diffInDays($project->end_date) }} days</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recent Activity</h5>
                    <div class="list-group list-group-flush">
                        @if($project->departments->count() > 0)
                            @foreach($project->departments->take(3) as $department)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-building text-info me-2"></i>
                                            <span class="text-muted">Department: {{ $department->name }}</span>
                                        </div>
                                        <div class="text-muted small">{{ $project->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @if($project->departmentUsers->count() > 0)
                            @foreach($project->departmentUsers->take(3) as $user)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user text-warning me-2"></i>
                                            <span class="text-muted">User: {{ $user->name }}</span>
                                        </div>
                                        <div class="text-muted small">{{ $user->pivot->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @if($project->departments->count() == 0 && $project->departmentUsers->count() == 0)
                            <div class="list-group-item text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No recent activity
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
