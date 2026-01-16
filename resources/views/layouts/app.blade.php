<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Workflow Management System')</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/modern-saas.css') }}">
    
    @yield('styles')
</head>
<body class="role-{{ str_replace('-', '-', auth()->user()->role ?? 'user') }}">
    <!-- Modern Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <span>Workflow OS</span>
            </a>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="sidebar-profile">
            <img src="{{ auth()->user()->profile_picture_url }}" alt="{{ auth()->user()->name }}" class="sidebar-profile-img">
            <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-profile-role">{{ ucfirst(str_replace('-', ' ', auth()->user()->role)) }}</div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-label">Main</div>
                <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('attendances.index') }}" class="sidebar-nav-link {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Attendance</span>
                </a>
                <a href="{{ route('projects.index') }}" class="sidebar-nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <i class="fas fa-project-diagram"></i>
                    <span>Projects</span>
                </a>
            </div>

            @if(auth()->user()->role === 'super-admin')
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-label">Administration</div>
                <a href="{{ route('departments.index') }}" class="sidebar-nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Departments</span>
                </a>
                <a href="{{ route('users.index') }}" class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->role === 'hr-admin')
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-label">HR Management</div>
                <a href="{{ route('hr.dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('hr.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>HR Dashboard</span>
                </a>
                <a href="{{ route('hr.assign-employees') }}" class="sidebar-nav-link {{ request()->routeIs('hr.assign-employees') ? 'active' : '' }}">
                    <i class="fas fa-user-plus"></i>
                    <span>Assign Employees</span>
                </a>
            </div>
            @endif

            <div class="sidebar-nav-section">
                <div class="sidebar-nav-label">Communication</div>
                <a href="{{ route('chats.index') }}" class="sidebar-nav-link {{ request()->routeIs('chats.index') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Chats</span>
                </a>
                <a href="{{ route('chats.advanced') }}" class="sidebar-nav-link {{ request()->routeIs('chats.advanced') ? 'active' : '' }}">
                    <i class="fas fa-rocket"></i>
                    <span>Advanced Chat</span>
                </a>
            </div>

            <div class="sidebar-nav-section">
                <div class="sidebar-nav-label">Account</div>
                <a href="{{ route('profile.show') }}" class="sidebar-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="sidebar-nav-link" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <!-- Modern Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search projects, users, tasks...">
            </div>
        </div>
        <div class="topbar-right">
            <button class="topbar-action" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            <div class="dropdown">
                <button class="topbar-action" data-bs-toggle="dropdown" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span id="notificationCount" class="topbar-notification-badge" style="display: none;"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 350px; padding: 0;">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <div id="notificationsList" style="max-height: 400px; overflow-y: auto;">
                        <li class="px-3 py-2 text-muted text-center">No notifications</li>
                    </div>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#" onclick="loadNotifications(); return false;">Refresh</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="topbar-user" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->profile_picture_url }}" alt="{{ auth()->user()->name }}" class="topbar-user-img">
                    <div class="topbar-user-info d-none d-md-block">
                        <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                        <div class="topbar-user-role">{{ ucfirst(str_replace('-', ' ', auth()->user()->role)) }}</div>
                    </div>
                    <i class="fas fa-chevron-down ms-2 d-none d-md-block"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        window.currentUserRole = "{{ auth()->user()->role ?? '' }}";
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainWrapper = document.querySelector('.main-wrapper');
        const topbar = document.querySelector('.topbar');

        function handleSidebarToggle() {
            if (window.innerWidth <= 1024) {
                // Mobile: toggle show class
                sidebarToggle.onclick = () => {
                    sidebar.classList.toggle('show');
                };
            } else {
                // Desktop: toggle collapsed class
                sidebarToggle.onclick = () => {
                    sidebar.classList.toggle('collapsed');
                };
            }
        }

        // Initialize
        handleSidebarToggle();
        window.addEventListener('resize', handleSidebarToggle);

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                const icon = themeToggle.querySelector('i');
                icon.classList.toggle('fa-moon');
                icon.classList.toggle('fa-sun');
            });
        }

        // Notifications (only for admin roles)
        const allowedNotificationRoles = ['super-admin', 'hr-admin', 'department-admin'];
        let notificationCount = 0;
        
        function loadNotifications() {
            if (!allowedNotificationRoles.includes(window.currentUserRole)) {
                return;
            }

            fetch('/attendances/notifications')
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 403) {
                            const notificationsList = document.getElementById('notificationsList');
                            const notificationCountElement = document.getElementById('notificationCount');
                            if (notificationsList && notificationCountElement) {
                                notificationCount = 0;
                                notificationCountElement.style.display = 'none';
                                notificationsList.innerHTML = '<li class="px-3 py-2 text-muted text-center">No notifications available</li>';
                            }
                        }
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const notificationsList = document.getElementById('notificationsList');
                    const notificationCountElement = document.getElementById('notificationCount');
                    
                    if (!notificationsList || !notificationCountElement) {
                        return;
                    }

                    if (Array.isArray(data) && data.length > 0) {
                        notificationsList.innerHTML = '';
                        data.forEach(notification => {
                            const notificationItem = document.createElement('li');
                            notificationItem.className = 'px-3 py-2';
                            notificationItem.style.borderBottom = '1px solid var(--border-color)';
                            notificationItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-clock text-primary me-2"></i>
                                    <div>
                                        <div class="fw-bold">${notification.user}</div>
                                        <small class="text-muted">${notification.action} at ${notification.time}</small>
                                    </div>
                                </div>
                            `;
                            notificationsList.appendChild(notificationItem);
                        });
                        
                        notificationCount = data.length;
                        notificationCountElement.textContent = notificationCount;
                        notificationCountElement.style.display = 'block';
                    } else {
                        notificationCount = 0;
                        notificationCountElement.style.display = 'none';
                        notificationsList.innerHTML = '<li class="px-3 py-2 text-muted text-center">No new notifications</li>';
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        // Load notifications every 30 seconds (admin roles only)
        if (allowedNotificationRoles.includes(window.currentUserRole)) {
            setInterval(loadNotifications, 30000);
            loadNotifications();
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
