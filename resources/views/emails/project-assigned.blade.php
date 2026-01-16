<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Assignment Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .project-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .project-title {
            color: #495057;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .project-details {
            color: #6c757d;
            margin-bottom: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-planning { background: #e3f2fd; color: #1976d2; }
        .status-active { background: #e8f5e8; color: #2e7d32; }
        .status-completed { background: #f3e5f5; color: #7b1fa2; }
        .status-on_hold { background: #fff3e0; color: #f57c00; }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 New Project Assignment</h1>
        <p>You have been assigned to a new project</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }}!</h2>
        
        <p>You have been assigned to a new project. Here are the details:</p>
        
        <div class="project-card">
            <div class="project-title">{{ $project->name }}</div>
            <div class="project-details">
                <p><strong>Description:</strong> {{ $project->description }}</p>
                <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($project->start_date)->format('F j, Y') }}</p>
                <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($project->end_date)->format('F j, Y') }}</p>
                <p><strong>Priority:</strong> {{ ucfirst($project->priority) }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $project->status }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </p>
            </div>
        </div>
        
        <p>Please log in to your dashboard to view more details and start working on the project.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/dashboard') }}" class="btn">View Dashboard</a>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from the Workflow Management System.</p>
            <p>If you have any questions, please contact your HR administrator.</p>
        </div>
    </div>
</body>
</html>