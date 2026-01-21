<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} - LaraWiki</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .document-container { max-width: 800px; margin: 0 auto; padding: 2rem; }
        .document-header { margin-bottom: 2rem; }
        .document-content { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .document-content { line-height: 1.8; }
    </style>
</head>
<body>
    <div class="document-container">
        <div class="document-header">
            <div class="d-flex align-items-center mb-3">
                <span class="fs-1 me-3">{{ $document->emoji }}</span>
                <div>
                    <h1 class="h2 mb-1">{{ $document->title }}</h1>
                    <div class="text-muted small">
                        @if($document->collection)
                            <span class="me-3"><i class="bi bi-folder me-1"></i>{{ $document->collection->name }}</span>
                        @endif
                        <span><i class="bi bi-clock me-1"></i>Updated {{ $document->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="document-content">
            {!! nl2br(e($document->content)) !!}
        </div>

        <div class="text-center mt-4 text-muted">
            <small>Shared via <a href="{{ url('/') }}">LaraWiki</a></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>