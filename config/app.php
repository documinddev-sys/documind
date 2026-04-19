<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'DocuMind',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'upload_max_mb' => (int)($_ENV['UPLOAD_MAX_MB'] ?? 20),
    'log_level' => $_ENV['LOG_LEVEL'] ?? 'error',

    // Phase 5: Default quotas (can be overridden per-user by admin)
    'default_upload_limit' => (int)($_ENV['DEFAULT_UPLOAD_LIMIT'] ?? 10),
    'default_daily_ai_limit' => (int)($_ENV['DEFAULT_DAILY_AI_LIMIT'] ?? 20),
];
