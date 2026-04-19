<?php $title = 'AI Chat - ' . htmlspecialchars(substr($document['original_name'], 0, 30)); ?>

<div class="glass-card p-0" style="height: 90vh; max-height: 900px;">
  <div class="row g-0 h-100 overflow-hidden">
    <!-- Sidebar -->
    <div class="col-lg-3 d-none d-lg-block border-end" style="border-color: var(--border) !important; background: var(--bg-glass);">
      <div class="p-5 h-100 d-flex flex-column">
        <div class="mb-6">
          <h2 class="h5 fw-bold mb-3 lh-sm"><?= htmlspecialchars(substr($document['original_name'], 0, 40)) ?></h2>
          <div class="d-flex gap-2 flex-wrap mb-4">
            <?php if (!empty($keywords)): ?>
              <?php foreach (array_slice($keywords, 0, 8) as $keyword): ?>
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill text-xs fw-semibold"><?= htmlspecialchars($keyword) ?></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div class="text-xs text-secondary space-y-1">
            <div><i class="bi bi-file-earmark-text me-1"></i><?= strtoupper($document['file_type']) ?></div>
            <div><i class="bi bi-calendar3 me-1"></i><?= date('M d, Y', strtotime($document['upload_date'])) ?></div>
            <div><i class="bi bi-hdd me-1"></i><?= round($document['file_size']/1024/1024, 1) ?> MB</div>
            <?php if ($document['ai_processed']): ?>
              <div class="badge bg-success px-2 py-1 rounded-pill text-xs mt-2"><i class="fa-solid fa-robot me-1"></i>AI Processed</div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="flex-grow-1 d-flex flex-column justify-content-end">
          <div class="mb-4">
            <button class="btn btn-outline-primary w-100 rounded-pill mb-2" onclick="window.location.reload()">
              <i class="bi bi-arrow-clockwise me-2"></i>New Chat
            </button>
            <button class="btn btn-outline-secondary w-100 rounded-pill" onclick="clearHistory()">
              <i class="bi bi-trash3 me-2"></i>Clear History
            </button>
          </div>
          <?php if (!empty($document['summary'])): ?>
            <div class="bg-secondary-subtle p-4 rounded-3 small">
              <strong>Summary:</strong>
              <div class="mt-2 lh-sm" style="max-height: 120px; overflow-y: auto;"><?= htmlspecialchars(substr($document['summary'], 0, 200)) ?>...</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button id="sidebarToggle" class="btn btn-primary position-fixed start-0 top-50 translate-middle-y ms-4 p-3 rounded-end-0 shadow-lg z-1060 d-lg-none" style="height: 60px;">
      <i class="bi bi-list fs-5"></i>
    </button>

    <!-- Main Chat -->
    <div class="col-lg-9 col-12 h-100 position-relative">
      <!-- Header -->
      <div class="glass-card border-0 position-sticky top-0 z-3 p-4" style="backdrop-filter: blur(20px);">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 d-lg-none">
            <button id="mobileBack" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
              <i class="bi bi-arrow-left"></i>
            </button>
            <h5 class="mb-0 fw-bold text-truncate" style="max-width: 250px;"><?= htmlspecialchars(substr($document['original_name'], 0, 35)) ?></h5>
          </div>
          <div class="d-none d-lg-flex align-items-center gap-3">
            <span class="badge bg-primary px-3 py-2 rounded-pill text-xs fw-semibold">Live Chat</span>
            <span class="text-xs text-secondary" id="chatStatus">Ready to chat</span>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="window.print()">
              <i class="bi bi-printer"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-4" onclick="toggleSettings()">
              <i class="bi bi-gear"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Messages -->
      <div id="chatMessages" class="flex-grow-1 p-5 overflow-y-auto" style="scroll-behavior: smooth;">
        <?php if (empty($chatHistory)): ?>
          <div class="empty-chat text-center py-12">
            <div class="ai-avatar mb-4 mx-auto">
              <i class="bi bi-robot display-3 text-primary opacity-25"></i>
            </div>
            <h3 class="fw-bold text-muted mb-3">Welcome to AI Chat!</h3>
            <p class="lead text-secondary mb-6 mx-auto" style="max-width: 500px;">
              Ask anything about this document. Try questions like:
            </p>
            <div class="row g-3 justify-content-center">
              <div class="col-md-6 col-lg-4">
                <button class="btn btn-outline-primary w-100 rounded-pill py-3 glass-card" onclick="quickQuestion('summarize')">
                  <i class="fa-regular fa-file-lines me-2"></i>Summarize document
                </button>
              </div>
              <div class="col-md-6 col-lg-4">
                <button class="btn btn-outline-success w-100 rounded-pill py-3 glass-card" onclick="quickQuestion('key points')">
                  <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Key insights
                </button>
              </div>
              <div class="col-md-6 col-lg-4">
                <button class="btn btn-outline-info w-100 rounded-pill py-3 glass-card" onclick="quickQuestion('explain')">
                  <i class="fa-solid fa-circle-question me-2"></i>Explain section
                </button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($chatHistory as $msg): ?>
            <div class="message-row <?= $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' ?>">
              <div class="message-bubble <?= $msg['role'] === 'user' ? 'user-bubble' : 'ai-bubble' ?>">
                <div class="message-content"><?= htmlspecialchars($msg['message']) ?></div>
                <div class="message-meta">
                  <small class="text-muted"><?= date('H:i', strtotime($msg['created_at'])) ?></small>
                  <div class="message-actions ms-2">
                    <button class="btn btn-sm p-1" onclick="copyMessage(this)" title="Copy">
                      <i class="bi bi-copy"></i>
                    </button>
                    <?php if ($msg['role'] === 'user'): ?>
                      <button class="btn btn-sm p-1" onclick="retryMessage(<?= $msg['id'] ?>)" title="Regenerate">
                        <i class="bi bi-arrow-clockwise"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Input Area -->
      <div class="border-top p-4" style="border-color: var(--border) !important; background: var(--bg-card);">
        <form id="chatForm" class="position-relative">
          <div class="input-group">
            <textarea 
              id="messageInput" 
              class="form-control rounded-4 border-0 pt-4 pb-4 shadow-none" 
              rows="2" 
              placeholder="Ask anything about the document..."
              style="resize: vertical; font-size: 16px; line-height: 1.5; max-height: 120px;"
              required></textarea>
            <div class="input-group-text border-0 bg-transparent p-0 position-absolute bottom-0 end-0 me-4 mb-3">
              <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-lg position-relative" id="sendBtn" disabled>
                <span class="send-text">
                  <i class="bi bi-send-fill me-2"></i>Send
                </span>
                <span class="send-loading d-none">
                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                  Thinking...
                </span>
              </button>
            </div>
          </div>
          
          <!-- Typing Indicator (hidden by default) -->
          <div id="typingIndicator" class="typing-indicator mt-3 d-none">
            <div class="d-flex align-items-center gap-3">
              <div class="ai-avatar">
                <i class="bi bi-robot text-primary"></i>
              </div>
              <div>
                <div class="typing-dots">
                  <span></span><span></span><span></span>
                </div>
                <small class="text-muted">AI is thinking...</small>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Mobile Sidebar -->
