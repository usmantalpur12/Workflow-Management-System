<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        h1 {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User Name</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>GPS Location</th>
                <th>Locked</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->id }}</td>
                <td>{{ $attendance->user ? $attendance->user->name : 'N/A' }}</td>
                <td>{{ $attendance->clock_in ? $attendance->clock_in->format('Y-m-d H:i:s') : 'N/A' }}</td>
                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('Y-m-d H:i:s') : 'N/A' }}</td>
                <td>{{ $attendance->gps_location ?? 'N/A' }}</td>
                <td>{{ $attendance->locked ? 'Yes' : 'No' }}</td>
                <td>{{ $attendance->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 