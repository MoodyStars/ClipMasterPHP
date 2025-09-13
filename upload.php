<?php
require_once __DIR__ . '/lib.php';
session_start();
$user = current_user();
if (!$user) {
    header('Location: login.php');
    exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'Miscellaneous';

    if (empty($title) || empty($_FILES['file']['name'])) {
        $errors[] = "Title and video file are required.";
    } else {
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload error code: " . intval($f['error']);
        } else {
            // Store file
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['flv','mp4','webm','avi','mov','wmv','mkv'];
            if (!in_array($ext, $allowed)) {
                $errors[] = "Unsupported file type: " . htmlspecialchars($ext);
            } else {
                $dest = 'uploads/' . uniqid('vid_', true) . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $errors[] = "Failed to move uploaded file.";
                } else {
                    // Add metadata
                    $meta = [
                        'title' => $title,
                        'description' => $description,
                        'filename' => $dest,
                        'thumbnail' => 'assets/placeholder.png',
                        'uploader' => $user['username'],
                        'category' => $category
                    ];
                    add_video($meta);
                    header('Location: index.php');
                    exit;
                }
            }
        }
    }
}
$cats = get_categories();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Upload Video - ClipMaster</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Upload Video</h2>
    <?php if (!empty($errors)): ?>
      <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <table class="formtable">
        <tr><td>Title:</td><td><input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? '') ?>" /></td></tr>
        <tr><td>Description:</td><td><textarea name="description"><?php echo htmlspecialchars($_POST['description'] ?? '') ?></textarea></td></tr>
        <tr><td>Category:</td>
            <td>
              <select name="category">
                <?php foreach ($cats as $c): ?>
                  <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
              </select>
            </td></tr>
        <tr><td>Video File:</td><td><input type="file" name="file" /></td></tr>
        <tr><td></td><td><input type="submit" value="Upload" /></td></tr>
      </table>
    </form>
    <p class="small">Tip: Older browsers may not support large uploads; if you have trouble, try a smaller file or use a modern browser.</p>
  </div>

  <div id="sidebar">
    <h3>Upload Quickly</h3>
    <p>Share Quickly upload, tag and share videos in almost any video format.</p>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>