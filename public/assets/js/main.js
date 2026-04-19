// DocuMind Phase 5 — Enhanced Main JS

// Safe basePath helper
window.basePath = '/documind/public/';

// Toast notifications
// Toast notifications using SweetAlert2
window.showToast = function(message, type = 'info') {
  const iconMap = {
    'success': 'success',
    'error': 'error',
    'warning': 'warning',
    'info': 'info'
  };

  Swal.fire({
    icon: iconMap[type] || 'info',
    title: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
};

// DocuMindUI namespace
window.DocuMindUI = {
  basePath: window.basePath,
  showToast: window.showToast
};

// Notification badge update
async function updateNotificationBadge() {
  try {
    const response = await fetch(window.basePath + 'user/unread-count');
    const data = await response.json();
    const badge = document.getElementById('notificationBadge');
    if (badge && data.success && data.unread_count > 0) {
      badge.textContent = data.unread_count;
      badge.classList.remove('d-none');
      badge.style.animation = 'pulse 1s ease-in-out 2';
    }
  } catch (e) {}
}

// API helper
window.apiCall = async function(endpoint, options = {}) {
  const url = endpoint.startsWith('http') ? endpoint : window.basePath + endpoint.replace(/^\//, '');
  return fetch(url, {
    ...options,
    headers: {
      'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Content-Type': 'application/json',
      ...options.headers
    }
  }).then(res => res.ok ? res.json() : Promise.reject(res));
};

// ── Mobile Sidebar Toggle ──
function initMobileSidebar() {
  const hamburger = document.getElementById('mobileHamburger');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('mobileOverlay');

  if (!hamburger || !sidebar) return;

  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    if (overlay) overlay.classList.toggle('d-none');
    document.body.classList.toggle('sidebar-open');
  });

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('active');
      overlay.classList.add('d-none');
      document.body.classList.remove('sidebar-open');
    });
  }

  // Close sidebar on navigation (mobile)
  sidebar.querySelectorAll('.nav-item-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 1024) {
        sidebar.classList.remove('active');
        if (overlay) overlay.classList.add('d-none');
        document.body.classList.remove('sidebar-open');
      }
    });
  });
}

// ── Scroll-reveal with IntersectionObserver ──
function initScrollReveal() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.glass-card, .card-glass, .card').forEach(el => {
    if (!el.closest('.modal')) {
      el.style.opacity = '0';
      el.style.transform = 'translateY(12px)';
      el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      observer.observe(el);
    }
  });
}

// ── Auto-dismiss alerts ──
function initAlertDismiss() {
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
    setTimeout(() => {
      try {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      } catch(e) {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
      }
    }, 5000);
  });
}

// ── Count-up Animation ──
function initCountUp() {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count) || 0;
    if (target === 0) { el.textContent = '0'; return; }

    let current = 0;
    const duration = 1500;
    const stepTime = 16;
    const steps = duration / stepTime;
    const increment = target / steps;

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        el.textContent = target.toLocaleString();
        clearInterval(timer);
      } else {
        el.textContent = Math.floor(current).toLocaleString();
      }
    }, stepTime);
  });
}

// ── Smooth Card Hovers ──
function initCardHovers() {
  document.querySelectorAll('.doc-card').forEach(card => {
    card.style.transition = `transform var(--transition), box-shadow var(--transition)`;
    card.addEventListener('mouseenter', () => {
      card.style.transform = 'translateY(-4px)';
      card.style.boxShadow = '0 8px 24px rgba(0,0,0,0.08)';
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = 'translateY(0)';
      card.style.boxShadow = '';
    });
  });
}

// ── Initialization ──
document.addEventListener('DOMContentLoaded', function() {
  initMobileSidebar();
  initAlertDismiss();
  initCountUp();
  initCardHovers();

  // Delay scroll-reveal slightly to let content render
  requestAnimationFrame(() => {
    initScrollReveal();
  });

  // Notification polling (only if logged in — badge element exists)
  if (document.getElementById('notificationBadge')) {
    updateNotificationBadge();
    setInterval(updateNotificationBadge, 30000);
  }

  console.log('✅ DocuMind Phase 5 JS Loaded');
});
