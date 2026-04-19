<div class="row mb-5 align-items-center">
    <div class="col-md-8">
        <div class="d-flex align-items-center gap-4">
            <div class="p-4 shadow-lg rounded-4 d-flex align-items-center justify-content-center" style="background-color: <?php echo htmlspecialchars($collection['color']); ?>; width: 64px; height: 64px;">
                <i class="bi bi-folder2-fill fs-1 text-white opacity-90"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($collection['name']); ?></h2>
                <p class="text-secondary small mb-0"><?php echo htmlspecialchars($collection['description'] ?? 'Curated ecosystem for related document synchronization.'); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-md-end">
        <div class="d-inline-flex align-items-center gap-3">
            <div class="card-glass px-3 py-1 border-primary border-opacity-10 bg-primary-glow">
                <span class="text-primary fw-bold"><?php echo $total ?? 0; ?></span>
                <span class="text-secondary small ms-1">Resources</span>
            </div>
            <button class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-lg rounded-pill" data-bs-toggle="modal" data-bs-target="#addDocumentsModal">
                <i class="bi bi-plus-lg"></i>
                <span>Inject Resources</span>
            </button>
        </div>
    </div>
</div>

<?php if (empty($documents)): ?>
    <div class="card-glass text-center py-5 border-dashed" style="border: 2px dashed var(--border) !important;">
        <div class="mb-4 opacity-25">
            <i class="bi bi-inbox" style="font-size: 64px;"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">Vault is Unpopulated</h4>
        <p class="text-secondary mb-4 mx-auto" style="max-width: 400px;">This collection doesn't contain any documents yet. Synchronize assets from your global repository.</p>
        <button class="btn btn-outline-primary px-5 rounded-pill" data-bs-toggle="modal" data-bs-target="#addDocumentsModal">Link Documents</button>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($documents as $doc): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card-glass h-100 d-flex flex-column hover-lift">
                    <div class="p-3 d-flex align-items-start justify-content-between mb-2">
                        <div class="p-2 rounded-3 bg-opacity-10 <?php echo $doc['file_type'] === 'pdf' ? 'bg-danger' : 'bg-primary'; ?>" style="font-size: 20px;">
                            <i class="bi <?php echo $doc['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill'; ?> <?php echo $doc['file_type'] === 'pdf' ? 'text-danger' : 'text-primary'; ?>"></i>
                        </div>
                        <span class="badge bg-opacity-10 <?php echo $doc['file_type'] === 'pdf' ? 'bg-danger text-danger' : 'bg-primary text-primary'; ?> px-2 py-1 text-uppercase" style="font-size: 9px;">
                            <?php echo $doc['file_type']; ?>
                        </span>
                    </div>

                    <div class="px-3 pb-2">
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?php echo htmlspecialchars($doc['original_name']); ?>">
                            <?php echo htmlspecialchars($doc['original_name']); ?>
                        </h6>
                        <div class="d-flex align-items-center gap-2 opacity-50 mb-3" style="font-size: 10px;">
                            <span><?php echo round($doc['file_size'] / 1024 / 1024, 2); ?> MB</span>
                            <span>•</span>
                            <span><?php echo date('M d, Y', strtotime($doc['upload_date'])); ?></span>
                        </div>
                    </div>

                    <div class="mt-auto px-3 pb-3 d-flex gap-2 pt-2 border-top border-opacity-10">
                        <a href="/documind/public/documents/<?php echo $doc['id']; ?>" class="btn btn-outline-primary flex-grow-1 btn-sm rounded-pill fw-bold border-opacity-25" style="font-size: 11px;">
                            Open Analysis
                        </a>
                        <button class="btn btn-outline-danger btn-sm px-2 rounded-circle remove-doc-btn border-0" 
                                data-collection-id="<?php echo $collection['id']; ?>" 
                                data-doc-id="<?php echo $doc['id']; ?>"
                                title="Unlink from Vault">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item mx-1">
                        <a class="page-link border-0 card-glass <?php echo $i === $currentPage ? 'bg-primary text-white' : 'text-secondary'; ?>" href="/documind/public/collections/<?php echo $collection['id']; ?>?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<style>
