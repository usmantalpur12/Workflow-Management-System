@extends('layouts.app')

@section('title', $department->name)

@section('content')
<div class="container-fluid mt-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $department->name }}</h5>
            <div>
                <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-warning me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('departments.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Department Details</h6>
                    <p><strong>HR Admin:</strong> {{ $department->hrAdmin->name }}</p>
                    <p><strong>Description:</strong> {{ $department->description }}</p>
                </div>
                <div class="col-md-6">
                    <h6>Statistics</h6>
                    <p><strong>Projects:</strong> {{ $department->projects()->count() }}</p>
                    <p><strong>Users:</strong> {{ $department->users()->count() }}</p>
                </div>
            </div>

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="nav-item active">
                        <a class="nav-link" data-toggle="tab" href="#projects">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#users">Users</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="projects">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($department->projects as $project)
                                    <tr>
                                        <td>{{ $project->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $project->status_color }}">
                                                {{ $project->status }}
                                            </span>
                                        </td>
                                        <td>{{ $project->start_date->format('M d, Y') }}</td>
                                        <td>{{ $project->end_date->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane" id="users">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Projects</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($department->users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role }}</td>
                                        <td>{{ $user->projects()->count() }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
