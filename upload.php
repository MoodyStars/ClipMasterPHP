<?php
require_once __DIR__ . '/lib.php';
$user = current_user();
if (!$user) { header('Location: login.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['_csrf'] ?? '')) $errors[] = "Invalid form submission.";
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'Miscellaneous';
    if ($title === '') $errors[] = "Title is required.";
    if (empty($_FILES['file']['name'])) $errors[] = "Video file is required.";

    if (!$errors) {
        $uploaded = handle_upload_file('file', $errors, $title);
        if ($uploaded) {
            $meta = [
                'id' => uniqid('v', true),
                'title' => $title,
                'description' => $description,
                'filename' => $uploaded['filename'],
                'thumbnail' => $uploaded['thumbnail'],
                'uploader' => $user['username'],
                'category' => $category,
            ];
            if (add_video($meta)) {
                header('Location: index.php'); exit;
            } else {
                $errors[] = "Failed to save video metadata.";
            }
        }
    }
}
$cats = get_categories();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Upload Video - <?php echo e($config['site_name']); ?></title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Upload Video</h2>
    <?php if ($errors): ?><div class="error"><?php echo implode('<br>', array_map('e', $errors)); ?></div><?php endif; ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <table class="formtable">
        <tr><td>Title:</td><td><input type="text" name="title" value="<?php echo e($_POST['title'] ?? '') ?>"></td></tr>
        <tr><td>Description:</td><td><textarea name="description"><?php echo e($_POST['description'] ?? '') ?></textarea></td></tr>
        <tr><td>Category:</td>
            <td>
              <select name="category">
                <?php foreach ($cats as $c): ?><option value="<?php echo e($c); ?>"><?php echo e($c); ?></option><?php endforeach; ?>
              </select>
            </td></tr>
        <tr><td>Video File:</td><td><input type="file" name="file"></td></tr>
        <tr><td></td><td><input type="submit" value="Upload"></td></tr>
      </table>
    </form>
    <p class="small">Tip: If you have trouble uploading large files, check server upload limits (post_max_size / upload_max_filesize) or use a modern browser.</p>
  </div>
  <div id="sidebar">
    <h3>Upload Quickly</h3>
    <p>Share Quickly upload, tag and share videos in almost any video format.</p>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>