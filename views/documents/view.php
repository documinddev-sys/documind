<div class="row g-4">
    <!-- Left: Document Context & Metadata -->
    <div class="col-lg-4">
        <div class="card shadow-sm p-4 sticky-top" style="top: 100px; z-index: 10;">
            <div class="text-center mb-4">
                <div class="p-4 d-inline-block rounded-4 bg-opacity-10 <?php echo $document['file_type'] === 'pdf' ? 'bg-danger' : 'bg-primary'; ?> mb-3 shadow-sm">
                    <i class="bi <?php echo $document['file_type'] === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill'; ?> <?php echo $document['file_type'] === 'pdf' ? 'text-danger' : 'text-primary'; ?> fs-1"></i>
                </div>
                <h3 class="h5 fw-bold text-dark text-truncate mb-1" title="<?php echo htmlspecialchars($document['original_name']); ?>">
                    <?php echo htmlspecialchars($document['original_name']); ?>
                </h3>
                <span class="badge bg-opacity-10 bg-secondary text-secondary px-2 py-1 text-uppercase" style="font-size: 10px;">
                    <?php echo $document['file_type']; ?>
                </span>
                <?php
                    $statusClass = match($document['status'] ?? 'approved') {
                        'approved' => 'badge-status-approved',
                        'rejected' => 'badge-status-rejected',
                        default => 'badge-status-pending',
                    };
                ?>
                <span class="badge rounded-pill px-2 py-1 ms-1 <?= $statusClass ?>" style="font-size: 10px;">
                    <?= ucfirst($document['status'] ?? 'pending') ?>
                </span>
            </div>

            <div class="d-flex flex-column gap-3 mb-4 border-top border-opacity-10 pt-4">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">File Size</span>
                    <span class="text-dark small fw-medium"><?php echo round($document['file_size'] / 1024 / 1024, 2); ?> MB</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Uploaded</span>
                    <span class="text-dark small fw-medium"><?php echo date('M d, Y', strtotime($document['upload_date'])); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Views</span>
                    <span class="text-dark small fw-medium"><?php echo $document['view_count'] ?? 0; ?> times</span>
                </div>
            </div>

            <!-- Summary Card -->
            <?php if (!empty($document['summary'])): ?>
            <div class="card border-0 bg-light p-3 mb-4 shadow-sm">
                <h6 class="fw-bold text-primary small mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-stars"></i> AI Executive Summary
                </h6>
                <p class="small text-secondary mb-0 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical; line-height: 1.6;">
                    <?php echo htmlspecialchars($document['summary']); ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Keywords -->
            <?php if (!empty($keywords)): ?>
            <div class="mb-5">
                <h6 class="fw-bold text-dark small mb-3">Core Concepts</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (array_slice($keywords, 0, 8) as $keyword): ?>
                    <span class="badge bg-opacity-10 bg-info text-info border border-info border-opacity-25 px-2 py-1 rounded-pill" style="font-size: 11px;">
                        #<?php echo htmlspecialchars($keyword); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <a href="/documind/public/documents/<?php echo $document['id']; ?>/download" class="btn btn-primary rounded-pill shadow-sm py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-download"></i> Download Original
                </a>
                <button type="button" class="btn btn-outline-secondary rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 border-opacity-25" data-bs-toggle="modal" data-bs-target="#shareModal">
                    <i class="bi bi-share"></i> Invite Collaborators
                </button>
                <button id="favoriteBtn" class="btn <?php echo (isset($document['is_favorite']) && $document['is_favorite']) ? 'btn-warning' : 'btn-outline-warning'; ?> rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 border-opacity-25">
                    <i class="bi <?php echo (isset($document['is_favorite']) && $document['is_favorite']) ? 'bi-star-fill' : 'bi-star'; ?>"></i> 
                    <?php echo (isset($document['is_favorite']) && $document['is_favorite']) ? 'Drop Favorite' : 'Mark Favorite'; ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Right: AI Chat Interface -->
    <div class="col-lg-8">
        <div class="card shadow-sm d-flex flex-column" style="height: 80vh; min-height: 600px;">
            <!-- Chat Header -->
            <div class="p-4 border-bottom border-opacity-10 d-flex align-items-center justify-content-between bg-surface-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle bg-primary-glow text-primary">
                        <i class="bi bi-robot fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">DocuMind AI</h5>
                        <p class="text-muted small mb-0">Analyzed with Gemini Pro</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <?php if (($_SESSION['user_role'] ?? '') !== 'admin'): ?>
                        <div id="aiLimitBadge" class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background: var(--surface-hover); font-size: 12px;">
                            <i class="bi bi-chat-dots" style="color: var(--primary);"></i>
                            <span id="remainingCount" class="fw-bold" style="color: var(--primary);"><?= $remaining ?? 0 ?></span>
                            <span class="text-muted">/<?= $dailyLimit ?? 20 ?> today</span>
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-link p-0" title="Clear Chat" style="color: var(--text-secondary);"><i class="bi bi-trash3"></i></button>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div id="chatMessages" class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-4" style="background: rgba(0,0,0,0.05);">
                <?php if (empty($chatHistory)): ?>
                <div class="text-center my-auto py-5 opacity-50">
                    <div class="mb-4">
                        <i class="bi bi-chat-dots text-primary" style="font-size: 64px;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">How can I assist you?</h4>
                    <p class="small mx-auto" style="max-width: 300px;">Ask me to summarize specific parts, explain complex terms, or extract data from this document.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($chatHistory as $msg): ?>
                    <div class="d-flex <?php echo $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start'; ?> animate-fade-in">
                        <div class="p-3 rounded-4 shadow-sm <?php echo $msg['role'] === 'user' ? 'bg-primary text-white ms-5' : 'card border border-opacity-10 text-dark me-5'; ?>" style="max-width: 85%; line-height: 1.6;">
                            <?php if ($msg['role'] !== 'user'): ?>
                                <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                                    <i class="bi bi-robot text-primary small"></i>
                                    <span class="text-muted fw-bold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">AI Analyst</span>
                                </div>
                            <?php endif; ?>
                            <div class="chat-content small">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chat Input Area -->
            <div class="p-4 border-top border-opacity-10 bg-surface-footer">
                <form id="chatForm">
                    <div class="position-relative">
                        <textarea id="questionInput" 
                                  placeholder="Type your message about this document..." 
                                  class="form-control border-secondary border-opacity-25 rounded-4 py-3 pe-5 text-dark ps-4" 
                                  rows="1" 
                                  style="resize: none; min-height: 60px; max-height: 200px;"
                                  required></textarea>
                        <button type="submit" 
                                class="btn btn-primary rounded-circle position-absolute" 
                                style="bottom: 10px; right: 10px; width: 40px; height: 40px; padding: 0;"
                                id="sendBtn">
                            <i class="bi bi-send-fill" id="sendIcon"></i>
                            <span class="spinner-border spinner-border-sm d-none" id="sendLoading" role="status"></span>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between mt-2 px-1">
                        <div class="d-flex gap-3">
                            <span class="text-muted small d-flex align-items-center gap-1" style="font-size: 10px;"><i class="bi bi-keyboard"></i> Shift+Enter for new line</span>
                        </div>
                        <span class="text-muted small" style="font-size: 10px; cursor: help;" title="AI may generate inaccurate information. Please verify important details.">Security Notice <i class="bi bi-info-circle"></i></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const documentId = <?= $document['id'] ?>;
