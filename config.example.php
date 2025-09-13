<?php
// Copy this file to config.php and adjust settings.
// Default configuration uses SQLite (no DB server required).
return [
    // PDO DSN. Examples:
    // SQLite (default): 'sqlite:' . __DIR__ . '/data/clipmaster.sqlite'
    // MySQL: 'mysql:host=localhost;dbname=clipmaster;charset=utf8mb4'
    'pdo_dsn' => 'sqlite:' . __DIR__ . '/data/clipmaster.sqlite',

    // For MySQL provide user/password:
    'pdo_user' => '',
    'pdo_pass' => '',

    // Upload settings
    'uploads_dir' => __DIR__ . '/uploads',
    'thumbs_dir'  => __DIR__ . '/uploads/thumbs',
    'max_upload_bytes' => 200 * 1024 * 1024, // 200MB

    // Allowed extensions and mime-type prefixes
    'allowed_ext' => ['mp4','webm','ogg','ogv','mov','avi','mkv','flv'],
    'allowed_mime_prefixes' => ['video/', 'application/octet-stream'],

    // Site settings
    'site_name' => 'ClipMaster',
    'per_page' => 12,
];