
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Shared with Me</h1>
        </div>
        <div class="col-md-6 text-end">
            <span class="badge bg-primary"><?php echo $total ?? 0; ?> Shared Documents</span>
        </div>
    </div>

    <?php if (empty($documents)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="bi bi-share2 fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">No Documents Shared</h5>
                <p class="text-muted">Documents shared with you will appear here</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($documents as $doc): ?>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars(substr($doc['original_name'], 0, 40)); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($doc['owner_name']); ?></small>
                                </div>
                                <span class="badge bg-secondary"><?php echo strtoupper($doc['file_type']); ?></span>
                            </div>
                            <p class="text-muted small mb-2"><?php echo round($doc['file_size'] / 1024 / 1024, 2); ?> MB</p>
                            <div class="mb-3">
                                <span class="badge bg-info">Permission: <?php echo ucfirst($doc['permission']); ?></span>
                            </div>
                            <div class="small text-muted mb-3">
                                Shared: <?php echo date('M d, Y', strtotime($doc['shared_at'])); ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="/documind/public/documents/<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-eye"></i> View Document
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="/documind/public/user/shared-with-me?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

