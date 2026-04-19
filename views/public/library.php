<!-- Public Library Hero -->
<div class="public-hero">
    <h1><i class="bi bi-book me-2"></i>Document Library</h1>
    <p>Browse our collection of AI-analysed documents. Sign in to unlock conversational AI features.</p>
</div>

<!-- Search & Filters -->
<div class="card-glass p-3 mb-4">
    <form method="GET" action="/documind/public/library" class="row g-2 align-items-center">
        <div class="col-md-7">
            <div class="input-group">
                <span class="input-group-text border-0" style="background: var(--surface-hover);"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control border-0" style="background: var(--surface-hover);"
                       placeholder="Search documents by name, topic, or keyword..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select border-0" style="background: var(--surface-hover);">
                <option value="">All Types</option>
                <option value="pdf" <?= ($typeFilter ?? '') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                <option value="docx" <?= ($typeFilter ?? '') === 'docx' ? 'selected' : '' ?>>DOCX</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 rounded-pill">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
    </form>
</div>

<!-- Results Count -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="text-muted small mb-0">
        <?php if (!empty($search)): ?>
            <strong><?= $total ?></strong> results for "<em><?= htmlspecialchars($search) ?></em>"
        <?php else: ?>
            <strong><?= $total ?></strong> documents available
        <?php endif; ?>
    </p>
    <?php if (!empty($search) || !empty($typeFilter)): ?>
        <a href="/documind/public/library" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 12px;">
            <i class="bi bi-x-lg me-1"></i>Clear Filters
        </a>
    <?php endif; ?>
</div>

<!-- Document Grid -->
<?php if (empty($documents)): ?>
    <div class="card-glass text-center py-5">
        <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
        <h5 class="fw-bold mb-2">No documents found</h5>
        <p class="text-muted small mb-0">
            <?php if (!empty($search)): ?>
                Try adjusting your search terms or filters.
            <?php else: ?>
                No approved documents are available yet.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="row g-3 stagger-enter">
        <?php foreach ($documents as $doc): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="/documind/public/library/<?= $doc['id'] ?>" class="text-decoration-none">
                    <div class="card-glass doc-card h-100" style="cursor: pointer;">
                        <!-- File type header -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2 rounded" style="background: <?= $doc['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>;">
                                    <i class="bi <?= $doc['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill' ?>" 
                                       style="color: <?= $doc['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;"></i>
                                </div>
                                <span class="badge rounded-pill text-uppercase" style="font-size: 9px; background: <?= $doc['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>; color: <?= $doc['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;">
                                    <?= $doc['file_type'] ?>
                                </span>
                            </div>
                            <span class="text-muted" style="font-size: 10px;">
                                <i class="bi bi-eye me-1"></i><?= $doc['view_count'] ?? 0 ?>
                            </span>
                        </div>

                        <!-- Title -->
                        <h6 class="fw-bold mb-2 text-truncate" style="color: var(--text-primary); font-size: 14px;">
                            <?= htmlspecialchars($doc['original_name']) ?>
                        </h6>

                        <!-- Summary preview -->
                        <p class="text-muted small mb-3" style="font-size: 12px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                            <?= htmlspecialchars(substr($doc['summary'] ?? 'No summary available', 0, 120)) ?>
                        </p>

                        <!-- Footer -->
                        <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top" style="border-color: var(--border) !important;">
                            <span class="text-muted" style="font-size: 10px;">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($doc['owner_name'] ?? 'Unknown') ?>
                            </span>
                            <span class="text-muted" style="font-size: 10px;">
                                <?= date('M d, Y', strtotime($doc['upload_date'])) ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link border-0 rounded-pill" href="/documind/public/library?page=<?= $currentPage - 1 ?>&q=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item mx-1">
                    <a class="page-link border-0 rounded-pill px-3 <?= $i === $currentPage ? 'text-white' : '' ?>" 
                       style="<?= $i === $currentPage ? 'background: var(--primary);' : 'background: var(--surface-hover);' ?>"
                       href="/documind/public/library?page=<?= $i ?>&q=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link border-0 rounded-pill" href="/documind/public/library?page=<?= $currentPage + 1 ?>&q=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Login CTA for guests -->
<?php if (empty($isLoggedIn)): ?>
    <div class="login-cta-banner mt-4 animate-fade-in">
        <i class="bi bi-chat-dots-fill fs-3 d-block mb-2" style="color: var(--primary);"></i>
        <h5 class="fw-bold mb-2">Unlock AI-Powered Analysis</h5>
        <p class="text-muted small mb-3">Sign in to chat with our AI about any document, get summaries, and more.</p>
        <a href="/documind/public/login" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Get Started
        </a>
    </div>
<?php endif; ?>
