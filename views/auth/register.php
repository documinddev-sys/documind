<div class="auth-card">
    <div class="auth-header mb-5">
        <div class="logo-orb mb-4 mx-auto">
            <i class="bi bi-person-plus-fill fs-3 text-white"></i>
        </div>
        <h1 class="h2 fw-bold text-dark">Create Account</h1>
        <p class="text-secondary">Join DocuMind today</p>
    </div>

    <form method="POST" action="/documind/public/register" class="auth-form gap-3">
        <?php echo $csrf_field ?? \App\Helpers\Csrf::field(); ?>

        <div class="mb-3">
            <label for="name" class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Full Name</label>
            <div class="input-group">
                <span class="input-group-text border-secondary">
                    <i class="bi bi-person text-secondary"></i>
                </span>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="John Doe" 
                    required
                    maxlength="150"
                    class="form-control border-secondary"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                >
            </div>
        </div>

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

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Password</label>
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
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text border-secondary">
                            <i class="bi bi-check2-circle text-secondary"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="••••••••" 
                            required
                            minlength="8"
                            class="form-control border-secondary"
                        >
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block rounded-pill py-3 fw-bold mt-2">
            Create Account
        </button>
    </form>

    <div class="auth-divider my-4 small opacity-50">OR</div>

    <div class="auth-footer text-center">
        <p class="text-secondary small">Already have an account? <a href="/documind/public/login" class="text-primary fw-bold text-decoration-none">Sign in</a></p>
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

@media (max-width: 576px) {
    .auth-card {
        padding: 32px 24px;
    }
}
</style>
