<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'WikiTeqrious') }} - Team Knowledge Base</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #4f46e5;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 600px;
        }

        .btn-light {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-outline-light {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature-card {
            padding: 2rem;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .features-section {
            padding: 100px 0;
            background: #f8fafc;
        }

        .cta-section {
            padding: 100px 0;
            background: #1e1e2e;
            color: white;
        }

        .navbar {
            background: transparent;
            padding: 1rem 0;
        }

        .navbar.scrolled {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .footer {
            padding: 3rem 0;
            background: #0f0f1a;
            color: #a1a1aa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark position-absolute w-100" style="z-index: 1000;">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="/">
                <i class="bi bi-journal-text me-2"></i>WikiTeqrious
            </a>
            <div class="d-flex gap-3">
                <a href="{{ route('login') }}" class="btn btn-outline-light">Login</a> 
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Your team's knowledge,<br>beautifully organized</h1>
                    <p class="mb-4">
                        WikiTeqrious is a modern wiki and knowledge base for teams. 
                        Write, organize, and share documentation with ease.
                    </p>
                    <div class="d-flex gap-3"> 
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            Learn more
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="text-center">
                        <i class="bi bi-journal-richtext" style="font-size: 15rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">Everything you need for team documentation</h2>
                <p class="text-muted">Powerful features to help your team work better together</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <h5 class="fw-bold">Rich Documents</h5>
                        <p class="text-muted mb-0">
                            Write beautiful documents with Markdown support, 
                            nested pages, and rich formatting options.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-folder"></i>
                        </div>
                        <h5 class="fw-bold">Collections</h5>
                        <p class="text-muted mb-0">
                            Organize documents into collections and sub-collections 
                            for easy navigation and discovery.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-bold">Team Collaboration</h5>
                        <p class="text-muted mb-0">
                            Work together with comments, version history, 
                            and role-based permissions.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h5 class="fw-bold">Powerful Search</h5>
                        <p class="text-muted mb-0">
                            Find anything instantly with full-text search 
                            across all your documents.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h5 class="fw-bold">Version History</h5>
                        <p class="text-muted mb-0">
                            Never lose your work. Every change is tracked 
                            and can be restored at any time.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-share"></i>
                        </div>
                        <h5 class="fw-bold">Easy Sharing</h5>
                        <p class="text-muted mb-0">
                            Share documents publicly or privately with 
                            password-protected links.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Ready to get started?</h2>
            <p class="text-muted mb-4">Join teams using WikiTeqrious to organize their knowledge.</p>           
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-journal-text me-2 fs-4"></i>
                        <span class="fw-bold">WikiTeqrious</span>
                    </div>
                    <p class="small mt-2 mb-0">Built with Laravel & Bootstrap</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; {{ date('Y') }} WikiTeqrious. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>