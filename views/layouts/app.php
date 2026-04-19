<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(\App\Helpers\Csrf::generate()); ?>">
    <meta name="description" content="DocuMind — AI-powered document management and analysis platform">
    <title><?php echo htmlspecialchars($title ?? 'Dashboard'); ?> | <?php echo htmlspecialchars($_ENV['APP_NAME'] ?? 'DocuMind'); ?></title>

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
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-header">
                <a href="/documind/public/dashboard" class="sidebar-brand">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>DocuMind</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <a href="/documind/public/dashboard" class="nav-item-link <?= $currentUri === '/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house-door fs-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/documind/public/documents" class="nav-item-link <?= strpos($currentUri, '/documents') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-files fs-5"></i>
                    <span>Documents</span>
                </a>
                <a href="/documind/public/collections" class="nav-item-link <?= strpos($currentUri, '/collections') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-folder fs-5"></i>
                    <span>Collections</span>
                </a>
                <a href="/documind/public/user/shared-with-me" class="nav-item-link <?= strpos($currentUri, '/user/shared') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-share fs-5"></i>
                    <span>Shared</span>
                </a>
                <a href="/documind/public/library" class="nav-item-link <?= strpos($currentUri, '/library') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-book fs-5"></i>
                    <span>Public Library</span>
                </a>

                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <div class="dropdown-divider my-2"></div>
                <a href="/documind/public/admin" class="nav-item-link <?= $currentUri === '/admin' ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock fs-5"></i>
                    <span>Admin Panel</span>
                </a>
                <a href="/documind/public/admin/pending-documents" class="nav-item-link <?= strpos($currentUri, '/admin/pending') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-clipboard-check fs-5"></i>
                    <span>Review Queue</span>
                    <?php
                        $pendingCount = (new \App\Models\Document())->countByStatus('pending');
                        if ($pendingCount > 0):
                    ?>
                    <span class="badge bg-warning text-dark ms-auto" style="font-size: 10px;"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/documind/public/admin/users" class="nav-item-link <?= strpos($currentUri, '/admin/users') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-people fs-5"></i>
                    <span>Users</span>
                </a>
                <a href="/documind/public/admin/documents" class="nav-item-link <?= strpos($currentUri, '/admin/documents') === 0 && strpos($currentUri, '/admin/documents') !== false && strpos($currentUri, '/pending') === false ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text fs-5"></i>
                    <span>All Documents</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="p-3 rounded-3 mb-2" style="background: var(--surface-hover);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 32px; height: 32px; font-size: 12px; background: var(--primary);">
                            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold small text-truncate" style="max-width: 160px;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></div>
                            <div class="text-muted" style="font-size: 11px;"><?= htmlspecialchars(($_SESSION['user_role'] ?? 'user') === 'admin' ? 'Administrator' : 'Member') ?></div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="/documind/public/logout" class="mb-0">
                    <?= \App\Helpers\Csrf::field() ?>
                    <button type="submit" class="nav-item-link w-100 d-flex align-items-center gap-3 border-0 bg-transparent text-start" style="color: var(--danger);">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="d-flex align-items-center gap-3">
                    <button id="mobileHamburger" class="btn btn-link p-0 d-xl-none text-dark border-0" aria-label="Toggle menu">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div class="page-title">
                        <h1><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
                    </div>
                </div>

                <div class="top-bar-actions">
                    <a href="/documind/public/user/notifications" class="position-relative text-decoration-none" style="color: var(--text-secondary);" aria-label="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: .6em;">0</span>
                    </a>

                    <div class="dropdown">
                        <a class="dropdown-toggle d-flex align-items-center gap-2 text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" style="color: var(--text-secondary);">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 34px; height: 34px; font-size: .75em; background: var(--primary);">
                                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <span class="d-none d-md-inline fw-medium small"><?= htmlspecialchars(substr($_SESSION['user_name'] ?? 'User', 0, 15)) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: var(--radius-sm);">
                            <li><a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2 small" href="/documind/public/user/profile">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2 small" href="/documind/public/library">
                                <i class="bi bi-book"></i> Public Library
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="/documind/public/logout">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="dropdown-item d-flex align-items-center gap-2 px-3 py-2 small w-100 border-0 bg-transparent" style="color: var(--danger);">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Mobile Overlay -->
            <div id="mobileOverlay" class="mobile-overlay position-fixed d-none"></div>

            <!-- Page Content -->
            <div class="app-container">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

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
