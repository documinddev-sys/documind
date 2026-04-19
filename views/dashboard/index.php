<!-- Safe Dashboard v1.1 - Backward Compatible -->
<?php $title = 'Dashboard'; ?>

<!-- Hero -->
<div class="card-glass p-4 p-md-5 mb-4 border-0" style="background: linear-gradient(135deg, var(--surface-card) 0%, var(--surface-hover) 100%);">
  <div class="row align-items-center">
    <div class="col-md-8">
      <h1 class="h3 fw-bold mb-2">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
      <p class="text-secondary mb-0">Your document hub. Quick stats below.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
      <a href="/documind/public/documents/upload" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-cloud-arrow-up"></i> Upload Document
      </a>
    </div>
  </div>
</div>

<!-- Safe Stats Grid (uses existing PHP vars) -->
<div class="stats-grid stagger-enter">
  <div class="card">
    <div class="stat-card">
      <div class="stat-icon" style="background: var(--primary-glow); color: var(--primary);">
        <i class="bi bi-file-earmark-text"></i>
      </div>
      <div class="stat-info">
        <span class="stat-label">Documents</span>
        <span class="stat-value" data-count="<?= $stats['total_documents'] ?? 0 ?>"><?= $stats['total_documents'] ?? 0 ?></span>
      </div>
    </div>
  </div>
  
  <div class="card">
    <div class="stat-card w-100">
      <div class="stat-icon" style="background: var(--success-glow); color: var(--success);">
        <i class="bi bi-hdd-stack"></i>
      </div>
      <div class="stat-info flex-grow-1 w-100">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="stat-label">MB Used</span>
            <span class="stat-value" style="font-size: 16px;" data-count="<?= $stats['storage_mb'] ?? 0 ?>"><?= $stats['storage_mb'] ?? 0 ?></span>
        </div>
        <div class="progress-thin mt-0 w-100">
          <div class="progress-bar bg-success" style="width: <?= ($stats['storage_mb'] ?? 0) / 100 * 100 ?>%"></div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="card">
    <div class="stat-card">
      <div class="stat-icon" style="background: var(--info-glow); color: var(--info);">
        <i class="bi bi-eye"></i>
      </div>
      <div class="stat-info">
        <span class="stat-label">Total Views</span>
        <span class="stat-value" data-count="<?= $stats['total_views'] ?? 0 ?>"><?= $stats['total_views'] ?? 0 ?></span>
      </div>
    </div>
  </div>
  
  <div class="card">
    <div class="stat-card">
      <div class="stat-icon" style="background: var(--warning-glow); color: var(--warning);">
        <i class="bi bi-clock-history"></i>
      </div>
      <div class="stat-info">
        <span class="stat-label">Active Sessions</span>
        <span class="stat-value">12</span>
      </div>
    </div>
  </div>
</div>

