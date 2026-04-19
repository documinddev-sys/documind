<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(\App\Helpers\Csrf::generate()); ?>">
    <meta name="description" content="DocuMind — AI-powered document management and analysis">
    <title><?php echo htmlspecialchars($title ?? 'Welcome'); ?> | <?php echo htmlspecialchars($_ENV['APP_NAME'] ?? 'DocuMind'); ?></title>

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
<body class="auth-bg">
    <div class="auth-container animate-fade-in">
        <?php echo $content ?? ''; ?>
    </div>

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
