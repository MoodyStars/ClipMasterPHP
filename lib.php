<?php
// lib.php - improved ClipMaster helpers with PDO + secure defaults and fallbacks.
// Designed to be backwards-compatible with the previous file-based scaffold.
// Place config.php (copied from config.example.php) next to this file.

if (session_status() === PHP_SESSION_NONE) session_start();

$config = file_exists(__DIR__ . '/config.php') ? include __DIR__ . '/config.php' : include __DIR__ . '/config.example.php';

// Ensure directories exist
@mkdir(__DIR__ . '/data', 0755, true);
@mkdir($config['uploads_dir'], 0755, true);
@mkdir($config['thumbs_dir'], 0755, true);

// Database connection (PDO). If it fails, fallback to file storage.
function db() {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($config['pdo_dsn'], $config['pdo_user'] ?? null, $config['pdo_pass'] ?? null, $options);
        // Ensure tables exist for SQLite or MySQL (idempotent)
        initialize_schema($pdo);
        return $pdo;
    } catch (Exception $e) {
        // Logging could be added, but silently fallback to file store for demo.
        $pdo = null;
        return null;
    }
}

function initialize_schema(PDO $pdo) {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    // Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        email VARCHAR(255),
        created_at INTEGER
    )");
    // Videos
    $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
        id VARCHAR(64) PRIMARY KEY,
        title TEXT,
        description TEXT,
        filename TEXT,
        thumbnail TEXT,
        uploader TEXT,
        category TEXT,
        created_at INTEGER
    )");
    // Comments
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        video_id VARCHAR(64),
        author VARCHAR(100),
        body TEXT,
        created_at INTEGER
    )");
}

// CSRF helpers
function csrf_token() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}
function csrf_check($token) {
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], (string)$token);
}

// Simple sanitization for display
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Password helpers
function make_password_hash($password) {
    if (function_exists('password_hash')) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    // Fallback (not ideal) - salted sha256
    $salt = bin2hex(random_bytes(8));
    return "sha256:$salt:" . hash('sha256', $salt . $password);
}
function verify_password($password, $hash) {
    if (strpos($hash, 'sha256:') === 0) {
        list(, $salt, $digest) = explode(':', $hash, 3);
        return hash('sha256', $salt . $password) === $digest;
    }
    if (function_exists('password_verify')) return password_verify($password, $hash);
    return false;
}

// User management
function find_user_by_username($username) {
    $pdo = db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username)=LOWER(:u) LIMIT 1");
        $stmt->execute([':u' => $username]);
        return $stmt->fetch() ?: null;
    }
    // Fallback file store
    $users = load_data('users.dat');
    foreach ($users as $u) {
        if (strcasecmp($u['username'], $username) == 0) return $u;
    }
    return null;
}

function add_user($username, $password, $email) {
    $pdo = db();
    $now = time();
    $hash = make_password_hash($password);
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO users (username,password,email,created_at) VALUES (:u,:p,:e,:c)");
        try {
            return $stmt->execute([':u' => $username, ':p' => $hash, ':e' => $email, ':c' => $now]);
        } catch (Exception $e) {
            return false;
        }
    }
    // File fallback
    $users = load_data('users.dat');
    foreach ($users as $u) if (strcasecmp($u['username'], $username) == 0) return false;
    $users[] = ['username'=>$username, 'password'=>$hash, 'email'=>$email, 'created_at'=>$now];
    return save_data('users.dat', $users);
}

// Session helpers
function login_user_session($user) {
    $_SESSION['user'] = ['username' => $user['username'], 'email' => $user['email'] ?? ''];
}
function current_user() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}
function logout() {
    $_SESSION = [];
    @session_destroy();
}

// Video management
function add_video($meta) {
    $pdo = db();
    $id = $meta['id'] ?? uniqid('v', true);
    $now = time();
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO videos (id,title,description,filename,thumbnail,uploader,category,created_at) VALUES (:id,:t,:d,:f,:thumb,:u,:c,:created)");
        return $stmt->execute([
            ':id'=>$id, ':t'=>$meta['title'], ':d'=>$meta['description'] ?? '', ':f'=>$meta['filename'],
            ':thumb'=>$meta['thumbnail'] ?? '', ':u'=>$meta['uploader'] ?? '', ':c'=>$meta['category'] ?? '', ':created'=>$now
        ]);
    }
    // file fallback
    $videos = load_data('videos.dat');
    $meta['id'] = $id;
    $meta['created_at'] = $now;
    array_unshift($videos, $meta);
    return save_data('videos.dat', $videos);
}

function get_video($id) {
    $pdo = db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch() ?: null;
    }
    $videos = load_data('videos.dat');
    foreach ($videos as $v) if ($v['id'] === $id) return $v;
    return null;
}