<div id="mobileSidebar" class="offcanvas offcanvas-start glass-card" tabindex="-1">
  <div class="offcanvas-header border-bottom p-4">
    <h5 class="offcanvas-title fw-bold mb-0"><?= htmlspecialchars(substr($document['original_name'], 0, 30)) ?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-4">
    <!-- Sidebar content same as desktop -->
    <div class="mb-6">
      <div class="d-flex gap-2 flex-wrap mb-4">
        <?php if (!empty($keywords)): ?>
          <?php foreach (array_slice($keywords, 0, 6) as $keyword): ?>
            <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill text-xs"><?= htmlspecialchars($keyword) ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="text-xs text-secondary space-y-2">
        <div><i class="bi bi-file-earmark-text me-1"></i><?= strtoupper($document['file_type']) ?></div>
        <div><i class="bi bi-calendar3 me-1"></i><?= date('M d, Y', strtotime($document['upload_date'])) ?></div>
        <div><i class="bi bi-hdd me-1"></i><?= round($document['file_size']/1024/1024, 1) ?> MB</div>
      </div>
    </div>
    <div class="d-grid gap-3">
      <button class="btn btn-outline-primary rounded-pill py-3" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>New Chat
      </button>
      <button class="btn btn-outline-secondary rounded-pill py-3" onclick="clearHistory()">
        <i class="bi bi-trash3 me-2"></i>Clear History
      </button>
    </div>
  </div>
</div>

<!-- AI Settings Modal -->
<div class="modal fade" id="aiSettingsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-card border-0">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">AI Processing Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-4">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Response Conciseness</label>
            <select id="settingStyle" class="form-select border-secondary">
                <option value="balanced" selected>Balanced (Default)</option>
                <option value="concise">Extremely Concise (Bullet points)</option>
                <option value="detailed">Verbose / Detailed</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Analytical Depth</label>
            <select id="settingDepth" class="form-select border-secondary">
                <option value="standard" selected>Standard (General Audience)</option>
                <option value="academic">Academic / Formal</option>
                <option value="eli5">Explain Like I'm 5</option>
            </select>
        </div>
        <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" onclick="saveAISettings()">
            <i class="fa-solid fa-floppy-disk me-2"></i>Save Preferences
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const documentId = <?= $document['id'] ?>;

