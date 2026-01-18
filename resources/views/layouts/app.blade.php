<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wiki') - {{ config('app.name', 'WikiTeqrious') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 60px;
            --primary-color: #6366f1;
            --secondary-color: #4f46e5;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #1e1e2e;
            color: #cdd6f4;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: #fff;
            text-decoration: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            color: #a6adc8;
            text-decoration: none;
            transition: all 0.2s;
        }

        .sidebar-nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .sidebar-nav-item.active {
            background: rgba(99, 102, 241, 0.2);
            color: #fff;
            border-right: 3px solid var(--primary-color);
        }

        .sidebar-nav-item i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar-section {
            padding: 0.75rem 1.25rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c7086;
            font-weight: 600;
        }

        .collection-item {
            padding: 0.5rem 1.25rem 0.5rem 2rem;
            display: flex;
            align-items: center;
            color: #a6adc8;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .collection-item:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .collection-item .emoji {
            margin-right: 0.5rem;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .main-header {
            position: sticky;
            top: 0;
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            z-index: 100;
        }

        .main-content {
            padding: 1.5rem;
        }

        /* Search */
        .search-wrapper {
            position: relative;
            max-width: 400px;
            flex: 1;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }

        /* Document Card */
        .document-card {
            display: block;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .document-card-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
        }

        .document-card-title .emoji {
            margin-right: 0.5rem;
        }

        .document-card-meta {
            font-size: 0.8rem;
            color: #64748b;
        }

        .document-card-excerpt {
            font-size: 0.875rem;
            color: #475569;
            margin-top: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Tags */
        .tag {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            border-radius: 9999px;
            background: #e0e7ff;
            color: #4338ca;
            text-decoration: none;
        }

        .tag:hover {
            background: #c7d2fe;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }

        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color);
        }

        /* User Dropdown */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .user-dropdown:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        /* Activity Item */
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f5f9;
        }

        .activity-content {
            flex: 1;
        }

        .activity-time {
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="bi bi-journal-text me-2"></i>WikiTeqrious
            </a>
            <button class="btn btn-sm btn-link text-white d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('documents.index') }}" class="sidebar-nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i>
                <span>Documents</span>
            </a>
            <a href="{{ route('collections.index') }}" class="sidebar-nav-item {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                <i class="bi bi-folder"></i>
                <span>Collections</span>
            </a>
            <a href="{{ route('tags.index') }}" class="sidebar-nav-item {{ request()->routeIs('tags.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i>
                <span>Tags</span>
            </a>

            @auth
    @if(auth()->user()->currentTeam())
        <div class="sidebar-section">Collections</div>
        @php
            $sidebarCollections = auth()->user()->currentTeam()->rootCollections()->orderBy('sort_order')->limit(10)->get();
        @endphp
        @foreach($sidebarCollections as $collection)
            <a href="{{ route('collections.show', $collection) }}" class="collection-item">
                <span class="emoji">{{ $collection->icon }}</span>
                <span>{{ Str::limit($collection->name, 20) }}</span>
            </a>
        @endforeach
    @endif
@endauth
        </nav>

        <div class="mt-auto p-3 border-top border-secondary">
            <a href="{{ route('documents.create') }}" class="btn btn-primary w-100">
                <i class="bi bi-plus-lg me-1"></i> New Document
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Header -->
        <header class="main-header">
            <button class="btn btn-link text-dark d-lg-none me-2" onclick="toggleSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search documents..." id="globalSearch">
            </div>

            <div class="ms-auto d-flex align-items-center gap-3">
                @if(auth()->check())
                    <!-- Team Switcher -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            {{ auth()->user()->currentTeam()?->name ?? 'Select Team' }}
                        </button>
                        <ul class="dropdown-menu">
                            @foreach(auth()->user()->teams as $team)
                                <li>
                                    <form action="{{ route('teams.switch', $team) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item {{ auth()->user()->currentTeam()?->id === $team->id ? 'active' : '' }}">
                                            {{ $team->name }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('teams.create') }}"><i class="bi bi-plus me-1"></i> Create Team</a></li>
                        </ul>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <div class="user-dropdown" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar_url }}" alt="" class="user-avatar">
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('teams.show', auth()->user()->currentTeam()) }}"><i class="bi bi-people me-2"></i> Team Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Search Results Dropdown -->
    <div class="position-fixed" id="searchResults" style="display: none; top: 70px; left: 50%; transform: translateX(-50%); width: 500px; max-width: 90vw; z-index: 1050;">
        <div class="card shadow-lg">
            <div class="card-body p-0" id="searchResultsContent">
                <!-- Results will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Global Search
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        const searchResultsContent = document.getElementById('searchResultsContent');
        let searchTimeout;

        searchInput?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('search.quick') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.results.length === 0) {
                            searchResultsContent.innerHTML = '<div class="p-3 text-muted text-center">No results found</div>';
                        } else {
                            searchResultsContent.innerHTML = data.results.map(item => `
                                <a href="${item.url}" class="d-block p-3 border-bottom text-decoration-none text-dark hover-bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">${item.emoji}</span>
                                        <div>
                                            <div class="fw-semibold">${item.title}</div>
                                            ${item.collection ? `<small class="text-muted">in ${item.collection}</small>` : ''}
                                        </div>
                                        <span class="ms-auto badge bg-light text-dark">${item.type}</span>
                                    </div>
                                </a>
                            `).join('');
                        }
                        searchResults.style.display = 'block';
                    });
            }, 300);
        });

        // Close search results on click outside
        document.addEventListener('click', function(e) {
            if (!searchInput?.contains(e.target) && !searchResults?.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Keyboard shortcut for search
        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                searchInput?.focus();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>