const chatForm = document.getElementById('chatForm');
const questionInput = document.getElementById('questionInput');
const chatMessages = document.getElementById('chatMessages');
const sendBtn = document.getElementById('sendBtn');
const sendIcon = document.getElementById('sendIcon');
const sendLoading = document.getElementById('sendLoading');

// Auto-expand textarea
questionInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// Handle Enter to submit (Shift+Enter for newline)
questionInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm.requestSubmit();
    }
});

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const message = questionInput.value.trim();
    if (!message) return;

    // Add user message UI
    appendMessage('user', message);

    // Reset input
    questionInput.value = '';
    questionInput.style.height = 'auto';
    questionInput.focus();

    // Show loading
    sendBtn.disabled = true;
    sendIcon.classList.add('d-none');
    sendLoading.classList.remove('d-none');

    try {
        const response = await fetch('/documind/public/documents/ask', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ documentId: documentId, message: message }),
        });

        const data = await response.json();

        if (data.success) {
            appendMessage('ai', data.response);
            // Update remaining counter
            const counter = document.getElementById('remainingCount');
            if (counter && data.remaining !== undefined) {
                counter.textContent = data.remaining;
                if (data.remaining <= 3) {
                    counter.style.color = 'var(--warning)';
                }
                if (data.remaining <= 0) {
                    counter.style.color = 'var(--danger)';
                    questionInput.disabled = true;
                    questionInput.placeholder = 'Daily AI message limit reached. Try again tomorrow.';
                    sendBtn.disabled = true;
                }
            }
        } else if (data.limit_reached) {
            appendMessage('error', data.error || 'Daily AI message limit reached. Try again tomorrow.');
            questionInput.disabled = true;
            questionInput.placeholder = 'Daily AI message limit reached. Try again tomorrow.';
            sendBtn.disabled = true;
        } else {
            console.error('AI Error:', data.error);
            appendMessage('error', data.error || 'Sorry, I encountered an issue. Please try again.');
        }
    } catch (error) {
        appendMessage('error', 'Network error. Please check your connection.');
    } finally {
        if (!questionInput.disabled) {
            sendBtn.disabled = false;
        }
        sendIcon.classList.remove('d-none');
        sendLoading.classList.add('d-none');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

function appendMessage(role, text) {
    const div = document.createElement('div');
    const isUser = role === 'user';
    const isError = role === 'error';
    
    div.className = `d-flex ${isUser ? 'justify-content-end' : 'justify-content-start'} animate-fade-in`;
    
    let contentHtml = '';
    if (isUser) {
        contentHtml = `
            <div class="p-3 rounded-4 shadow-sm bg-primary text-white ms-5 small" style="max-width: 85%; line-height: 1.6;">
                ${escapeHtml(text).replace(/\n/g, '<br>')}
            </div>
        `;
    } else {
        contentHtml = `
            <div class="p-3 rounded-4 shadow-sm card border border-opacity-10 ${isError ? 'border-danger text-danger' : 'text-dark'} me-5" style="max-width: 85%; line-height: 1.6;">
                <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                    <i class="bi ${isError ? 'bi-exclamation-triangle' : 'bi-robot'} ${isError ? 'text-danger' : 'text-primary'} small"></i>
                    <span class="text-muted fw-bold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">
                        ${isError ? 'Platform Sync Error' : 'AI Analyst'}
                    </span>
                </div>
                <div class="chat-content small">
                    ${isError ? text : escapeHtml(text).replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    }
    
    div.innerHTML = contentHtml;
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Favorite functionality
document.getElementById('favoriteBtn').addEventListener('click', async () => {
    try {
        const response = await fetch('/documind/public/documents/<?= $document['id'] ?>/toggle-favorite', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const data = await response.json();
        if (data.success) {
            const btn = document.getElementById('favoriteBtn');
            const icon = btn.querySelector('i');
            
            if (data.is_favorite) {
                btn.className = 'btn btn-warning rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 border-opacity-25';
                icon.className = 'bi bi-star-fill';
                btn.lastChild.textContent = ' Drop Favorite';
            } else {
                btn.className = 'btn btn-outline-warning rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 border-opacity-25';
                icon.className = 'bi bi-star';
                btn.lastChild.textContent = ' Mark Favorite';
            }
        }
    } catch (error) {
        console.error('Favorite toggle failed');
    }
});

// Auto-scroll to bottom on load
chatMessages.scrollTop = chatMessages.scrollHeight;
</script>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-secondary py-3">
                <h5 class="modal-title fw-bold text-dark">Invite Collaborators</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="shareForm" class="mb-4">
                    <div class="mb-3">
                        <label class="form-label small text-secondary">User Email Address</label>
                        <input type="email" class="form-control border-opacity-25 py-2" id="shareEmail" name="email" placeholder="name@company.com" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-8">
                            <select class="form-select border-opacity-25 py-2" id="sharePermission" name="permission">
                                <option value="view">View Only</option>
                                <option value="comment">Comment Access</option>
                                <option value="edit">Full Access</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" id="shareBtn">Invite</button>
                        </div>
                    </div>
                </form>
                
                <h6 class="fw-bold text-dark small mb-3 border-top border-opacity-10 pt-3">Platform Access List</h6>
                <div id="sharesContainer" class="d-flex flex-column gap-2">
                    <!-- Shares will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const shareBtn = document.getElementById('shareBtn');
const sharesContainer = document.getElementById('sharesContainer');

async function loadShares() {
    try {
        const response = await fetch('/documind/public/share/get-shares/<?= $document['id'] ?>');
        const data = await response.json();
        sharesContainer.innerHTML = '';
        
        if (data.shares && data.shares.length > 0) {
            data.shares.forEach(share => {
                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center p-2 rounded-3 bg-secondary bg-opacity-10 border border-opacity-10 border-white';
                div.innerHTML = `
                    <div class="overflow-hidden pe-3">
                        <p class="mb-0 fw-bold small text-dark text-truncate">${escapeHtml(share['name'])}</p>
                        <span class="text-muted d-block" style="font-size: 10px;">${escapeHtml(share['email'])} • ${share['permission']}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle unshare-btn" data-share-id="${share['shared_with_id']}" title="Remove Access">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;
                sharesContainer.appendChild(div);
            });
            
            document.querySelectorAll('.unshare-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const sharedWithId = e.currentTarget.dataset.shareId;
                    const result = await Swal.fire({
                        title: 'Revoke Access?',
                        text: "This user will no longer be able to access this document.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Revoke'
                    });

                    if (result.isConfirmed) {
                        await unshareDocument(sharedWithId);
                    }
                });
            });
        } else {
            sharesContainer.innerHTML = '<p class="text-center text-muted small py-3 my-0">Document isn\'t shared yet.</p>';
        }
    } catch (error) {
        console.error('Share loading failed');
    }
}

shareBtn.addEventListener('click', async () => {
    const email = document.getElementById('shareEmail').value;
    const permission = document.getElementById('sharePermission').value;
    
    if (!email) return;

    try {
        const response = await fetch('/documind/public/share/share', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ document_id: <?= $document['id'] ?>, user_email: email, permission: permission })
        });
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('shareEmail').value = '';
            Swal.fire({
                icon: 'success',
                title: 'Access Granted',
                text: 'The document has been shared successfully.',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            loadShares();
        } else {
            Swal.fire('Error', data.error || 'Failed to share document', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Network error occurred', 'error');
    }
});

async function unshareDocument(sharedWithId) {
    try {
        const response = await fetch('/documind/public/share/unshare', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ document_id: <?= $document['id'] ?>, shared_with_id: sharedWithId })
        });
        const data = await response.json();
        if (data.success) loadShares();
    } catch (error) {
        console.error('Revoke failed');
    }
}

document.getElementById('shareModal').addEventListener('show.bs.modal', loadShares);
</script>
