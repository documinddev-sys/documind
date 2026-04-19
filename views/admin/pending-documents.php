<!-- Admin Pending Documents Queue -->
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1">Review Queue</h2>
        <p class="text-muted small">Approve or reject uploaded documents before they appear in the system.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="card-glass d-inline-block px-4 py-2">
            <span class="fw-bold" style="color: var(--warning);"><?= $total ?? 0 ?></span>
            <span class="text-muted ms-1 small">documents pending review</span>
        </div>
    </div>
</div>

<?php if (empty($documents)): ?>
    <div class="card-glass text-center py-5">
        <i class="bi bi-check2-all fs-1 d-block mb-3" style="color: var(--success);"></i>
        <h5 class="fw-bold mb-2">All caught up! <i class="fa-solid fa-circle-check text-success ms-2"></i></h5>
        <p class="text-muted small mb-0">No documents are waiting for review.</p>
    </div>
<?php else: ?>
    <div class="row g-3 stagger-enter">
        <?php foreach ($documents as $doc): ?>
            <div class="col-12" id="pending-doc-<?= $doc['id'] ?>">
                <div class="card-glass">
                    <div class="d-flex flex-wrap align-items-start gap-3">
                        <!-- Document Info -->
                        <div class="p-2 rounded flex-shrink-0" style="background: <?= $doc['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>;">
                            <i class="bi <?= $doc['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill' ?> fs-4"
                               style="color: <?= $doc['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($doc['original_name']) ?></h6>
                            <div class="d-flex flex-wrap gap-2 text-muted mb-2" style="font-size: 12px;">
                                <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($doc['owner_name'] ?? 'Unknown') ?></span>
                                <span>•</span>
                                <span><i class="bi bi-calendar me-1"></i><?= date('M d, Y H:i', strtotime($doc['upload_date'])) ?></span>
                                <span>•</span>
                                <span><i class="bi bi-hdd me-1"></i><?= round(($doc['file_size'] ?? 0) / 1024 / 1024, 2) ?> MB</span>
                                <span>•</span>
                                <span class="badge text-uppercase" style="font-size: 9px; background: <?= $doc['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>; color: <?= $doc['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;"><?= $doc['file_type'] ?></span>
                            </div>
                            
                            <!-- Text preview -->
                            <?php if (!empty($doc['full_text'])): ?>
                                <details class="mb-2">
                                    <summary class="small text-muted" style="cursor: pointer; font-size: 12px;">
                                        <i class="bi bi-eye me-1"></i>Preview extracted text
                                    </summary>
                                    <div class="mt-2 p-3 rounded-3 small" style="background: var(--surface-hover); max-height: 200px; overflow-y: auto; font-size: 12px; line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars(substr($doc['full_text'], 0, 500))) ?>
                                        <?php if (strlen($doc['full_text'] ?? '') > 500): ?>
                                            <span class="text-muted">... (truncated)</span>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex flex-column gap-2 flex-shrink-0">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" id="public-<?= $doc['id'] ?>" checked>
                                <label class="form-check-label small" for="public-<?= $doc['id'] ?>">Make Public</label>
                            </div>
                            <button class="btn btn-sm rounded-pill px-3 approve-btn" 
                                    data-doc-id="<?= $doc['id'] ?>"
                                    style="background: var(--success); color: white; font-size: 13px;">
                                <i class="bi bi-check-lg me-1"></i>Approve
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 reject-btn" 
                                    data-doc-id="<?= $doc['id'] ?>" style="font-size: 13px;">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item mx-1">
                    <a class="page-link border-0 rounded-pill px-3 <?= $i === $currentPage ? 'text-white' : '' ?>" 
                       style="<?= $i === $currentPage ? 'background: var(--primary);' : 'background: var(--surface-hover);' ?>"
                       href="/documind/public/admin/pending-documents?page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: var(--radius);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Reject Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejectReason">Reason (optional)</label>
                    <textarea id="rejectReason" class="form-control" rows="3" placeholder="Explain why this document is being rejected..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm rounded-pill px-4" id="confirmReject" style="background: var(--danger); color: white;">
                    <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let rejectDocId = null;

// Approve document
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const docId = btn.dataset.docId;
        const isPublic = document.getElementById(`public-${docId}`)?.checked ?? true;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

        try {
            const response = await fetch('/documind/public/admin/approve-document', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ document_id: parseInt(docId), is_public: isPublic })
            });
            const data = await response.json();

            if (data.success) {
                const card = document.getElementById(`pending-doc-${docId}`);
                card.style.opacity = '0';
                card.style.transform = 'translateX(20px)';
                card.style.transition = 'all 0.3s ease';
                setTimeout(() => card.remove(), 300);
                window.showToast('Document approved successfully', 'success');
            } else {
                window.showToast(data.error || 'Failed to approve', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve';
            }
        } catch (err) {
            window.showToast('Network error', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve';
        }
    });
});

// Reject document
document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        rejectDocId = btn.dataset.docId;
        document.getElementById('rejectReason').value = '';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    });
});

document.getElementById('confirmReject')?.addEventListener('click', async () => {
    if (!rejectDocId) return;

    const reason = document.getElementById('rejectReason').value.trim();
    const btn = document.getElementById('confirmReject');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...';

    try {
        const response = await fetch('/documind/public/admin/reject-document', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ document_id: parseInt(rejectDocId), reason: reason })
        });
        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('rejectModal'))?.hide();
            const card = document.getElementById(`pending-doc-${rejectDocId}`);
            card.style.opacity = '0';
            card.style.transform = 'translateX(-20px)';
            card.style.transition = 'all 0.3s ease';
            setTimeout(() => card.remove(), 300);
            window.showToast('Document rejected', 'warning');
        } else {
            window.showToast(data.error || 'Failed to reject', 'error');
        }
    } catch (err) {
        window.showToast('Network error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-lg me-1"></i>Confirm Rejection';
        rejectDocId = null;
    }
});
</script>
