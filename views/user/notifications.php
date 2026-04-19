
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3">Notifications</h1>
            <p class="text-muted">Stay updated with system notifications</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (empty($notifications)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                        No notifications yet
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="list-group-item notification-item <?php echo !$notif['is_read'] ? 'bg-light' : ''; ?>" data-notification-id="<?php echo $notif['id']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="badge bg-primary">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></small>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                    <button class="btn btn-sm btn-link mark-read-btn" data-notif-id="<?php echo $notif['id']; ?>">Mark as read</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.mark-read-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const notifId = e.target.dataset.notifId;
        try {
            const response = await fetch('/documind/public/user/mark-notification-read', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({notification_id: notifId})
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Failed to mark notification as read');
        }
    });
});
</script>