class AIChat {
  constructor() {
    this.init();
    this.scrollToBottom();
  }

  init() {
    document.getElementById('chatForm').addEventListener('submit', this.sendMessage.bind(this));
    document.getElementById('messageInput').addEventListener('input', this.toggleSendButton);
    document.getElementById('messageInput').addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.sendMessage();
      }
    });
  }

  toggleSendButton() {
    const input = document.getElementById('messageInput');
    const btn = document.getElementById('sendBtn');
    btn.disabled = !input.value.trim();
  }

  async sendMessage(e) {
    e?.preventDefault();
    
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return;

    // UI States
    this.addUserMessage(message);
    input.value = '';
    this.setLoadingState(true);
    this.scrollToBottom();

    try {
      const chatStyle = localStorage.getItem('aiStyle') || 'balanced';
      const chatDepth = localStorage.getItem('aiDepth') || 'standard';

      const response = await apiCall('ai/send', {
        method: 'POST',
        body: JSON.stringify({ documentId, message, style: chatStyle, depth: chatDepth })
      });

      if (response.success) {
        this.addAIMessage(response.response);
      } else {
        this.addSystemMessage('Error: ' + (response.error || 'AI unavailable'), 'error');
      }
    } catch (error) {
      this.addSystemMessage('Network error. Please check connection.', 'error');
    } finally {
      this.setLoadingState(false);
      this.toggleSendButton();
      this.scrollToBottom();
    }
  }

  addUserMessage(message) {
    const messages = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message-row justify-content-end mb-4';
    div.innerHTML = `
      <div class="message-bubble user-bubble">
        <div class="message-content">${this.escapeHtml(message)}</div>
        <div class="message-meta">
          <small class="text-muted me-2">You</small>
          <small class="text-muted">${this.formatTime()}</small>
          <div class="message-actions ms-2">
            <button class="btn btn-sm p-1 text-muted" onclick="AIChat.copyMessage(this)" title="Copy">
              <i class="bi bi-copy"></i>
            </button>
          </div>
        </div>
      </div>
    `;
    messages.appendChild(div);
  }

  addAIMessage(message, streaming = false) {
    const messages = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message-row justify-content-start mb-4';
    div.innerHTML = `
      <div class="message-bubble ai-bubble">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="ai-avatar">
            <i class="bi bi-robot text-primary fs-4"></i>
          </div>
          <div>
            <strong class="d-block mb-1">AI Assistant</strong>
            <small class="text-muted">${this.formatTime()}</small>
          </div>
        </div>
        <div class="message-content streaming" id="lastMessage">${streaming ? '' : this.escapeHtml(message)}</div>
        <div class="message-meta mt-2">
          <div class="message-reactions d-flex gap-1">
            <button class="btn btn-sm p-1 reaction-btn" data-reaction="up" title="Thumbs Up"><i class="fa-solid fa-thumbs-up text-secondary"></i></button>
            <button class="btn btn-sm p-1 reaction-btn" data-reaction="love" title="Heart"><i class="fa-solid fa-heart text-danger"></i></button>
            <button class="btn btn-sm p-1 reaction-btn" data-reaction="retry" title="Regenerate"><i class="fa-solid fa-rotate-right text-secondary"></i></button>
          </div>
        </div>
      </div>
    `;
    messages.appendChild(div);
    
    if (streaming) {
      this.typeMessage(div.querySelector('#lastMessage'), message);
    }
    
    this.scrollToBottom();
  }

  addSystemMessage(message, type = 'info') {
    const messages = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message-row justify-content-center mb-4';
    div.innerHTML = `
      <div class="message-bubble system-bubble px-4 py-3 rounded-pill">
        <i class="bi bi-info-circle me-2 ${type === 'error' ? 'text-danger' : 'text-info'}"></i>
        ${this.escapeHtml(message)}
      </div>
    `;
    messages.appendChild(div);
    this.scrollToBottom();
  }

  typeMessage(element, text) {
    let i = 0;
    const timer = setInterval(() => {
      element.textContent += text.charAt(i);
      i++;
      this.scrollToBottom();
      
      if (i >= text.length) {
        clearInterval(timer);
        element.classList.remove('streaming');
      }
    }, 30);
  }

  setLoadingState(loading) {
    const btn = document.getElementById('sendBtn');
    const typing = document.getElementById('typingIndicator');
    
    if (loading) {
      btn.querySelector('.send-loading').classList.remove('d-none');
      btn.querySelector('.send-text').classList.add('d-none');
      typing.classList.remove('d-none');
      this.scrollToBottom();
    } else {
      btn.querySelector('.send-loading').classList.add('d-none');
      btn.querySelector('.send-text').classList.remove('d-none');
      typing.classList.add('d-none');
    }
  }

  scrollToBottom() {
    const messages = document.getElementById('chatMessages');
    messages.scrollTop = messages.scrollHeight;
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  formatTime() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  static copyMessage(btn) {
    const content = btn.closest('.message-bubble').querySelector('.message-content').textContent;
    navigator.clipboard.writeText(content).then(() => {
      const original = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-check-lg"></i>';
      btn.classList.add('text-success');
      setTimeout(() => {
        btn.innerHTML = original;
        btn.classList.remove('text-success');
      }, 2000);
    });
  }
}

