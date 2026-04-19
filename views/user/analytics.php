<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">My Analytics</h2>
        <p class="text-secondary mb-0">Track your document engagement, AI utilization, and storage consumption.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <button onclick="window.history.back()" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard
        </button>
    </div>
</div>

<!-- Stats Overview -->
<div class="row g-4 mb-5 stagger-enter">
    <div class="col-xl-3 col-lg-6">
        <div class="card border-0 h-100 summary-card text-center" style="background: var(--surface-hover);">
            <div class="card-body p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-file-invoice text-primary fs-5"></i>
                </div>
                <h6 class="text-secondary fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Documents</h6>
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['total_documents'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card border-0 h-100 summary-card text-center" style="background: var(--surface-hover);">
            <div class="card-body p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-hard-drive fs-5"></i>
                </div>
                <h6 class="text-secondary fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Storage Used</h6>
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['storage_mb'] ?? 0 ?> <span class="text-secondary fs-6 align-text-bottom">MB</span></h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card border-0 h-100 summary-card text-center" style="background: var(--surface-hover);">
            <div class="card-body p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-eye fs-5"></i>
                </div>
                <h6 class="text-secondary fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Document Views</h6>
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['total_views'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card border-0 h-100 summary-card text-center" style="background: var(--surface-hover);">
            <div class="card-body p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-info-subtle text-info rounded-circle mb-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-bolt fs-5"></i>
                </div>
                <h6 class="text-secondary fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Recent Activities</h6>
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['recent_activity'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 16px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0">Engagement Density (7 Days)</h6>
                <p class="text-secondary small">Your system interaction frequency mapped over the last week.</p>
            </div>
            <div class="card-body p-4 position-relative" style="height: 350px;">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 16px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0">Storage Composition</h6>
                <p class="text-secondary small">Distribution of files vs limit (100MB)</p>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center position-relative" style="height: 350px;">
                <canvas id="storageChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const activityData = <?= json_encode($activityDensity) ?>;
    const labels = activityData.map(d => d.day);
    const dataPoints = activityData.map(d => d.count);
    
    // Engagement Line Chart
    const ctxEngagement = document.getElementById('engagementChart').getContext('2d');
    
    const gradient = ctxEngagement.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.4)');
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

    new Chart(ctxEngagement, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'System Actions',
                data: dataPoints,
                borderColor: '#0d6efd',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: { grid: { display: false, drawBorder: false } },
                y: { 
                    grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // Storage Doughnut Chart
    const ctxStorage = document.getElementById('storageChart').getContext('2d');
    const usedStorage = <?= $stats['storage_mb'] ?? 0 ?>;
    const freeStorage = Math.max(0, 100 - usedStorage);

    new Chart(ctxStorage, {
        type: 'doughnut',
        data: {
            labels: ['Used (MB)', 'Available (MB)'],
            datasets: [{
                data: [usedStorage, freeStorage],
                backgroundColor: ['#198754', '#e9ecef'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, usePointStyle: true, padding: 20 }
                }
            }
        }
    });
});
</script>

<style>
.summary-card {
    transition: all 0.3s ease;
    border-radius: 16px;
}
.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}
</style>
