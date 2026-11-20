<?php
declare(strict_types=1);

/**
 * Validate and upload an event poster image (.png, .jpg, .jpeg, .webp, max 5MB).
 * Returns ['path' => ?string, 'error' => ?string]
 */
function rnAdminUploadPoster(string $fieldName, string $prefix = 'poster_'): array
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'File upload failed. Please try again.'];
    }

    $fileTmp  = $_FILES[$fieldName]['tmp_name'];
    $fileName = $_FILES[$fieldName]['name'];
    $fileSize = (int) $_FILES[$fieldName]['size'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
        return ['path' => null, 'error' => 'Invalid format! Only .png, .jpg, and .webp files are allowed.'];
    }

    if ($fileSize > 5 * 1024 * 1024) {
        return ['path' => null, 'error' => 'Poster file size must be under 5MB.'];
    }

    if (@getimagesize($fileTmp) === false) {
        return ['path' => null, 'error' => 'Uploaded file is not a valid image.'];
    }

    $newFileName = $prefix . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
    $uploadDir   = __DIR__ . '/../../assets/images/events/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
        return ['path' => null, 'error' => 'Failed to save uploaded image on server.'];
    }

    return ['path' => 'assets/images/events/' . $newFileName, 'error' => null];
}

/**
 * Safely remove an old poster file from disk.
 */
function rnAdminDeleteFile(?string $relativePath): void
{
    if (empty($relativePath)) {
        return;
    }

    $fullPath = __DIR__ . '/../../' . ltrim($relativePath, '/');
    if (file_exists($fullPath) && is_file($fullPath)) {
        @unlink($fullPath);
    }
}