// Quick questions
function quickQuestion(type) {
  const questions = {
    summarize: 'Please provide a comprehensive summary of this document.',
    'key points': 'What are the 5 most important key points from this document?',
    explain: 'Can you explain the main concepts covered in this document?'
  };
  document.getElementById('messageInput').value = questions[type];
  document.getElementById('chatForm').dispatchEvent(new Event('submit'));
}

// Init
const AIChat = new AIChat();

// Mobile controls
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  const sidebar = new bootstrap.Offcanvas(document.getElementById('mobileSidebar'));
  sidebar.show();
});

async function clearHistory() {
  const result = await Swal.fire({
    title: 'Clear History?',
    text: "This will permanently delete your entire conversation history for this document.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, Clear All'
  });

  if (result.isConfirmed) {
    apiCall(`ai/${documentId}/clear`, { method: 'POST' })
      .then(() => location.reload())
      .catch(() => DocuMindUI.showToast('Clear failed', 'error'));
  }
}

function toggleSettings() {
  const style = localStorage.getItem('aiStyle') || 'balanced';
  const depth = localStorage.getItem('aiDepth') || 'standard';
  
  document.getElementById('settingStyle').value = style;
  document.getElementById('settingDepth').value = depth;
  
  new bootstrap.Modal(document.getElementById('aiSettingsModal')).show();
}

function saveAISettings() {
  const style = document.getElementById('settingStyle').value;
  const depth = document.getElementById('settingDepth').value;
  
  localStorage.setItem('aiStyle', style);
  localStorage.setItem('aiDepth', depth);
  
  bootstrap.Modal.getInstance(document.getElementById('aiSettingsModal')).hide();
  DocuMindUI.showToast('AI Settings updated for this session', 'success');
}
</script>

<style>
:root {
  --chat-bg: var(--bg-secondary);
  --bubble-radius: 20px;
}

.message-row { margin-bottom: 1.5rem; }
.message-bubble {
  max-width: 75%;
  padding: 1.25rem 1.5rem;
  border-radius: var(--bubble-radius);
  position: relative;
  box-shadow: var(--shadow);
  backdrop-filter: blur(12px);
  animation: messageSlideIn 0.3s ease-out;
}

@keyframes messageSlideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.user-bubble {
  background: linear-gradient(135deg, var(--primary), #8b5cf6) !important;
  color: white !important;
  margin-left: auto;
  border-bottom-right-radius: 8px !important;
}

.ai-bubble {
  background: var(--bg-glass) !important;
  border: 1px solid var(--border) !important;
  margin-right: auto;
  border-bottom-left-radius: 8px !important;
}

.system-bubble {
  background: var(--info) !important;
  color: white !important;
  font-size: 0.875rem;
}

.message-content { 
  line-height: 1.6; 
  word-break: break-word;
  white-space: pre-wrap;
}
.message-content.streaming::after {
  content: '|';
  animation: blink 1s infinite;
}
@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

.message-meta {
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  opacity: 0.7;
}

.ai-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  flex-shrink: 0;
}

.message-actions {
  display: flex;
  gap: 0.25rem;
}

.reaction-btn:hover {
  transform: scale(1.2);
  background: rgba(255,255,255,0.2) !important;
}

.typing-indicator {
  animation: fadeIn 0.3s ease-out;
}

.typing-dots {
  display: flex;
  gap: 4px;
}
.typing-dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--text-secondary);
  animation: typing 1.4s infinite ease-in-out;
}
.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}

.empty-chat {
  animation: fadeIn 0.6s ease-out;
}

#chatMessages {
  background: linear-gradient(to bottom, var(--bg-card), var(--chat-bg));
}

@media (max-width: 992px) {
  .message-bubble { max-width: 90% !important; }
}

.input-group-text {
  border: none !important;
  background: transparent !important;
  z-index: 3;
}
</style>
