<div class="row mb-5 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Global Search Analysis</h2>
        <p class="text-secondary small">Found <span class="text-primary fw-bold"><?= count($documents) ?></span> result<?= count($documents) !== 1 ? 's' : '' ?> matching your query: <span class="text-dark">"<?= htmlspecialchars($query) ?>"</span></p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="/documind/public/documents" class="btn btn-outline-secondary px-4 rounded-pill fw-bold small border-opacity-25">
            <i class="bi bi-arrow-left me-1"></i> Back to Repository
        </a>
    </div>
</div>

<?php if (empty($documents)): ?>
    <div class="card-glass text-center py-5 border-dashed" style="border: 2px dashed var(--border) !important;">
        <div class="mb-4 opacity-25">
            <i class="bi bi-search" style="font-size: 64px;"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">No Matches Found</h4>
        <p class="text-secondary mb-4 mx-auto" style="max-width: 400px;">We couldn't find any documents matching those keywords. Try refining your search or browsing your vaults.</p>
        <a href="/documind/public/documents" class="btn btn-primary px-5 rounded-pill shadow-lg">Browse Global Vault</a>
    </div>
<?php else: ?>
    <div class="d-flex flex-column gap-4 mb-5">
        <?php foreach ($documents as $doc): ?>
            <div class="card-glass p-0 overflow-hidden hover-lift border-primary border-opacity-10">
                <div class="row g-0">
                    <div class="col-md-1 bg-primary-glow d-flex align-items-center justify-content-center py-4 border-end border-opacity-10">
                        <i class="bi <?php echo $doc['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill'; ?> fs-1 <?php echo $doc['file_type'] === 'pdf' ? 'text-danger' : 'text-primary'; ?>"></i>
                    </div>
                    <div class="col-md-11 p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <a href="/documind/public/documents/<?= $doc['id'] ?>" class="text-dark text-decoration-none hover-primary transition-all">
                                        <?= htmlspecialchars($doc['original_name']) ?>
                                    </a>
                                </h5>
                                <div class="d-flex align-items-center gap-3 opacity-50" style="font-size: 11px;">
                                    <span>Uploaded on <?= date('M d, Y', strtotime($doc['upload_date'])) ?></span>
                                    <span>•</span>
                                    <span><?= round($doc['file_size'] / 1024 / 1024, 2) ?> MB</span>
                                    <span>•</span>
                                    <span class="text-uppercase fw-bold text-primary"><?= $doc['file_type'] ?></span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="/documind/public/documents/<?= $doc['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">Analysis & Chat</a>
                            </div>
                        </div>

                        <?php if (!empty($doc['summary'])): ?>
                            <p class="text-secondary small mb-0 mt-3 border-top border-opacity-10 pt-3 opacity-75 line-clamp-2">
                                <i class="bi bi-stars text-primary me-2"></i><?= htmlspecialchars($doc['summary']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.hover-lift {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
}
.hover-lift:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
}
.hover-primary:hover {
    color: var(--primary) !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
