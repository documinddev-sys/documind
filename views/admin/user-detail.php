<!-- Admin User Detail with Quota Controls -->
<div class="mb-3">
    <a href="/documind/public/admin/users" class="text-decoration-none small" style="color: var(--primary);">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
</div>

<div class="row g-4">
    <!-- User Profile Card -->
    <div class="col-lg-4">
        <div class="card-glass animate-fade-in">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3"
                     style="width: 72px; height: 72px; font-size: 28px; background: var(--primary);">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge rounded-pill px-3 py-1" style="font-size: 11px; background: var(--primary-glow); color: var(--primary);">
                        <?= ucfirst($user['role'] ?? 'user') ?>
                    </span>
                    <span class="badge rounded-pill px-3 py-1 <?= ($user['is_active'] ?? 1) ? 'badge-status-approved' : 'badge-status-rejected' ?>" style="font-size: 11px;">
                        <?= ($user['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
            </div>

            <div class="border-top pt-3 mt-3" style="border-color: var(--border) !important;">
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted small">Joined</span>
                    <span class="small fw-medium"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted small">Last Login</span>
                    <span class="small fw-medium"><?= $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never' ?></span>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="border-top pt-3 mt-3" style="border-color: var(--border) !important;">
                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                    <?php if ($user['is_active'] ?? 1): ?>
                        <button class="btn btn-outline-danger btn-sm w-100 rounded-pill" id="deactivateBtn" data-user-id="<?= $user['id'] ?>">
                            <i class="bi bi-person-dash me-1"></i> Deactivate Account
                        </button>
                    <?php else: ?>
                        <button class="btn btn-sm w-100 rounded-pill" id="reactivateBtn" data-user-id="<?= $user['id'] ?>" style="background: var(--success); color: white;">
                            <i class="bi bi-person-check me-1"></i> Reactivate Account
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center small mb-0"><i class="bi bi-info-circle me-1"></i>This is your account</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quota Management -->
        <div class="card-glass mt-3 animate-fade-in">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2" style="color: var(--primary);"></i> Usage Quotas
            </h6>
            <form id="quotaForm">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Upload Limit</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" id="uploadLimit" name="upload_limit" 
                               value="<?= $user['upload_limit'] ?? 10 ?>" min="1" max="1000">
                        <span class="input-group-text" style="background: var(--surface-hover); border: 1px solid var(--border-strong);">docs</span>
                    </div>
                    <small class="text-muted">Currently using: <strong><?= $stats['total_documents'] ?? 0 ?></strong></small>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Daily AI Messages</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" id="dailyAiLimit" name="daily_ai_limit" 
                               value="<?= $user['daily_ai_limit'] ?? 20 ?>" min="1" max="500">
                        <span class="input-group-text" style="background: var(--surface-hover); border: 1px solid var(--border-strong);">msg/day</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                    <i class="bi bi-check2 me-1"></i>Save Quotas
                </button>
            </form>
        </div>
    </div>

    <!-- Stats & Activity -->
    <div class="col-lg-8">
        <!-- Stats Cards -->
        <div class="stats-grid mb-4 stagger-enter">
            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--primary-glow); color: var(--primary);">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Documents</span>
                        <span class="stat-value"><?= $stats['total_documents'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--info-glow); color: var(--info);">
                        <i class="bi bi-cloud-arrow-down"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Storage</span>
                        <span class="stat-value"><?= $stats['storage_mb'] ?? 0 ?> <small style="font-size: 10px;">MB</small></span>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--success-glow); color: var(--success);">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Views</span>
                        <span class="stat-value"><?= $stats['total_views'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card-glass animate-fade-in">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history" style="color: var(--primary);"></i> Recent Activity
            </h6>
            
            <?php if (empty($activity)): ?>
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>
                    No activity recorded yet.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($activity as $act): ?>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--surface-hover);">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge rounded-pill px-2 py-1 me-1" style="font-size: 9px; background: var(--primary-glow); color: var(--primary);">
                                            <?= strtoupper(htmlspecialchars($act['action'])) ?>
                                        </span>
                                        <span class="badge rounded-pill px-2 py-1" style="font-size: 9px; background: var(--surface-card); color: var(--text-secondary);">
                                            <?= htmlspecialchars($act['entity_type'] ?? '') ?>
                                        </span>
                                    </div>
                                    <span class="text-muted" style="font-size: 10px;"><?= date('M d, H:i', strtotime($act['created_at'])) ?></span>
                                </div>
                                <?php if (!empty($act['description'])): ?>
                                    <p class="mb-0 mt-1 text-muted small"><?= htmlspecialchars($act['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Quota form
document.getElementById('quotaForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const uploadLimit = document.getElementById('uploadLimit').value;
    const dailyAiLimit = document.getElementById('dailyAiLimit').value;

    try {
        const response = await fetch('/documind/public/admin/update-user-limits', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: <?= $user['id'] ?>,
                upload_limit: parseInt(uploadLimit),
                daily_ai_limit: parseInt(dailyAiLimit)
            })
        });
        const data = await response.json();
        if (data.success) {
            window.showToast('Quotas updated successfully', 'success');
        } else {
            window.showToast(data.error || 'Failed to update', 'error');
        }
    } catch (err) {
        window.showToast('Network error', 'error');
    }
});

// Deactivate
document.getElementById('deactivateBtn')?.addEventListener('click', async function() {
    const result = await Swal.fire({
        title: 'Deactivate Account?',
        text: "This user will no longer be able to access the system. You can reactivate them later.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Deactivate'
    });
    
    if (!result.isConfirmed) return;
    try {
        const response = await fetch('/documind/public/admin/deactivate-user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: parseInt(this.dataset.userId)})
        });
        const data = await response.json();
        if (data.success) {
            window.showToast('User deactivated', 'warning');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.showToast(data.error, 'error');
        }
    } catch (err) {
        window.showToast('Network error', 'error');
    }
});

// Reactivate
document.getElementById('reactivateBtn')?.addEventListener('click', async function() {
    try {
        const response = await fetch('/documind/public/admin/reactivate-user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: parseInt(this.dataset.userId)})
        });
        const data = await response.json();
        if (data.success) {
            window.showToast('User reactivated', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.showToast(data.error, 'error');
        }
    } catch (err) {
        window.showToast('Network error', 'error');
    }
});
</script>
