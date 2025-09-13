<?php
// Simple, portable helpers. Designed to work on older PHP installs (2007-era) while running in 2025.
// Storage: simple serialized files in data/ directory.
// This is a demo scaffold — do NOT use in production without hardening.

define('DATA_DIR', __DIR__ . '/data');
@mkdir(DATA_DIR, 0755, true);
@mkdir(__DIR__ . '/uploads', 0755, true);

// Load data file (returns array)
function load_data($file) {
    $path = DATA_DIR . '/' . $file;
    if (!file_exists($path)) return [];
    $s = @file_get_contents($path);
    if ($s === false) return [];
    $data = @unserialize($s);
    return is_array($data) ? $data : [];
}

function save_data($file, $data) {
    $path = DATA_DIR . '/' . $file;
    return file_put_contents($path, serialize($data)) !== false;
}

function get_categories() {
    // Predefined categories (2007-style long list)
    return [
      "Business","Cars and Vehicles","Cartoon","Comedy","Event and Party","Family",
      "Fashion and Lifestyle","Funny","Games","Howto and DIY","Miscellaneous","Music",
      "News and Politics","People and Blog","Pets and Animals","Science and Technology",
      "Potty Klasky Csupo","Mixes","Logos","Nintendo Commercial","Songs","Record",
      "Sport","Street","Travel and Holiday","Webcam"
    ];
}

// Users
function find_user_by_username($username) {
    $users = load_data('users.dat');
    foreach ($users as $u) {
        if (strcasecmp($u['username'], $username) == 0) return $u;
    }
    return null;
}

function add_user($username, $password, $email) {
    $users = load_data('users.dat');
    if (find_user_by_username($username)) return false;
    $u = [
        'username' => $username,
        'password' => make_password_hash($password),
        'email' => $email,
        'created_at' => time()
    ];
    $users[] = $u;
    return save_data('users.dat', $users);
}

function make_password_hash($password) {
    if (function_exists('password_hash')) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    // Fallback for old PHP installs — use salted md5 (not secure, only for demo).
    $salt = substr(md5(uniqid('', true)), 0, 8);
    return 'md5:' . $salt . ':' . md5($salt . $password);
}

function verify_password($password, $hash) {
    if (strpos($hash, 'md5:') === 0) {
        list(, $salt, $digest) = explode(':', $hash);
        return md5($salt . $password) === $digest;
    }
    if (function_exists('password_verify')) {
        return password_verify($password, $hash);
    }
    // Last resort — try raw compare (not recommended)
    return $hash === $password;
}

function current_user() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user'])) return $_SESSION['user'];
    return null;
}

// Videos
function add_video($meta) {
    $videos = load_data('videos.dat');
    $meta['id'] = uniqid('v', true);
    $meta['created_at'] = time();
    $videos = array_merge([$meta], $videos); // newest first
    return save_data('videos.dat', $videos);
}

function get_video($id) {
    $videos = load_data('videos.dat');
    foreach ($videos as $v) if ($v['id'] === $id) return $v;
    return null;
}