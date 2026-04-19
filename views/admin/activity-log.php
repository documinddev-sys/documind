<div class="row mb-5 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1">Audit Logs</h2>
        <p class="text-secondary">Track every significant event across the entire platform.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="card-glass d-inline-block px-4 py-2">
            <span class="text-primary fw-bold"><?php echo $total ?? 0; ?></span>
            <span class="text-secondary ms-1">Global Events Logged</span>
        </div>
    </div>
</div>

<div class="card-glass">
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0 text-dark">
            <thead style="border-bottom: 1px solid var(--border);">
                <tr style="font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th class="ps-0">User & Context</th>
                    <th>Event Action</th>
                    <th>Target Entity</th>
                    <th>Log Description</th>
                    <th class="text-end pe-0">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No activity logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <tr style="border-bottom: 1 solid rgba(255,255,255,0.03);">
                            <td class="ps-0">
                                <div class="d-flex align-items-center gap-3 py-2">
                                    <div class="avatar-sm rounded-circle bg-primary-glow d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px; font-size: 12px;">
                                        <?php echo strtoupper(substr($activity['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($activity['name']); ?></p>
                                        <code class="text-muted small" style="font-size: 10px;"><?php echo htmlspecialchars($activity['ip_address'] ?? '0.0.0.0'); ?></code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-opacity-10 bg-primary text-primary px-2 py-1" style="font-size: 9px;"><?php echo strtoupper($activity['action']); ?></span>
                            </td>
                            <td>
                                <span class="text-secondary small"><?php echo htmlspecialchars($activity['entity_type']); ?></span>
                                <span class="text-muted small">#<?php echo $activity['entity_id'] ?: 'N/A'; ?></span>
                            </td>
                            <td>
                                <p class="mb-0 small text-dark" style="max-width: 300px;"><?php echo htmlspecialchars($activity['description'] ?? '-'); ?></p>
                            </td>
                            <td class="text-end pe-0">
                                <span class="small text-muted d-block"><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></span>
                                <span class="small text-muted" style="font-size: 10px;"><?php echo date('H:i:s', strtotime($activity['created_at'])); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item mx-1">
                    <a class="page-link border-0 card-glass <?php echo $i === $currentPage ? 'bg-primary text-white' : 'text-secondary'; ?>" href="/documind/public/admin/activity-log?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

