<!-- Public Document Detail View -->
<div class="mb-3">
    <a href="/documind/public/library" class="text-decoration-none small" style="color: var(--primary);">
        <i class="bi bi-arrow-left me-1"></i> Back to Library
    </a>
</div>

<div class="row g-4">
    <!-- Document Info -->
    <div class="col-lg-8">
        <div class="card-glass animate-fade-in">
            <!-- Header -->
            <div class="d-flex align-items-start gap-3 mb-4">
                <div class="p-3 rounded-3 flex-shrink-0" style="background: <?= $document['file_type'] === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)' ?>;">
                    <i class="bi <?= $document['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill' ?> fs-3"
                       style="color: <?= $document['file_type'] === 'pdf' ? 'var(--danger)' : 'var(--primary)' ?>;"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h2 class="fw-bold mb-1" style="font-size: 22px;"><?= htmlspecialchars($document['original_name']) ?></h2>
                    <div class="d-flex flex-wrap gap-2 text-muted small">
                        <span><i class="bi bi-file-earmark me-1"></i><?= strtoupper($document['file_type']) ?></span>
                        <span>•</span>
                        <span><i class="bi bi-hdd me-1"></i><?= round(($document['file_size'] ?? 0) / 1024 / 1024, 2) ?> MB</span>
                        <span>•</span>
                        <span><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($document['upload_date'])) ?></span>
                        <span>•</span>
                        <span><i class="bi bi-eye me-1"></i><?= $document['view_count'] ?? 0 ?> views</span>
                    </div>
                </div>
            </div>

            <!-- AI Summary -->
            <?php if (!empty($document['summary'])): ?>
                <div class="mb-4">
                    <h5 class="fw-bold mb-3" style="font-size: 15px;">
                        <i class="bi bi-robot me-2" style="color: var(--primary);"></i>AI Summary
                    </h5>
                    <div class="p-3 rounded-3" style="background: var(--surface-hover); line-height: 1.7; font-size: 14px;">
                        <?= nl2br(htmlspecialchars($document['summary'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Keywords -->
            <?php if (!empty($keywords)): ?>
                <div class="mb-4">
                    <h5 class="fw-bold mb-3" style="font-size: 15px;">
                        <i class="bi bi-tags me-2" style="color: var(--info);"></i>Keywords
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($keywords as $kw): ?>
                            <span class="badge rounded-pill px-3 py-2" style="background: var(--primary-glow); color: var(--primary); font-size: 12px;">
                                <?= htmlspecialchars($kw) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Download -->
        <?php if (!empty($isLoggedIn)): ?>
            <div class="card-glass mb-3 text-center animate-fade-in">
                <a href="/documind/public/documents/<?= $document['id'] ?>/download" class="btn btn-primary w-100 rounded-pill">
                    <i class="bi bi-download me-2"></i>Download Document
                </a>
            </div>
        <?php endif; ?>

        <!-- AI Chat CTA -->
        <div class="card-glass animate-fade-in">
            <div class="text-center py-3">
                <i class="bi bi-chat-dots-fill fs-2 d-block mb-3" style="color: var(--primary);"></i>
                <h5 class="fw-bold mb-2" style="font-size: 16px;">Chat with this Document</h5>
                <p class="text-muted small mb-3">Ask questions and get AI-powered answers based on the document content.</p>
                
                <?php if (!empty($isLoggedIn)): ?>
                    <a href="/documind/public/documents/<?= $document['id'] ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-chat-dots me-1"></i> Open AI Chat
                    </a>
                <?php else: ?>
                    <a href="/documind/public/login" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Chat
                    </a>
                    <p class="text-muted mt-2 mb-0" style="font-size: 11px;">Free account required</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
