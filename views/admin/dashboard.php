<!-- Admin Dashboard -->
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1">System Overview</h2>
        <p class="text-muted small">Administrative controls and system metrics.</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid stagger-enter">
    <!-- Total Users -->
    <div class="card">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-glow); color: var(--primary);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Users</span>
                <span class="stat-value" data-count="<?= $stats['total_users'] ?? 0 ?>"><?= $stats['total_users'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <!-- Active Users -->
    <div class="card">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-glow); color: var(--success);">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Active (30d)</span>
                <span class="stat-value" data-count="<?= $stats['active_users'] ?? 0 ?>"><?= $stats['active_users'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <!-- Total Documents -->
    <div class="card">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed;">
                <i class="bi bi-file-earmark"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Documents</span>
                <span class="stat-value" data-count="<?= $stats['total_documents'] ?? 0 ?>"><?= $stats['total_documents'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <!-- Pending Review -->
    <div class="card">
        <a href="/documind/public/admin/pending-documents" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning-glow); color: var(--warning);">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Pending Review</span>
                    <span class="stat-value" style="color: var(--warning);" data-count="<?= $pendingCount ?? 0 ?>"><?= $pendingCount ?? 0 ?></span>
                </div>
            </div>
        </a>
    </div>

    <!-- Storage -->
    <div class="card">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-glow); color: var(--info);">
                <i class="bi bi-cloud-arrow-down"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Storage Used</span>
                <span class="stat-value"><?= $stats['total_storage_mb'] ?? 0 ?> <small style="font-size: 10px; opacity: 0.6;">MB</small></span>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- System Activity -->
    <div class="col-xl-8 mb-4">
        <div class="card h-100">
            <div class="section-header">
                <h2 class="h5 mb-0">Recent Activity</h2>
                <a href="/documind/public/admin/activity-log" class="btn btn-sm btn-link p-0 text-decoration-none fw-semibold" style="color: var(--primary);">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($recentActivity)): ?>
                <div class="text-center py-5">
                    <p class="text-muted small">No system activity logged yet.</p>
                </div>
            <?php else: ?>
                <div class="activity-list px-3 pb-3">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item p-3 rounded-3 mb-2" style="background: var(--surface-hover);">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" 
                                     style="width: 34px; height: 34px; font-size: 13px; background: var(--primary); flex-shrink: 0;">
                                    <?= strtoupper(substr($activity['user_name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between">
                                        <p class="mb-0 small fw-bold"><?= htmlspecialchars($activity['user_name']) ?></p>
                                        <span class="text-muted" style="font-size: 10px;"><?= date('M d, H:i', strtotime($activity['created_at'])) ?></span>
                                    </div>
                                    <p class="mb-0 text-muted small mt-1">
                                        <span class="badge rounded-pill px-2 py-1 me-1" style="font-size: 9px; background: var(--primary-glow); color: var(--primary);">
                                            <?= strtoupper($activity['action']) ?>
                                        </span>
                                        <?= htmlspecialchars($activity['description'] ?? '') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Most Active Users -->
    <div class="col-xl-4 mb-4">
        <div class="card-glass h-100">
            <div class="section-header">
                <h2 class="h5 mb-0">Top Users</h2>
            </div>

            <?php if (empty($topUsers)): ?>
                <div class="text-center py-5">
                    <p class="text-muted small">No user data available.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($topUsers as $user): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: var(--surface-hover);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" 
                                     style="width: 30px; height: 30px; font-size: 11px; background: var(--primary);">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="mb-0 small fw-bold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($user['name']) ?></p>
                                    <p class="mb-0 text-muted text-truncate" style="font-size: 10px; max-width: 140px;"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                            <span class="badge rounded-pill px-3" style="font-size: 10px; background: var(--primary-glow); color: var(--primary);">
                                <?= $user['activity_count'] ?? 0 ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0" style="background: linear-gradient(135deg, var(--primary-glow) 0%, var(--warning-glow) 100%); border: 1px solid rgba(99, 102, 241, 0.15) !important;">
            <div class="p-4">
                <h5 class="fw-bold mb-3">Quick Actions</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/documind/public/admin/users" class="btn btn-primary rounded-pill px-4">Manage Users</a>
                    <a href="/documind/public/admin/documents" class="btn btn-outline-primary bg-white rounded-pill px-4">All Documents</a>
                    <a href="/documind/public/admin/pending-documents" class="btn rounded-pill px-4" style="background: var(--warning); color: white;">
                        Review Queue <?php if (($pendingCount ?? 0) > 0): ?>(<?= $pendingCount ?>)<?php endif; ?>
                    </a>
                    <a href="/documind/public/admin/activity-log" class="btn btn-outline-secondary bg-white rounded-pill px-4">Audit Log</a>
                </div>
            </div>
        </div>
    </div>
</div>
