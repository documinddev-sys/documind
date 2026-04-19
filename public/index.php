<?php

// Front Controller - Single entry point for all requests
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load autoloader and environment
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../router.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configure sessions with hardened security settings
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Initialize database connection
try {
    $dbConfig = require __DIR__ . '/../config/database.php';
    $pdo = \App\Helpers\Database::getInstance($dbConfig);
    \App\Models\BaseModel::setPdo($pdo);
} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection failed. Please check your configuration.";
    exit;
}

// Global exception handler
set_exception_handler(function(Throwable $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    http_response_code(500);
    include __DIR__ . '/../views/errors/500.php';
    exit;
});

// Create and dispatch router
$router = new App\Router();

// Auth routes
$router->get('/login', ['App\Controllers\AuthController', 'showLogin']);
$router->post('/login', ['App\Controllers\AuthController', 'login']);
$router->get('/register', ['App\Controllers\AuthController', 'showRegister']);
$router->post('/register', ['App\Controllers\AuthController', 'register']);
$router->post('/logout', ['App\Controllers\AuthController', 'logout']);

// OAuth routes
$router->get('/auth/google', ['App\Controllers\AuthController', 'googleLogin']);
$router->get('/auth/google/callback', ['App\Controllers\AuthController', 'googleCallback']);

// Public Library routes (NO authentication required)
$router->get('/library', ['App\Controllers\PublicController', 'library']);
$router->get('/library/(?P<id>\d+)', ['App\Controllers\PublicController', 'viewDocument']);

// Dashboard
$router->get('/', ['App\Controllers\PublicController', 'library']);
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index'], ['App\Middleware\AuthMiddleware']);

// Document routes
$router->get('/documents', ['App\Controllers\DocumentController', 'index']);
$router->get('/documents/list-json', ['App\Controllers\DocumentController', 'listJson']);
$router->get('/documents/upload', ['App\Controllers\DocumentController', 'showUpload']);
$router->post('/documents/upload', ['App\Controllers\DocumentController', 'upload']);
$router->get('/documents/search', ['App\Controllers\DocumentController', 'search']);
$router->post('/documents/ask', ['App\Controllers\DocumentController', 'askQuestion']);
$router->get('/documents/(?P<id>\d+)', ['App\Controllers\DocumentController', 'view']);
$router->get('/documents/(?P<id>\d+)/download', ['App\Controllers\DocumentController', 'download']);
$router->get('/documents/(?P<id>\d+)/inline', ['App\Controllers\DocumentController', 'inline']);
$router->post('/documents/(?P<id>\d+)/delete', ['App\Controllers\DocumentController', 'delete']);
$router->post('/documents/(?P<id>\d+)/toggle-favorite', ['App\Controllers\DocumentController', 'toggleFavorite']);
$router->post('/documents/bulk-share', ['App\Controllers\DocumentController', 'bulkShare']);
$router->post('/documents/bulk-delete', ['App\Controllers\DocumentController', 'bulkDelete']);

// AI Chat routes
$router->get('/ai/chat/(?P<documentId>\d+)', ['App\Controllers\AiController', 'chat']);
$router->post('/ai/send', ['App\Controllers\AiController', 'sendMessage']);
$router->get('/ai/history/(?P<documentId>\d+)', ['App\Controllers\AiController', 'getChatHistory']);
$router->post('/ai/(?P<documentId>\d+)/clear', ['App\Controllers\AiController', 'clearHistory']);

// Admin routes
$router->get('/admin', ['App\Controllers\AdminController', 'dashboard'], ['App\Middleware\AdminMiddleware']);
$router->get('/admin/dashboard', ['App\Controllers\AdminController', 'dashboard'], ['App\Middleware\AdminMiddleware']);
$router->get('/admin/users', ['App\Controllers\AdminController', 'users'], ['App\Middleware\AdminMiddleware']);
$router->get('/admin/users/(?P<id>\d+)', ['App\Controllers\AdminController', 'viewUser'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/deactivate-user', ['App\Controllers\AdminController', 'deactivateUser'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/reactivate-user', ['App\Controllers\AdminController', 'reactivateUser'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/make-admin', ['App\Controllers\AdminController', 'makeAdmin'], ['App\Middleware\AdminMiddleware']);
$router->get('/admin/documents', ['App\Controllers\AdminController', 'documents'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/delete-document', ['App\Controllers\AdminController', 'deleteDocument'], ['App\Middleware\AdminMiddleware']);
$router->get('/admin/activity-log', ['App\Controllers\AdminController', 'activityLog'], ['App\Middleware\AdminMiddleware']);

// Phase 5: Admin approval workflow
$router->get('/admin/pending-documents', ['App\Controllers\AdminController', 'pendingDocuments'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/approve-document', ['App\Controllers\AdminController', 'approveDocument'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/reject-document', ['App\Controllers\AdminController', 'rejectDocument'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/toggle-public', ['App\Controllers\AdminController', 'toggleDocumentPublic'], ['App\Middleware\AdminMiddleware']);
$router->post('/admin/update-user-limits', ['App\Controllers\AdminController', 'updateUserLimits'], ['App\Middleware\AdminMiddleware']);

// User profile routes
$router->get('/user/profile', ['App\Controllers\UserController', 'profile']);
$router->get('/user/analytics', ['App\Controllers\AnalyticsController', 'index']);
$router->post('/user/update-profile', ['App\Controllers\UserController', 'updateProfile']);
$router->post('/user/change-password', ['App\Controllers\UserController', 'changePassword']);
$router->get('/user/notifications', ['App\Controllers\UserController', 'notifications']);
$router->post('/user/mark-notification-read', ['App\Controllers\UserController', 'markNotificationRead']);
$router->get('/user/unread-count', ['App\Controllers\UserController', 'getUnreadCount']);
$router->get('/user/shared-with-me', ['App\Controllers\ShareController', 'sharedWithMe']);

// Document sharing routes
$router->get('/share/get-shares/(?P<documentId>\d+)', ['App\Controllers\ShareController', 'getShares']);
$router->post('/share/share', ['App\Controllers\ShareController', 'share']);
$router->post('/share/update-permission', ['App\Controllers\ShareController', 'updatePermission']);
$router->post('/share/unshare', ['App\Controllers\ShareController', 'unshare']);

// Collection routes
$router->get('/collections', ['App\Controllers\CollectionController', 'index']);
$router->get('/collections/list-json', ['App\Controllers\CollectionController', 'listJson']);
$router->post('/collections/create', ['App\Controllers\CollectionController', 'create']);
$router->get('/collections/(?P<id>\d+)', ['App\Controllers\CollectionController', 'view']);
$router->post('/collections/add-document', ['App\Controllers\CollectionController', 'addDocument']);
$router->post('/collections/remove-document', ['App\Controllers\CollectionController', 'removeDocument']);
$router->post('/collections/(?P<id>\d+)/delete', ['App\Controllers\CollectionController', 'delete']);

// Dispatch the request
$router->dispatch();
