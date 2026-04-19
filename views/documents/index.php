<?php $title = 'My Repository'; ?>

<!-- Hero + Controls -->
<div class="glass-card mb-8 p-6">
  <div class="row align-items-center g-4">
    <div class="col-lg-6">
      <div class="d-flex align-items-center gap-4 mb-4 mb-lg-0">
        <h1 class="h3 fw-bold mb-0 lh-1 flex-grow-1">My Repository</h1>
        <div class="search-container position-relative">
          <input type="text" id="docSearch" class="form-control ps-5 rounded-pill shadow-sm" placeholder="&#xf002; Search documents..." style="font-family: 'Inter', 'Font Awesome 6 Free'; font-weight: 900; border: 1px solid var(--border);">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-4 text-muted"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="d-flex align-items-center gap-3 justify-content-end justify-content-lg-end">
        <!-- Filters -->
        <select id="docFilter" class="form-select form-select-sm rounded-pill shadow-sm" style="width: auto; border: 1px solid var(--border);">
          <option value="" selected>All Types</option>
          <option value="pdf">PDF</option>
          <option value="docx">DOCX</option>
          <option value="ai">AI Processed</option>
        </select>
        <div class="vr mx-2 d-none d-lg-block"></div>
        <!-- Bulk Actions (hidden initially) -->
        <div id="bulkActions" class="d-none gap-2">
          <button class="btn btn-outline-danger btn-sm rounded-pill px-4" onclick="bulkDelete()">
            <i class="bi bi-trash3"></i> Delete
          </button>
          <button class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="bulkShare()">
            <i class="bi bi-share"></i> Share
          </button>
        </div>
        <!-- Upload + Grid Toggle -->
        <button class="btn btn-primary-modern px-5 rounded-pill shadow-lg" onclick="window.location='/documind/public/documents/upload'">
          <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload
        </button>
        <button id="gridToggle" class="btn btn-outline-secondary btn-sm rounded-circle p-2" title="Toggle Layout">
          <i class="bi bi-grid-3x3-gap-fill"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Select Counter -->
<div id="selectCounter" class="glass-card position-fixed bottom-0 start-50 translate-middle-x mb-4 mx-4 p-3 rounded-4 shadow-lg d-none z-1050" style="max-width: 400px;">
  <div class="d-flex align-items-center justify-content-between">
    <span id="counterText" class="fw-bold"></span>
    <div class="btn-group btn-group-sm" role="group">
      <button class="btn btn-outline-danger rounded-pill px-3" onclick="clearSelection()">Clear</button>
      <button class="btn btn-success rounded-pill px-3" onclick="bulkAction()">Action</button>
    </div>
  </div>
</div>

<!-- Documents Grid -->
<div id="documentsGrid" class="infinite-container position-relative">
  <div class="row g-4" id="docList">
    <!-- Dynamic content loaded here -->
  </div>
  
  <!-- Loading Spinner -->
  <div id="loadingSpinner" class="text-center py-12 d-none">
    <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3 text-secondary">Loading more documents...</p>
  </div>
  
  <!-- No Results -->
  <div id="noResults" class="text-center py-12 d-none">
    <div class="mb-6">
      <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
    </div>
    <h3 class="fw-bold text-muted mb-3">No documents found</h3>
    <p class="text-secondary mb-6" style="max-width: 500px;">Try adjusting your search or filters. Your documents will appear here.</p>
    <button class="btn btn-primary-modern px-6 rounded-pill" onclick="window.location.reload()">
      <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
  </div>
  
  <!-- End of List -->
  <div id="endOfList" class="text-center py-12 d-none">
    <i class="bi bi-check-circle-fill text-success fs-1 mb-3 opacity-75"></i>
    <h5 class="text-secondary fw-semibold">All caught up!</h5>
    <p class="text-muted small">You've seen all your documents.</p>
  </div>
</div>

