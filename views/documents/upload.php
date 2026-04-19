<div class="row justify-content-center pt-4">
    <div class="col-lg-8 col-xl-7">
        <div class="card-glass p-4 p-md-5 animate-fade-in">
            <div class="mb-4">
                <h2 class="fw-bold mb-2">Upload Document</h2>
                <p class="text-muted small">Upload a PDF or DOCX document for AI analysis. Maximum file size: <span class="fw-bold" style="color: var(--primary);"><?= $maxSize ?>MB</span></p>
                
                <?php if (!($isAdmin ?? false)): ?>
                    <!-- Upload quota display -->
                    <div class="d-flex align-items-center gap-2 mt-2 p-2 rounded-3" style="background: var(--surface-hover); font-size: 13px;">
                        <i class="bi bi-cloud-arrow-up" style="color: var(--primary);"></i>
                        <span class="text-muted">Upload quota:</span>
                        <strong style="color: var(--primary);"><?= $remaining ?? 0 ?></strong>
                        <span class="text-muted">of <?= $uploadLimit ?? 10 ?> remaining</span>
                        <?php if (($remaining ?? 0) <= 0): ?>
                            <span class="badge badge-status-rejected ms-2" style="font-size: 10px;">Limit reached</span>
                        <?php elseif (($remaining ?? 0) <= 3): ?>
                            <span class="badge badge-status-pending ms-2" style="font-size: 10px;">Almost full</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!($isAdmin ?? false) && ($remaining ?? 0) <= 0): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    You've reached your upload limit of <strong><?= $uploadLimit ?? 10 ?></strong> documents. Contact an administrator to increase your quota.
                </div>
            <?php else: ?>
                <form id="uploadForm" enctype="multipart/form-data">
                    <!-- Drop Zone -->
                    <div class="drop-zone card-glass d-flex flex-column align-items-center justify-content-center text-center p-5 mb-4" 
                         id="dropZone" 
                         style="border: 2px dashed var(--border-strong) !important; cursor: pointer; transition: all 0.3s ease;">
                        <div class="mb-3 p-4 rounded-circle" style="background: var(--primary-glow);">
                            <i class="bi bi-cloud-arrow-up-fill fs-1" style="color: var(--primary);"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Drag & Drop Your File</h5>
                        <p class="text-muted small mb-3">or click here to browse files</p>
                        <input type="file" id="fileInput" name="document" accept=".pdf,.docx" class="d-none" required>
                        <div class="d-flex gap-2">
                            <span class="badge rounded-pill px-2 py-1" style="font-size: 10px; background: var(--danger-glow); color: var(--danger);">PDF</span>
                            <span class="badge rounded-pill px-2 py-1" style="font-size: 10px; background: var(--primary-glow); color: var(--primary);">DOCX</span>
                        </div>
                    </div>

                    <!-- File Selection Info -->
                    <div id="fileInfo" class="d-none card-glass p-3 mb-4" style="background: var(--primary-glow); border: 1px solid rgba(99,102,241,0.1);">
                        <div class="d-flex align-items-center gap-3">
                            <div id="fileTypeIcon" class="p-2 rounded text-white" style="background: var(--primary);">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                            <div class="overflow-hidden">
                                <p id="fileName" class="mb-0 fw-bold small text-truncate"></p>
                                <span id="fileSize" class="text-muted" style="font-size: 10px;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="progressContainer" class="d-none mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Uploading...</span>
                            <span id="progressPercent" class="fw-bold small" style="color: var(--primary);">0%</span>
                        </div>
                        <div class="progress-thin">
                            <div id="progressBar" class="progress-bar" style="width: 0%"></div>
                        </div>
                        <p class="text-muted mt-2" style="font-size: 11px;">
                            <?php if (!($isAdmin ?? false)): ?>
                                <i class="bi bi-info-circle me-1"></i>Your document will be available after admin approval.
                            <?php else: ?>
                                <i class="bi bi-info-circle me-1"></i>Admin uploads are auto-approved with AI processing.
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Error Messages -->
                    <div id="errorContainer" class="d-none alert alert-danger p-3 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                            <span class="fw-bold small">Upload Failed</span>
                        </div>
                        <ul id="errorList" class="list-unstyled mb-0 small" style="opacity: 0.8;"></ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3">
                        <button type="submit" id="submitBtn" class="btn btn-primary flex-grow-1 py-3 fw-bold rounded-pill disabled" disabled>
                            <span>Confirm & Upload</span>
                        </button>
                        <a href="/documind/public/documents" class="btn btn-outline-secondary px-4 py-3 rounded-pill fw-bold">
                            Back
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.drop-zone:hover, .drop-zone.dragging {
    background: var(--surface-hover);
    border-color: var(--primary) !important;
    transform: scale(0.998);
}
</style>

<script>
<?php if (($isAdmin ?? false) || ($remaining ?? 0) > 0): ?>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const fileInfo = document.getElementById('fileInfo');
const progressContainer = document.getElementById('progressContainer');
const errorContainer = document.getElementById('errorContainer');
const submitBtn = document.getElementById('submitBtn');

dropZone.addEventListener('click', () => fileInput.click());

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => {
    dropZone.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); }, false);
});

['dragenter', 'dragover'].forEach(e => {
    dropZone.addEventListener(e, () => dropZone.classList.add('dragging'));
});

['dragleave', 'drop'].forEach(e => {
    dropZone.addEventListener(e, () => dropZone.classList.remove('dragging'));
});

dropZone.addEventListener('drop', (e) => {
    fileInput.files = e.dataTransfer.files;
    handleFileSelect();
});

fileInput.addEventListener('change', handleFileSelect);

function handleFileSelect() {
    const file = fileInput.files[0];
    if (!file) return;

    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

    const iconWrapper = document.getElementById('fileTypeIcon');
    if (file.name.toLowerCase().endsWith('.pdf')) {
        iconWrapper.style.background = 'var(--danger)';
    } else {
        iconWrapper.style.background = 'var(--primary)';
    }

    fileInfo.classList.remove('d-none');
    errorContainer.classList.add('d-none');
    submitBtn.disabled = false;
    submitBtn.classList.remove('disabled');
}

uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!fileInput.files[0]) {
        showError(['Please select a valid document file']);
        return;
    }

    const formData = new FormData(uploadForm);
    submitBtn.disabled = true;
    submitBtn.classList.add('disabled');
    progressContainer.classList.remove('d-none');
    errorContainer.classList.add('d-none');

    try {
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                document.getElementById('progressPercent').textContent = pct + '%';
                document.getElementById('progressBar').style.width = pct + '%';
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200 || xhr.status === 201) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    showError(response.errors || [response.error]);
                    resetBtn();
                }
            } else {
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError(response.errors || [response.error || 'Server error']);
                } catch(err) {
                    showError(['An unexpected error occurred']);
                }
                resetBtn();
            }
        });

        xhr.addEventListener('error', () => {
            showError(['Check your internet connection']);
            resetBtn();
        });

        xhr.open('POST', '/documind/public/documents/upload', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    } catch (error) {
        showError([error.message]);
        resetBtn();
    }
});

function resetBtn() {
    progressContainer.classList.add('d-none');
    submitBtn.disabled = false;
    submitBtn.classList.remove('disabled');
}

function showError(errors) {
    const errorList = document.getElementById('errorList');
    errorList.innerHTML = errors.map(e => `<li><i class="bi bi-dot me-1"></i>${e}</li>`).join('');
    errorContainer.classList.remove('d-none');
}
<?php endif; ?>
</script>
