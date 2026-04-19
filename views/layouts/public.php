<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DocuMind — Browse AI-analysed documents in our public library">
    <title><?php echo htmlspecialchars($title ?? 'Library'); ?> | <?php echo htmlspecialchars($_ENV['APP_NAME'] ?? 'DocuMind'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap, Icons & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/documind/public/assets/css/main.css">
</head>
<body>
    <!-- Public Navigation -->
    <nav class="public-nav">
        <a href="/documind/public/library" class="sidebar-brand">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>DocuMind</span>
        </a>

        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($isLoggedIn)): ?>
                <a href="/documind/public/dashboard" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 13px;">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="/documind/public/login" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 13px;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </a>
                <a href="/documind/public/register" class="btn btn-sm btn-primary rounded-pill px-3" style="font-size: 13px;">
                    Get Started
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="public-container">
        <?= $content ?? '' ?>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 border-top" style="color: var(--text-muted); font-size: 13px;">
        <div class="public-container">
            <p class="mb-1">DocuMind — AI-powered document management</p>
            <p class="mb-0">
                <a href="/documind/public/library" class="text-decoration-none" style="color: var(--text-secondary);">Library</a>
                <span class="mx-2">•</span>
                <a href="/documind/public/login" class="text-decoration-none" style="color: var(--text-secondary);">Sign In</a>
                <span class="mx-2">•</span>
                <a href="/documind/public/register" class="text-decoration-none" style="color: var(--text-secondary);">Register</a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/documind/public/assets/js/main.js"></script>

    <?php if (!empty($_SESSION['success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: <?php echo json_encode($_SESSION['success']); ?>,
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (!empty($_SESSION['errors'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const errors = <?php echo json_encode((array)$_SESSION['errors']); ?>;
            Swal.fire({
                icon: 'error',
                title: 'Operation Failed',
                html: errors.map(e => `<div>${e}</div>`).join(''),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        });
    </script>
    <?php unset($_SESSION['errors']); endif; ?>
</body>
</html>
