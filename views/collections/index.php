<div class="row mb-5 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1">Knowledge Vaults</h2>
        <p class="text-secondary">Organize your research and projects into intelligent collections.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-lg rounded-pill" data-bs-toggle="modal" data-bs-target="#newCollectionModal">
            <i class="bi bi-folder-plus"></i>
            <span>Create New Vault</span>
        </button>
    </div>
</div>

<div class="row g-4 mb-5">
    <?php if (empty($collections)): ?>
        <div class="col-12">
            <div class="card-glass text-center py-5 border-dashed" style="border: 2px dashed var(--border) !important;">
                <div class="mb-4 opacity-25">
                    <i class="bi bi-folder2-open" style="font-size: 64px;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">No Vaults Found</h4>
                <p class="text-secondary mb-4 mx-auto" style="max-width: 400px;">Your workspace is currently unorganized. Group related documents together for better context management.</p>
                <button class="btn btn-outline-primary px-5 rounded-pill" data-bs-toggle="modal" data-bs-target="#newCollectionModal">Initialize Your First Vault</button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($collections as $coll): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-glass h-100 d-flex flex-column hover-lift">
                    <div class="p-3 d-flex align-items-start justify-content-between mb-3">
                        <div class="p-3 shadow-sm rounded-4 d-flex align-items-center justify-content-center" style="background-color: <?php echo htmlspecialchars($coll['color']); ?>; width: 56px; height: 56px;">
                            <i class="bi bi-folder2-fill fs-3" style="color: rgba(255,255,255,0.95);"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark card-glass border-opacity-10">
                                <li><a class="dropdown-item small" href="#"><i class="bi bi-pencil me-2"></i> Rename</a></li>
                                <li><a class="dropdown-item small text-danger" href="#"><i class="bi bi-trash3 me-2"></i> Archive</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="px-3 pb-3">
                        <h5 class="fw-bold text-dark mb-2">
                            <a href="/documind/public/collections/<?php echo $coll['id']; ?>" class="text-decoration-none text-inherit stretched-link">
                                <?php echo htmlspecialchars($coll['name']); ?>
                            </a>
                        </h5>
                        <p class="small text-secondary mb-4 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.5; min-height: 3em;">
                            <?php echo htmlspecialchars($coll['description'] ?? 'Workspace for organizing specific research materials and related project files.'); ?>
                        </p>
                    </div>

                    <div class="mt-auto px-3 pb-4 d-flex align-items-center justify-content-between border-top border-opacity-10 pt-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-files text-muted small"></i>
                            <span class="text-muted small" style="font-size: 11px;">Documents Managed</span>
                        </div>
                        <i class="bi bi-chevron-right text-primary small transition-move-right"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- New Collection Modal -->
<div class="modal fade" id="newCollectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-glass border-0 shadow-2xl" style="background: rgba(10, 15, 30, 0.98); border: 1px solid var(--border) !important;">
            <div class="modal-header border-bottom border-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-dark">Initialize New Knowledge Vault</h5>
                <button type="button" class="btn-close btn-close-white opacity-50" data-bs-dismiss="modal"></button>
            </div>
            <form id="new-collection-form">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small text-secondary fw-bold text-uppercase" style="letter-spacing: 1px;">Vault Identity</label>
                        <input type="text" class="form-control border-secondary border-opacity-25 py-3 text-dark shadow-sm" id="coll_name" name="name" placeholder="e.g. Legal Research 2024" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-secondary fw-bold text-uppercase" style="letter-spacing: 1px;">Description & Scope</label>
                        <textarea class="form-control border-secondary border-opacity-25 py-3 text-dark shadow-sm" id="coll_desc" name="description" rows="3" placeholder="Define the primary focus and classification standard for this collection..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold text-uppercase" style="letter-spacing: 1px;">Visual Classifier (Tag Color)</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <label class="color-swatch-label">
                                <input type="radio" name="color" value="#3b82f6" checked class="d-none">
                                <span class="swatch" style="background: #3b82f6;"></span>
                            </label>
                            <label class="color-swatch-label">
                                <input type="radio" name="color" value="#10b981" class="d-none">
                                <span class="swatch" style="background: #10b981;"></span>
                            </label>
                            <label class="color-swatch-label">
                                <input type="radio" name="color" value="#ef4444" class="d-none">
                                <span class="swatch" style="background: #ef4444;"></span>
                            </label>
                            <label class="color-swatch-label">
                                <input type="radio" name="color" value="#f59e0b" class="d-none">
                                <span class="swatch" style="background: #f59e0b;"></span>
                            </label>
                            <label class="color-swatch-label">
                                <input type="radio" name="color" value="#8b5cf6" class="d-none">
                                <span class="swatch" style="background: #8b5cf6;"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-opacity-10 py-3 bg-opacity-50">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none small px-4" data-bs-dismiss="modal">Cancel Operation</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-lg shadow-primary/20">Create Vault</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.3) !important;
}
.text-inherit { color: inherit; }
.transition-move-right { transition: transform 0.3s ease; }
.hover-lift:hover .transition-move-right { transform: translateX(5px); }

.color-swatch-label input:checked + .swatch {
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px var(--primary);
    transform: scale(1.1);
}
.swatch {
    display: block;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}
</style>

<script>
document.getElementById('new-collection-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('coll_name').value;
    const description = document.getElementById('coll_desc').value;
    const color = document.querySelector('input[name="color"]:checked').value;

    try {
        const response = await fetch('/documind/public/collections/create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({name, description, color})
        });
        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            DocuMindUI.showToast(data.error || 'Operation Failed', 'error');
        }
    } catch (error) {
        DocuMindUI.showToast('Network synchronization failed', 'error');
    }
});
</script>
