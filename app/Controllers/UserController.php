<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Services\Logger;

class UserController extends BaseController
{
    private $userModel;
    private $notificationModel;
    private $logger;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/documind/public/login');
        }

        $this->userModel = new User();
        $this->notificationModel = new Notification();
        $this->logger = new Logger();
    }

    /**
     * User profile page
     */
    public function profile(): void
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            $this->abort(404);
        }

        $this->render('user.profile', [
            'title' => 'My Profile',
            'user' => $user,
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $name = $input['name'] ?? null;

        if (empty($name) || strlen($name) < 2) {
            $this->json(['success' => false, 'error' => 'Invalid name'], 400);
        }

        try {
            $this->userModel->update($userId, ['name' => $name]);
            $_SESSION['user_name'] = $name;

            $this->logger->info("Profile updated: User=$userId");

            $this->json(['success' => true, 'message' => 'Profile updated']);
        } catch (\Exception $e) {
            $this->logger->error("Profile update failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $currentPassword = $input['current_password'] ?? null;
        $newPassword = $input['new_password'] ?? null;
        $confirmPassword = $input['confirm_password'] ?? null;

        // Validate
        if (empty($currentPassword) || empty($newPassword)) {
            $this->json(['success' => false, 'error' => 'Missing required fields'], 400);
        }

        if (strlen($newPassword) < 8) {
            $this->json(['success' => false, 'error' => 'Password must be at least 8 characters'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'error' => 'Passwords do not match'], 400);
        }

        try {
            $user = $this->userModel->findById($userId);

            if (!password_verify($currentPassword, $user['password'])) {
                $this->json(['success' => false, 'error' => 'Current password is incorrect'], 400);
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->userModel->update($userId, ['password' => $hashedPassword]);

            $this->logger->info("Password changed: User=$userId");

            $this->json(['success' => true, 'message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            $this->logger->error("Change password failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function notifications(): void
    {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getUserNotifications($userId, 50);

        $this->render('user.notifications', [
            'title' => 'Notifications',
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['notification_id'] ?? 0);
            
            if (!$id) {
                $this->json(['success' => false, 'error' => 'Invalid notification ID'], 400);
                return;
            }

            $userId = $_SESSION['user_id'];
            $this->notificationModel->markAsRead($id, $userId);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(): void
    {
        $userId = $_SESSION['user_id'];
        $count = $this->notificationModel->getUnreadCount($userId);

        $this->json(['success' => true, 'unread_count' => $count]);
    }
}