function get_videos($page = 1, $per_page = 12, &$total = null) {
    $pdo = db();
    $offset = max(0, ($page - 1) * $per_page);
    if ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM videos");
        $total = (int)($stmt->fetch()['c'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM videos ORDER BY created_at DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':lim', (int)$per_page, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    $videos = load_data('videos.dat');
    $total = count($videos);
    return array_slice($videos, $offset, $per_page);
}

function search_videos($q, $page=1, $per_page=12, &$total=null) {
    $pdo = db();
    $offset = max(0, ($page - 1) * $per_page);
    if ($pdo) {
        $like = '%' . strtolower($q) . '%';
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM videos WHERE LOWER(title) LIKE :q OR LOWER(description) LIKE :q");
        $stmt->execute([':q'=>$like]);
        $total = (int)($stmt->fetch()['c'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE LOWER(title) LIKE :q OR LOWER(description) LIKE :q ORDER BY created_at DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':q', $like, PDO::PARAM_STR);
        $stmt->bindValue(':lim', (int)$per_page, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    $videos = load_data('videos.dat');
    $filtered = [];
    foreach ($videos as $v) {
        if (stripos($v['title'] ?? '', $q) !== false || stripos($v['description'] ?? '', $q) !== false) $filtered[] = $v;
    }
    $total = count($filtered);
    return array_slice($filtered, ($page-1)*$per_page, $per_page);
}

// Comments
function add_comment($video_id, $author, $body) {
    $pdo = db();
    $now = time();
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO comments (video_id,author,body,created_at) VALUES (:v,:a,:b,:c)");
        return $stmt->execute([':v'=>$video_id, ':a'=>$author, ':b'=>$body, ':c'=>$now]);
    }
    $comments = load_data('comments.dat');
    $comments[] = ['video_id'=>$video_id,'author'=>$author,'body'=>$body,'created_at'=>$now];
    return save_data('comments.dat', $comments);
}

function get_comments($video_id) {
    $pdo = db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE video_id=:v ORDER BY created_at ASC");
        $stmt->execute([':v'=>$video_id]);
        return $stmt->fetchAll();
    }
    $comments = load_data('comments.dat');
    $out = [];
    foreach ($comments as $c) if ($c['video_id'] === $video_id) $out[] = $c;
    return $out;
}

// File storage helpers (fallback)
function load_data($file) {
    $path = __DIR__ . '/data/' . $file;
    if (!file_exists($path)) return [];
    $s = @file_get_contents($path);
    if ($s === false) return [];
    $data = @unserialize($s);
    return is_array($data) ? $data : [];
}
function save_data($file, $data) {
    $path = __DIR__ . '/data/' . $file;
    return file_put_contents($path, serialize($data)) !== false;
}

// Categories
function get_categories() {
    return [
      "Business","Cars and Vehicles","Cartoon","Comedy","Event and Party","Family",
      "Fashion and Lifestyle","Funny","Games","Howto and DIY","Miscellaneous","Music",
      "News and Politics","People and Blog","Pets and Animals","Science and Technology",
      "Potty Klasky Csupo","Mixes","Logos","Nintendo Commercial","Songs","Record",
      "Sport","Street","Travel and Holiday","Webcam"
    ];
}

// Upload handling (file move + optional thumbnail generation)
function handle_upload_file($file_field, &$errors, $title_hint = '') {
    global $config;
    if (empty($_FILES[$file_field]['name'])) {
        $errors[] = "No file uploaded.";
        return null;
    }
    $f = $_FILES[$file_field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error code: " . intval($f['error']);
        return null;
    }
    if ($f['size'] > $config['max_upload_bytes']) {
        $errors[] = "File exceeds max upload size.";
        return null;
    }

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $config['allowed_ext'])) {
        $errors[] = "Unsupported extension: " . e($ext);
        return null;
    }

    // MIME check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $f['tmp_name']) : mime_content_type($f['tmp_name']);
    if ($finfo) finfo_close($finfo);
    $ok_mime = false;
    foreach ($config['allowed_mime_prefixes'] as $p) {
        if (strpos($mime, $p) === 0) { $ok_mime = true; break; }
    }
    // Some servers produce application/octet-stream; allow but warn.
    if (!$ok_mime) {
        $errors[] = "Unrecognized MIME type: " . e($mime);
        return null;
    }

    $dest_name = uniqid('vid_', true) . '.' . $ext;
    $dest = rtrim($config['uploads_dir'], '/\\') . DIRECTORY_SEPARATOR . $dest_name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        $errors[] = "Failed to move uploaded file.";
        return null;
    }

    // Try to generate a thumbnail (requires ffmpeg). Silently ignore if not available.
    $thumb = generate_thumbnail($dest, $dest_name);

    return ['filename' => 'uploads/' . basename($dest), 'thumbnail' => $thumb];
}

function generate_thumbnail($filepath, $basename) {
    global $config;
    $thumbs_dir = rtrim($config['thumbs_dir'], '/\\') . DIRECTORY_SEPARATOR;
    $thumb_name = $basename . '.jpg';
    $thumb_path = $thumbs_dir . $thumb_name;

    // Check for ffmpeg
    $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null'));
    if (!$ffmpeg) {
        // No ffmpeg, return placeholder
        return 'assets/placeholder.png';
    }
    // Build command: capture frame at 2 seconds
    $cmd = escapeshellcmd($ffmpeg) . ' -y -i ' . escapeshellarg($filepath) . ' -ss 00:00:02 -vframes 1 -vf "scale=320:-1" ' . escapeshellarg($thumb_path) . ' 2>&1';
    @exec($cmd, $out, $rc);
    if ($rc === 0 && file_exists($thumb_path)) {
        // Return relative path for web
        return 'uploads/thumbs/' . basename($thumb_path);
    }
    return 'assets/placeholder.png';
}