<!-- Quick Preview Modal -->
<div class="modal fade" id="quickPreview" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content glass-card">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="previewTitle"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div id="previewContent" class="p-6"></div>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Share Modal -->
<div class="modal fade" id="bulkShareModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-card border-0">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Bulk Share Documents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-secondary mb-3">Share <span id="bulkShareCount" class="fw-bold text-primary">0</span> selected documents.</p>
        
        <div class="mb-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Recipient Email</label>
            <div class="input-group">
                <span class="input-group-text border-secondary bg-transparent"><i class="fa-solid fa-envelope text-secondary"></i></span>
                <input type="email" id="bulkShareEmail" class="form-control border-secondary border-start-0 ps-0" placeholder="colleague@example.com">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Permissions</label>
            <select id="bulkSharePermission" class="form-select border-secondary">
                <option value="viewer" selected>Viewer</option>
                <option value="editor">Editor</option>
            </select>
        </div>
        
        <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" onclick="executeBulkShare()">
            <i class="fa-solid fa-share-nodes me-2"></i>Share Documents
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Documents Gallery v2.0
class DocumentsGallery {
  constructor() {
    this.page = 0;
    this.perPage = 12;
    this.selected = new Set();
    this.isLoading = false;
    this.hasMore = true;
    this.init();
  }

  init() {
    this.loadDocuments(true);
    this.bindEvents();
    this.observeIntersection();
  }

