<div class="real-time-stats">
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="online-users">0</div>
                    <div class="stat-label">Online Users</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="active-tasks">0</div>
                    <div class="stat-label">Active Tasks</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning text-white">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="pending-approvals">0</div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info text-white">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="productivity-score">0%</div>
                    <div class="stat-label">Productivity Score</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.real-time-stats {
    margin-bottom: 2rem;
}

.stat-card {
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    height: 120px;
    display: flex;
    align-items: center;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 2.5rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}
</style>

<script>
// Real-time stats update
function updateRealTimeStats() {
    fetch('/api/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('online-users').textContent = data.online_users || 0;
            document.getElementById('active-tasks').textContent = data.active_tasks || 0;
            document.getElementById('pending-approvals').textContent = data.pending_approvals || 0;
            document.getElementById('productivity-score').textContent = (data.productivity_score || 0) + '%';
        })
        .catch(error => console.error('Error updating stats:', error));
}

// Update stats every 30 seconds
setInterval(updateRealTimeStats, 30000);

// Initial load
updateRealTimeStats();
</script>
