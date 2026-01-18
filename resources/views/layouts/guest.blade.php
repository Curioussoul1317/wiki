<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') - WikiTeqrious</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 2.5rem;
        }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-logo { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .auth-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
        .auth-subtitle { color: #64748b; margin-top: 0.5rem; }
        .auth-footer { text-align: center; margin-top: 1.5rem; color: #64748b; }
        .auth-footer a { color: #6366f1; text-decoration: none; font-weight: 500; }
        .btn-primary { background: #6366f1; border-color: #6366f1; }
        .btn-primary:hover { background: #4f46e5; border-color: #4f46e5; }
    </style>
</head>
<body>
    <div class="auth-card">
        @yield('content')
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>