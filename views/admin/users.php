<div class="row mb-5 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1">User Management</h2>
        <p class="text-secondary">Manage and monitor system users and their permissions.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="card-glass d-inline-block px-4 py-2">
            <span class="text-primary fw-bold"><?php echo $total ?? 0; ?></span>
            <span class="text-secondary ms-1">Total Registered Users</span>
        </div>
    </div>
</div>

<div class="card-glass">
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0 text-dark;">
            <thead style="border-bottom: 1px solid var(--border);">
                <tr style="font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th class="ps-0">User Details</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end pe-0">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No users found in the system.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                            <td class="ps-0">
                                <div class="d-flex align-items-center gap-3 py-2">
                                    <div class="avatar-sm rounded-circle bg-primary-glow d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 40px; height: 40px; font-size: 14px;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p class="mb-0 text-muted small" style="font-size: 11px;"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-opacity-10 <?php echo $user['role'] === 'admin' ? 'bg-danger text-danger' : 'bg-secondary text-secondary'; ?> px-2 py-1" style="font-size: 10px;">
                                    <?php echo strtoupper($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-opacity-10 <?php echo $user['is_active'] ? 'bg-success text-success' : 'bg-warning text-warning'; ?> px-2 py-1" style="font-size: 10px;">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                    <?php echo $user['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            </td>
                            <td class="text-end pe-0">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="/documind/public/admin/users/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View</a>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <button class="btn btn-sm btn-outline-warning make-admin-btn rounded-pill px-3" data-user-id="<?php echo $user['id']; ?>">Make Admin</button>
                                    <?php endif; ?>
                                    <?php if ($user['id'] !== 1): ?>
                                        <?php if ($user['is_active']): ?>
                                            <button class="btn btn-sm btn-outline-danger deactivate-btn rounded-pill px-3" data-user-id="<?php echo $user['id']; ?>">Deactivate</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success reactivate-btn rounded-pill px-3" data-user-id="<?php echo $user['id']; ?>">Reactivate</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 user-select-none d-flex align-items-center" style="font-size: 10px;">SYSTEM ADMIN</span>
                                    <?php endif; ?>
                                </div>
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
                    <a class="page-link border-0 card-glass <?php echo $i === $currentPage ? 'bg-primary text-white' : 'text-secondary'; ?>" href="/documind/public/admin/users?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
document.querySelectorAll('.make-admin-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const userId = e.currentTarget.dataset.userId;
        const result = await Swal.fire({
            title: 'Promote User?',
            text: "Are you sure you want to promote this user to an Admin role? This grant permanent administrative privileges.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Promote'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/documind/public/admin/make-admin', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({user_id: userId})
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', data.error || 'Promotion failed', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error or permission denied', 'error');
            }
        }
    });
});

document.querySelectorAll('.deactivate-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const userId = e.currentTarget.dataset.userId;
        const result = await Swal.fire({
            title: 'Deactivate User?',
            text: "The user will no longer be able to sign in or access their documents.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Deactivate'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/documind/public/admin/deactivate-user', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({user_id: userId})
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', data.error || 'Deactivation failed', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'System error during deactivation', 'error');
            }
        }
    });
});

document.querySelectorAll('.reactivate-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const userId = e.currentTarget.dataset.userId;
        const result = await Swal.fire({
            title: 'Reactivate User?',
            text: "Restore access for this user to the system.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reactivate'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/documind/public/admin/reactivate-user', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({user_id: userId})
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', data.error || 'Reactivation failed', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'System error during reactivation', 'error');
            }
        }
    });
});
</script>

