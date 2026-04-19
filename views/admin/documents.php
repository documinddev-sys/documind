<!-- Admin Documents with Status Filtering -->
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1">Document Registry</h2>
        <p class="text-muted small">Global overview of all documents in the system.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="card-glass d-inline-block px-4 py-2">
            <span class="fw-bold" style="color: var(--primary);"><?= $total ?? 0 ?></span>
            <span class="text-muted ms-1 small">Total Files</span>
        </div>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="/documind/public/admin/documents" 
       class="btn btn-sm rounded-pill px-3 <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline-secondary' ?>">
        All
    </a>
    <a href="/documind/public/admin/documents?status=pending" 
       class="btn btn-sm rounded-pill px-3 <?= ($statusFilter ?? '') === 'pending' ? '' : 'btn-outline-secondary' ?>"
       style="<?= ($statusFilter ?? '') === 'pending' ? 'background: var(--warning); color: white; border: none;' : '' ?>">
        <i class="bi bi-clock me-1"></i>Pending
        <?php if (($pendingCount ?? 0) > 0): ?>
            <span class="badge rounded-pill ms-1" style="background: rgba(255,255,255,0.3); font-size: 10px;"><?= $pendingCount ?></span>
        <?php endif; ?>
    </a>
    <a href="/documind/public/admin/documents?status=approved" 
       class="btn btn-sm rounded-pill px-3 <?= ($statusFilter ?? '') === 'approved' ? '' : 'btn-outline-secondary' ?>"
       style="<?= ($statusFilter ?? '') === 'approved' ? 'background: var(--success); color: white; border: none;' : '' ?>">
        <i class="bi bi-check-circle me-1"></i>Approved
        <span class="badge rounded-pill ms-1" style="background: rgba(0,0,0,0.1); font-size: 10px;"><?= $approvedCount ?? 0 ?></span>
    </a>
    <a href="/documind/public/admin/documents?status=rejected" 
       class="btn btn-sm rounded-pill px-3 <?= ($statusFilter ?? '') === 'rejected' ? '' : 'btn-outline-secondary' ?>"
       style="<?= ($statusFilter ?? '') === 'rejected' ? 'background: var(--danger); color: white; border: none;' : '' ?>">
        <i class="bi bi-x-circle me-1"></i>Rejected
        <span class="badge rounded-pill ms-1" style="background: rgba(0,0,0,0.1); font-size: 10px;"><?= $rejectedCount ?? 0 ?></span>
    </a>
</div>