  bindEvents() {
    // Search
    document.getElementById('docSearch').addEventListener('input', debounce(this.filterDocuments.bind(this), 300));
    
    // Filter
    document.getElementById('docFilter').addEventListener('change', this.filterDocuments.bind(this));
    
    // Grid toggle
    document.getElementById('gridToggle').addEventListener('click', this.toggleLayout);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
          case 'a': e.preventDefault(); this.selectAll(); break;
          case 'f': e.preventDefault(); document.getElementById('docSearch').focus(); break;
        }
      }
    });
  }

  async loadDocuments(initial = false, filter = '') {
    if (this.isLoading && !initial) return;
    
    this.isLoading = true;
    document.getElementById('loadingSpinner').classList.remove('d-none');
    
    try {
      const params = new URLSearchParams({
        page: this.page,
        per_page: this.perPage,
        filter: filter || document.getElementById('docFilter').value,
        search: document.getElementById('docSearch').value
      });
      
      const data = await apiCall(`documents/list-json?${params}`);
      
      if (initial) {
        document.getElementById('docList').innerHTML = '';
        this.selected.clear();
        // Clear status messages on initial load
        document.getElementById('noResults').classList.add('d-none');
        document.getElementById('endOfList').classList.add('d-none');
      }
      
      this.renderDocuments(data.documents || []);
      this.hasMore = data.has_more ?? false;
      
      if (!data.documents.length && this.page === 0) {
        document.getElementById('noResults').classList.remove('d-none');
      } else if (!this.hasMore && data.documents.length > 0) {
        document.getElementById('endOfList').classList.remove('d-none');
      }
      
    } catch (error) {
      DocuMindUI.showToast('Failed to load documents', 'error');
    } finally {
      this.isLoading = false;
      document.getElementById('loadingSpinner').classList.add('d-none');
    }
  }

  renderDocuments(docs) {
    const container = document.getElementById('docList');
    const skeletonHTML = Array(6).fill(`
      <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="glass-card h-100 skeleton">
          <div class="skeleton-avatar mx-auto mb-4"></div>
          <div class="skeleton-text mx-auto mb-3" style="width: 80%;"></div>
          <div class="skeleton-text mx-auto mb-4" style="width: 60%;"></div>
        </div>
      </div>
    `).join('');
    
    container.insertAdjacentHTML('beforeend', skeletonHTML);
    
    setTimeout(() => {
      container.querySelectorAll('.skeleton').forEach(el => el.remove());
      
      docs.forEach(doc => {
        const card = this.createDocCard(doc);
        container.insertAdjacentHTML('beforeend', card);
      });
      
      // Re-bind events for ALL new cards
      const newCards = container.querySelectorAll('.doc-card:not([data-bound="true"])');
      this.bindCardEvents(newCards);
      newCards.forEach(card => card.setAttribute('data-bound', 'true'));
    }, 300);
  }

  createDocCard(doc) {
    return `
      <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card doc-card h-100 position-relative overflow-hidden border-0" data-doc-id="${doc.id}" style="background: var(--surface-hover);">
          <div class="p-4 h-100 d-flex flex-column">
            <!-- Icon -->
            <div class="doc-icon mb-3 p-3 rounded-3 mx-auto text-center position-relative z-2" style="width: 70px; height: 70px; font-size: 2rem; background: ${doc.file_type === 'pdf' ? 'var(--danger-glow)' : 'var(--primary-glow)'};">
              <i class="bi ${doc.file_type === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill'} ${doc.file_type === 'pdf' ? 'text-danger' : 'text-primary'}" style="color: ${doc.file_type === 'pdf' ? 'var(--danger)' : 'var(--primary)'} !important;"></i>
            </div>
            
            <!-- Checkbox -->
            <div class="form-check position-absolute top-0 start-0 m-3 z-3">
              <input class="form-check-input doc-checkbox" type="checkbox" data-doc-id="${doc.id}">
            </div>
            
            <!-- Content -->
            <div class="flex-grow-1 d-flex flex-column text-center">
              <h6 class="fw-bold mb-2 text-truncate text-dark" style="font-size: 14px;" title="${doc.original_name}">${doc.original_name}</h6>
              
              <div class="mb-3 d-flex flex-wrap justify-content-center gap-1">
                <span class="badge ${doc.file_type === 'pdf' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary'} px-2 py-1 rounded-pill" style="font-size: 10px;">${doc.file_type.toUpperCase()}</span>
                ${doc.ai_processed ? '<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 10px;">AI Ready</span>' : ''}
                ${doc.status === 'pending' ? '<span class="badge px-2 py-1 rounded-pill" style="font-size: 10px; background: var(--warning-glow); color: var(--warning);">Pending</span>' : ''}
                ${doc.status === 'rejected' ? '<span class="badge px-2 py-1 rounded-pill" style="font-size: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444;">Rejected</span>' : ''}
              </div>
              
              <div class="text-secondary mt-auto mb-3" style="font-size: 11px;">
                <span><i class="bi bi-calendar3 me-1"></i> ${new Date(doc.upload_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</span> &bull; 
                <span><i class="bi bi-hdd-fill me-1"></i> ${Math.round(doc.file_size/1024/1024 * 10)/10} MB</span>
              </div>
              
              <!-- Actions -->
              <div class="d-flex justify-content-center gap-1 border-top pt-3" style="border-color: rgba(0,0,0,0.05) !important;">
                <button class="btn btn-sm text-primary p-1" title="Open AI Chat" onclick="openDoc(${doc.id})">
                  <i class="bi bi-chat-dots fs-5"></i>
                </button>
                <button class="btn btn-sm text-info p-1" title="View Document" onclick="viewDocOnline(${doc.id}, '${doc.file_type}', '${doc.original_name}')">
                  <i class="bi bi-eye fs-5"></i>
                </button>
                <button class="btn btn-sm text-success p-1" title="Download" onclick="downloadDoc(${doc.id})">
                  <i class="bi bi-download fs-5"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  bindCardEvents(cards) {
    cards.forEach(card => {
      // Hover preview
      card.addEventListener('mouseenter', () => {
        card.querySelector('.doc-hover-preview')?.classList.remove('opacity-0');
      });
      
      card.addEventListener('mouseleave', () => {
        card.querySelector('.doc-hover-preview')?.classList.add('opacity-0');
      });
      
      // Checkbox
      const checkbox = card.querySelector('.doc-checkbox');
      checkbox.addEventListener('change', (e) => {
        const docId = e.target.dataset.docId;
        if (e.target.checked) {
          this.selected.add(docId);
        } else {
          this.selected.delete(docId);
        }
        this.updateSelection();
      });
    });
  }

  updateSelection() {
    const count = this.selected.size;
    const counter = document.getElementById('selectCounter');
    const actions = document.getElementById('bulkActions');
    
    if (count > 0) {
      document.getElementById('counterText').textContent = `${count} selected`;
      counter.classList.remove('d-none');
      actions.classList.remove('d-none');
    } else {
      counter.classList.add('d-none');
      actions.classList.add('d-none');
    }
  }

  selectAll() {
    document.querySelectorAll('.doc-checkbox').forEach(cb => {
      cb.checked = true;
      this.selected.add(cb.dataset.docId);
    });
    this.updateSelection();
  }

  clearSelection() {
    document.querySelectorAll('.doc-checkbox').forEach(cb => {
      cb.checked = false;
      this.selected.clear();
    });
    this.updateSelection();
  }

  async filterDocuments() {
    this.page = 0;
    document.getElementById('docList').innerHTML = '';
    document.getElementById('noResults').classList.add('d-none');
    document.getElementById('endOfList').classList.add('d-none');
    await this.loadDocuments(true);
  }

  toggleLayout() {
    const grid = document.getElementById('documentsGrid');
    grid.classList.toggle('grid-dense');
    DocuMindUI.showToast('Layout toggled', 'info');
  }

  observeIntersection() {
    const observer = new IntersectionObserver(async (entries) => {
      if (entries[0].isIntersecting && this.hasMore && !this.isLoading) {
        this.page++;
        await this.loadDocuments();
      }
    }, { threshold: 0.1 });
    
    observer.observe(document.getElementById('loadingSpinner'));
  }
}

// Quick Actions
function openDoc(id) {
  window.location.href = `${DocuMindUI.basePath}documents/${id}`;
}

function viewDocOnline(id, type, name) {
  if (type === 'pdf') {
     document.getElementById('previewTitle').textContent = name;
     document.getElementById('previewContent').innerHTML = `
        <iframe src="${DocuMindUI.basePath}documents/${id}/inline" style="width: 100%; height: 75vh; border: none; border-radius: 0 0 16px 16px;"></iframe>
     `;
     const modal = new bootstrap.Modal(document.getElementById('quickPreview'));
     modal.show();
  } else {
     DocuMindUI.showToast('Inline preview is currently only available for PDF files. Pls download DOCX files.', 'warning');
  }
}

function downloadDoc(id) {
  window.location.href = `${DocuMindUI.basePath}documents/${id}/download`;
}

function favoriteDoc(id) {
  apiCall(`documents/${id}/toggle-favorite`, { method: 'POST' })
    .then(() => DocuMindUI.showToast('Favorited!', 'success'))
    .catch(() => DocuMindUI.showToast('Toggle failed', 'error'));
}

async function bulkDelete() {
  const result = await Swal.fire({
    title: 'Delete Documents?',
    text: `Are you sure you want to delete ${gallery.selected.size} selected documents? This action is irreversible.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, Delete All'
  });

  if (result.isConfirmed) {
    try {
      const response = await apiCall('documents/bulk-delete', {
        method: 'POST',
        body: JSON.stringify({ document_ids: Array.from(gallery.selected) })
      });
      
      if (response.success) {
        gallery.selected.clear();
        updateSelectionUI();
        loadDocuments(); // Refresh the gallery
        DocuMindUI.showToast(response.message || 'Documents deleted', 'success');
      } else {
        DocuMindUI.showToast(response.error || 'Bulk delete failed', 'error');
      }
    } catch (error) {
      DocuMindUI.showToast('Network error during bulk delete', 'error');
    }
  }
}