.hover-lift {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
}
</style>

<script>
function attachRemoveListeners() {
    document.querySelectorAll('.remove-doc-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const collectionId = e.currentTarget.dataset.collectionId;
            const docId = e.currentTarget.dataset.docId;

            const result = await Swal.fire({
                title: 'Sever Link?',
                text: "Are you sure you want to remove this document from this collection vault?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Sever Link'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/documind/public/collections/remove-document', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({collection_id: collectionId, document_id: docId})
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', data.error || 'Failed to remove document', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Network error occurred', 'error');
                }
            }
        });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachRemoveListeners);
} else {
    attachRemoveListeners();
}
</script>

<!-- Add Documents Modal -->
<div class="modal fade" id="addDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content card-glass border-0 shadow-2xl" style="background: rgba(10, 15, 30, 0.98); border: 1px solid var(--border) !important;">
            <div class="modal-header border-bottom border-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-dark">Link Repository Assets</h5>
                <button type="button" class="btn-close btn-close-white opacity-50" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small mb-4">Select the documents from your global vault to synchronize with this collection.</p>
                <div id="documentsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Syncing repository...</span>
                    </div>
                </div>
                <div id="documentsList" class="d-flex flex-column gap-2" style="max-height: 400px; overflow-y: auto; display: none;">
                    <!-- Documents will be loaded here -->
                </div>
            </div>
            <div class="modal-footer border-top border-opacity-10 py-3">
                <button type="button" class="btn btn-link text-secondary text-decoration-none small px-4" data-bs-dismiss="modal">Abort</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-lg" id="addDocumentsBtn">Sync Selected Assets</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addDocumentsModal')?.addEventListener('show.bs.modal', async () => {
    const docsList = document.getElementById('documentsList');
    const loading = document.getElementById('documentsLoading');
    
    try {
        const response = await fetch('/documind/public/documents/list-json');
        const data = await response.json();
        
        if (data.success && data.documents && data.documents.length > 0) {
            let html = '';
            data.documents.forEach(doc => {
                html += `
                    <label class="d-flex align-items-center gap-3 p-3 rounded-3 card-glass border-opacity-10 transition-all hover:bg-white/5 cursor-pointer">
                        <div class="form-check m-0">
                            <input class="form-check-input doc-checkbox" type="checkbox" value="${doc.id}">
                        </div>
                        <div class="p-2 rounded bg-opacity-10 ${doc.file_type === 'pdf' ? 'bg-danger' : 'bg-primary'} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi ${doc.file_type === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill'} ${doc.file_type === 'pdf' ? 'text-danger' : 'text-primary'}"></i>
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <p class="mb-0 fw-bold small text-dark text-truncate">${doc.original_name}</p>
                            <span class="text-muted d-block" style="font-size: 10px;">${doc.file_type.toUpperCase()} • ${(doc.file_size / 1024 / 1024).toFixed(2)} MB</span>
                        </div>
                    </label>
                `;
            });
            docsList.innerHTML = html;
        } else {
            docsList.innerHTML = '<div class="text-center py-4 opacity-50 small">Repository is currently empty</div>';
        }
    } catch (error) {
        docsList.innerHTML = '<div class="text-center py-4 text-danger small">Repository synchronization failed</div>';
    }
    
    loading.style.display = 'none';
    docsList.style.display = 'flex';
});

document.getElementById('addDocumentsBtn')?.addEventListener('click', async () => {
    const selectedDocs = Array.from(document.querySelectorAll('.doc-checkbox:checked')).map(cb => parseInt(cb.value));
    
    if (selectedDocs.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Selection',
            text: 'Please select at least one asset to link',
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }

    const collectionId = <?php echo $collection['id']; ?>;
    let added = 0;

    for (const docId of selectedDocs) {
        try {
            const response = await fetch('/documind/public/collections/add-document', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({collection_id: collectionId, document_id: docId})
            });
            const data = await response.json();
            if (data.success) added++;
        } catch (error) {
            console.error('Asset linking error:', error);
        }
    }

    if (added > 0) {
        location.reload();
    } else {
        Swal.fire('Error', 'Asset synchronization failed completely', 'error');
    }
});
</script>