<div class="card-glass">
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0">
            <thead style="border-bottom: 1px solid var(--border);">
                <tr style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                    <th class="ps-0">File Detail</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Size / Type</th>
                    <th>Engagement</th>
                    <th class="text-end pe-0">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No documents found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td class="ps-0">
                                <div class="d-flex align-items-center gap-3 py-2">
                                    <div class="p-2 rounded" style="background: <?= $doc['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>;">
                                        <i class="bi <?= $doc['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill' ?>" 
                                           style="color: <?= $doc['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="mb-0 fw-bold text-truncate small" style="max-width: 250px;"><?= htmlspecialchars($doc['original_name']) ?></p>
                                        <span class="text-muted" style="font-size: 11px;">ID: #<?= $doc['id'] ?> • <?= date('M d, Y', strtotime($doc['upload_date'])) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 24px; height: 24px; font-size: 10px; background: var(--text-secondary);">
                                        <?= strtoupper(substr($doc['owner_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <span class="small fw-medium"><?= htmlspecialchars($doc['owner_name'] ?? 'Unknown') ?></span>
                                </div>
                            </td>
                            <td>
                                <?php
                                    $statusClass = match($doc['status'] ?? 'pending') {
                                        'approved' => 'badge-status-approved',
                                        'rejected' => 'badge-status-rejected',
                                        default => 'badge-status-pending',
                                    };
                                    $statusIcon = match($doc['status'] ?? 'pending') {
                                        'approved' => 'bi-check-circle',
                                        'rejected' => 'bi-x-circle',
                                        default => 'bi-clock',
                                    };
                                ?>
                                <span class="badge rounded-pill px-2 py-1 <?= $statusClass ?>" style="font-size: 11px;">
                                    <i class="bi <?= $statusIcon ?> me-1"></i><?= ucfirst($doc['status'] ?? 'pending') ?>
                                </span>
                                <?php if (!empty($doc['is_public'])): ?>
                                    <span class="badge rounded-pill px-2 py-1 ms-1" style="font-size: 9px; background: var(--info-glow); color: var(--info);">
                                        <i class="bi bi-globe me-1"></i>Public
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-uppercase px-2 mb-1" style="font-size: 9px; background: var(--info-glow); color: var(--info);"><?= $doc['file_type'] ?></span>
                                <div class="text-muted" style="font-size: 10px;"><?= round(($doc['file_size'] ?? 0) / 1024 / 1024, 2) ?> MB</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-eye text-muted" style="font-size: 12px;"></i>
                                    <span class="small"><?= $doc['view_count'] ?? 0 ?> views</span>
                                </div>
                            </td>
                            <td class="text-end pe-0">
                                <div class="d-flex gap-1 justify-content-end">
                                    <?php if (($doc['status'] ?? 'pending') === 'pending'): ?>
                                        <button class="btn btn-sm rounded-pill px-2 approve-inline-btn" data-doc-id="<?= $doc['id'] ?>" title="Approve"
                                                style="background: var(--success-glow); color: var(--success); font-size: 12px;">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm rounded-pill px-2 reject-inline-btn" data-doc-id="<?= $doc['id'] ?>" title="Reject"
                                                style="background: var(--danger-glow); color: var(--danger); font-size: 12px;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php elseif (($doc['status'] ?? '') === 'approved'): ?>
                                        <button class="btn btn-sm rounded-pill px-2 toggle-public-btn" 
                                                data-doc-id="<?= $doc['id'] ?>" 
                                                title="<?= empty($doc['is_public']) ? 'Make Public' : 'Remove from Public' ?>"
                                                style="background: <?= empty($doc['is_public']) ? 'var(--surface-hover)' : 'var(--info-glow)' ?>; 
                                                       color: <?= empty($doc['is_public']) ? 'var(--text-secondary)' : 'var(--info)' ?>; 
                                                       font-size: 12px;">
                                            <i class="bi <?= empty($doc['is_public']) ? 'bi-globe' : 'bi-globe2' ?>"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-2 delete-doc-btn" data-doc-id="<?= $doc['id'] ?>" title="Delete" style="font-size: 12px;">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item mx-1">
                    <a class="page-link border-0 card-glass <?= $i === $currentPage ? 'text-white' : '' ?>" 
                       style="<?= $i === $currentPage ? 'background: var(--primary);' : '' ?>"
                       href="/documind/public/admin/documents?page=<?= $i ?>&status=<?= urlencode($statusFilter ?? '') ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
// Delete document
document.querySelectorAll('.delete-doc-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const docId = btn.dataset.docId;
        const result = await Swal.fire({
            title: 'Delete Document?',
            text: "Are you sure you want to delete this document? This action is irreversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/documind/public/admin/delete-document', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({document_id: parseInt(docId)})
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', data.error || 'Failed to delete', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error occurred', 'error');
            }
        }
    });
});

// Inline approve
document.querySelectorAll('.approve-inline-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const docId = btn.dataset.docId;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const response = await fetch('/documind/public/admin/approve-document', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ document_id: parseInt(docId), is_public: true })
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                Swal.fire('Error', data.error || 'Failed to approve document', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i>';
            }
        } catch (err) {
            Swal.fire('Error', 'Network error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        }
    });
});

// Inline reject
document.querySelectorAll('.reject-inline-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const docId = btn.dataset.docId;
        const result = await Swal.fire({
            title: 'Reject Document?',
            text: "Are you sure you want to reject this document?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject'
        });

        if (!result.isConfirmed) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const response = await fetch('/documind/public/admin/reject-document', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ document_id: parseInt(docId), reason: '' })
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                Swal.fire('Error', data.error || 'Failed to reject document', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Network error occurred', 'error');
        }
    });
});

// Toggle Public status for approved docs
document.querySelectorAll('.toggle-public-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const docId = btn.dataset.docId;
        const currentIcon = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:12px;height:12px;"></span>';
        
        try {
            const response = await fetch('/documind/public/admin/toggle-public', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ document_id: parseInt(docId) })
            });
            const data = await response.json();
            
            if (data.success) {
                // simple reload to update badges and UI
                location.reload();
            } else {
                Swal.fire('Error', data.error || 'Failed to toggle status', 'error');
                btn.disabled = false;
                btn.innerHTML = currentIcon;
            }
        } catch (err) {
            Swal.fire('Error', 'Network error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = currentIcon;
        }
    });
});
</script>