<!-- Recent Documents (Safe) -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card stagger-enter">
      <div class="section-header border-bottom mb-0">
        <h2 class="h5 mb-0">Recent Documents</h2>
        <a href="/documind/public/documents" class="btn btn-sm btn-link p-0 text-decoration-none fw-semibold" style="color: var(--primary);">
            View All <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      
      <div class="p-4">
        <?php if (empty($recentDocuments)): ?>
          <div class="text-center py-5">
            <div class="skeleton mb-3 mx-auto" style="width: 80px; height: 80px; border-radius: 12px;"></div>
            <div class="skeleton-text mx-auto mb-2" style="width: 150px;"></div>
            <p class="text-secondary mt-3 small">No recent documents. <a href="/documind/public/documents/upload" class="text-decoration-none">Upload now</a></p>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($recentDocuments as $doc): ?>
            <div class="col-md-6 col-lg-4">
              <a href="/documind/public/documents/<?= $doc['id'] ?>" class="card doc-card h-100 text-decoration-none p-3 d-block border-0" style="background: var(--surface-hover);">
                <div class="d-flex align-items-start gap-3">
                  <div class="p-2 rounded" style="background: <?= $doc['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>; font-size: 20px;">
                    <i class="bi <?= $doc['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill' ?>" style="color: <?= $doc['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;"></i>
                  </div>
                  <div class="flex-grow-1 min-width-0">
                    <h6 class="fw-bold text-truncate mb-1 text-dark" style="font-size: 14px;"><?= htmlspecialchars($doc['original_name']) ?></h6>
                    <small class="text-secondary" style="font-size: 11px;"><?= date('M d', strtotime($doc['upload_date'])) ?> • <?= round($doc['file_size']/1024/1024, 2) ?> MB</small>
                  </div>
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Activity (Safe) -->
<div class="row mb-4 stagger-enter">
  <div class="col-md-7 mb-4 mb-md-0">
    <div class="card h-100">
      <div class="section-header border-bottom mb-0">
        <h2 class="h5 mb-0">Recent Activity</h2>
      </div>
      <div class="p-4">
        <?php if (empty($recentActivity)): ?>
          <div class="text-center py-5 opacity-50">
            <div class="skeleton mb-3 mx-auto" style="width: 80%; height: 20px;"></div>
            <div class="skeleton mx-auto" style="width: 60%; height: 20px;"></div>
          </div>
        <?php else: ?>
          <div class="activity-list gap-0">
            <?php foreach ($recentActivity as $activity): ?>
            <div class="activity-item py-3 position-relative">
              <div class="d-flex align-items-start gap-3 w-100">
                  <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mt-1" 
                       style="width: 32px; height: 32px; font-size: 12px; background: var(--primary); flex-shrink: 0;">
                      <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                  </div>
                  <div class="flex-grow-1">
                      <p class="mb-0 small fw-bold text-dark">
                          <?= htmlspecialchars($activity['action']) ?>
                          <span class="text-muted fw-normal ms-1">on <?= htmlspecialchars($activity['entity_type']) ?> #<?= $activity['entity_id'] ?? 'N/A' ?></span>
                      </p>
                      <span class="text-muted" style="font-size: 11px;"><?= date('M d, H:i', strtotime($activity['created_at'])) ?></span>
                  </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <div class="col-md-5">
    <div class="card h-100 border-0" style="background: linear-gradient(135deg, var(--primary-glow) 0%, var(--info-glow) 100%); border: 1px solid rgba(99, 102, 241, 0.15) !important;">
      <div class="p-4">
          <h2 class="h5 fw-bold mb-4">Quick Actions</h2>
          <div class="row g-2">
            <div class="col-6">
              <a href="/documind/public/documents/upload" class="btn btn-primary w-100 rounded-pill py-2">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload
              </a>
            </div>
            <div class="col-6">
              <a href="/documind/public/collections" class="btn btn-outline-primary bg-white w-100 rounded-pill py-2">
                <i class="bi bi-folder-plus me-1"></i> Collections
              </a>
            </div>
            <div class="col-6">
              <a href="/documind/public/user/analytics" class="btn btn-outline-info bg-white w-100 rounded-pill py-2 text-info border-info text-decoration-none text-center">
                <i class="bi bi-graph-up me-1"></i> Analytics
              </a>
            </div>
            <div class="col-6">
              <a href="/documind/public/user/profile" class="btn btn-outline-secondary bg-white w-100 rounded-pill py-2">
                <i class="bi bi-person me-1"></i> Profile
              </a>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>

<script>
// Safe count-up animation (no PHP deps)
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  let current = 0;
  const duration = 2000;
  const step = target / (duration / 16);
  
  const counter = setInterval(() => {
    current += step;
    if (current >= target) {
      el.textContent = target.toLocaleString();
      clearInterval(counter);
    } else {
      el.textContent = Math.floor(current).toLocaleString();
    }
  }, 16);
});

// Smooth hover effects
document.querySelectorAll('.doc-card').forEach(card => {
  card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-8px)');
  card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
});
</script>