function bulkShare() {
  if (gallery.selected.size === 0) return;
  document.getElementById('bulkShareCount').textContent = gallery.selected.size;
  document.getElementById('bulkShareEmail').value = '';
  new bootstrap.Modal(document.getElementById('bulkShareModal')).show();
}

async function executeBulkShare() {
  const email = document.getElementById('bulkShareEmail').value.trim();
  const permission = document.getElementById('bulkSharePermission').value;
  
  if (!email) {
      DocuMindUI.showToast('Please enter an email address', 'error');
      return;
  }
  
  const docIds = Array.from(gallery.selected);
  
  try {
      const response = await fetch('/documind/public/documents/bulk-share', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ docIds, email, permission })
      });
      const data = await response.json();
      
      if (data.success) {
          bootstrap.Modal.getInstance(document.getElementById('bulkShareModal')).hide();
          DocuMindUI.showToast(data.message || 'Documents shared successfully', 'success');
          gallery.clearSelection();
      } else {
          DocuMindUI.showToast(data.error || 'Failed to share documents', 'error');
      }
  } catch (error) {
      DocuMindUI.showToast('Network error', 'error');
  }
}

// Utils
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Initialize Gallery
let gallery = null;

// Ensure gallery initializes when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    gallery = new DocumentsGallery();
    window.gallery = gallery;
  });
} else {
  // DOM is already ready
  gallery = new DocumentsGallery();
  window.gallery = gallery;
}
</script>

<style>
.infinite-container { min-height: 600px; }
.grid-dense .row { row-gap: 1rem !important; }
.grid-dense .col-xl-2 { padding-bottom: 1rem !important; }
.doc-card { transition: var(--transition); cursor: pointer; border-radius: 16px; }
.doc-card:hover { transform: translateY(-8px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
.select-counter { box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important; }
#bulkActions { gap: 0.5rem; }
@media (max-width: 768px) {
  .search-container { order: 3; width: 100%; margin-top: 1rem; }
}</style>
