<?php

namespace App\Helpers;

class FileHelper
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'docx'];
    private const ALLOWED_MIMES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    private const MAX_FILENAME_LENGTH = 500;

    public static function validateUpload(array $file): void
    {
        // Check if file was actually uploaded
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('No file was uploaded');
        }

        // Check file size
        $maxBytes = (int)($_ENV['UPLOAD_MAX_MB'] ?? 20) * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new \Exception('File size exceeds maximum limit');
        }

        // Check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception('Only PDF and DOCX files are accepted');
        }

        // Check MIME type
        $mimeType = self::getMimeType($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            throw new \Exception('Invalid file type detected');
        }

        // Check for double extensions
        $nameWithoutExt = pathinfo($file['name'], PATHINFO_FILENAME);
        if (preg_match('/\.\w+\.\w+$/', $nameWithoutExt)) {
            throw new \Exception('Invalid filename format');
        }
    }

    public static function generateStoredName(string $ext): string
    {
        $uuid = self::generateUuid();
        return $uuid . '.' . strtolower($ext);
    }

    public static function generateSecureName(string $originalName, string $fileType): string
    {
        $uuid = self::generateUuid();
        return $uuid . '.' . strtolower($fileType);
    }

    public static function getMimeType(string $path): string
    {
        if (!function_exists('finfo_file')) {
            // Fallback if finfo not available
            return mime_content_type($path) ?: 'application/octet-stream';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return $mime ?: 'application/octet-stream';
    }

    public static function sanitizeFileName(string $filename): string
    {
        // Remove unsafe characters, keep only alphanumeric, dash, underscore, dot
        $filename = preg_replace('/[^a-zA-Z0-9._\-]/', '', $filename);
        $filename = substr($filename, 0, self::MAX_FILENAME_LENGTH);
        return $filename ?: 'document';
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
