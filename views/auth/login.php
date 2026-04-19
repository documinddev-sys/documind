<div class="auth-card">
    <div class="auth-header mb-5">
        <div class="logo-orb mb-4 mx-auto">
            <i class="bi bi-file-earmark-text-fill fs-3 text-white"></i>
        </div>
        <h1 class="h2 fw-bold text-dark">Welcome Back</h1>
        <p class="text-secondary">Sign in to your DocuMind account</p>
    </div>

    <form method="POST" action="/documind/public/login" class="auth-form gap-4">
        <?php echo $csrf_field ?? \App\Helpers\Csrf::field(); ?>

        <div class="mb-3">
            <label for="email" class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Email Address</label>
            <div class="input-group">
                <span class="input-group-text border-secondary">
                    <i class="bi bi-envelope text-secondary"></i>
                </span>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="name@company.com" 
                    required
                    class="form-control border-secondary"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between mb-2">
                <label for="password" class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Password</label>
                <a href="#" class="text-primary text-decoration-none" style="font-size: 11px;">Forgot password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text border-secondary">
                    <i class="bi bi-shield-lock text-secondary"></i>
                </span>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                    minlength="8"
                    class="form-control border-secondary"
                >
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block rounded-pill py-3 fw-bold mt-3">
            Sign In
        </button>
    </form>

    <div class="auth-divider my-4 small opacity-50">OR</div>

    <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2 d-flex align-items-center justify-content-center gap-2 small" onclick="DocuMindUI.showToast('OAuth Integration Pending', 'info')">
        <i class="bi bi-google"></i>
        <span>Continue with Google</span>
    </button>

    <div class="auth-footer text-center mt-5">
        <p class="text-secondary small">Don't have an account? <a href="/documind/public/register" class="text-primary fw-bold text-decoration-none">Create one</a></p>
        <p class="text-muted mt-2" style="font-size: 12px;">
            <a href="/documind/public/library" class="text-decoration-none" style="color: var(--text-secondary);">
                <i class="bi bi-book me-1"></i>Browse Public Library without an account
            </a>
        </p>
    </div>
</div>

<style>
.logo-orb {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #0d6efd, #0dcaf0);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.input-group-text {
    background: transparent;
    border-right: none;
}

.input-group .form-control {
    border-left: none;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: none;
}
</style>
