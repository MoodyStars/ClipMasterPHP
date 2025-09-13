<?php
require_once __DIR__ . '/lib.php';
$id = $_GET['id'] ?? '';
$video = get_video($id);
if (!$video) { header("HTTP/1.0 404 Not Found"); echo "Video not found."; exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!csrf_check($_POST['_csrf'] ?? '')) $errors[] = "Invalid form submission.";
    $author = current_user()['username'] ?? ($_POST['author'] ?? 'Guest');
    $body = trim($_POST['comment'] ?? '');
    if ($body === '') $errors[] = "Comment cannot be empty.";
    if (!$errors) {
        add_comment($video['id'], $author, $body);
        header('Location: video.php?id=' . urlencode($id)); exit;
    }
}
$comments = get_comments($video['id']);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo e($video['title']); ?> - <?php echo e($config['site_name']); ?></title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2><?php echo e($video['title']); ?></h2>

    <div class="player">
      <?php
      $src = e($video['filename']);
      $ext = strtolower(pathinfo($video['filename'], PATHINFO_EXTENSION));
      $mime = 'application/octet-stream';
      if ($ext === 'mp4') $mime = 'video/mp4';
      if ($ext === 'webm') $mime = 'video/webm';
      if ($ext === 'ogg' || $ext === 'ogv') $mime = 'video/ogg';
      ?>
      <video width="640" height="360" controls poster="<?php echo e($video['thumbnail'] ?: 'assets/placeholder.png'); ?>">
        <source src="<?php echo $src; ?>" type="<?php echo $mime; ?>">
        <object width="640" height="360" data="<?php echo $src; ?>">
          <param name="src" value="<?php echo $src; ?>">
          <embed src="<?php echo $src; ?>" width="640" height="360"></embed>
        </object>
      </video>
      <p class="small">If playback fails, download the file: <a href="<?php echo $src; ?>">Download video</a></p>
    </div>

    <div class="video-meta">
      <p><?php echo nl2br(e($video['description'])); ?></p>
      <p class="small">Uploaded by <?php echo e($video['uploader']); ?> • Category: <?php echo e($video['category']); ?></p>
    </div>

    <h3>Comments</h3>
    <?php if ($comments): foreach ($comments as $c): ?>
      <div class="comment"><strong><?php echo e($c['author']); ?></strong> <span class="small"><?php echo date('Y-m-d H:i', $c['created_at'] ?? time()); ?></span>
        <div><?php echo nl2br(e($c['body'])); ?></div>
      </div>
    <?php endforeach; else: ?>
      <p>No comments yet. Be the first to comment.</p>
    <?php endif; ?>

    <h4>Post a comment</h4>
    <?php if ($errors): ?><div class="error"><?php echo implode('<br>', array_map('e', $errors)); ?></div><?php endif; ?>
    <form method="post" action="video.php?id=<?php echo urlencode($video['id']); ?>">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <?php if (!current_user()): ?>
        <p><label>Your name: <input type="text" name="author" value="<?php echo e($_POST['author'] ?? '') ?>"></label></p>
      <?php endif; ?>
      <p><textarea name="comment" rows="4" cols="60"><?php echo e($_POST['comment'] ?? '') ?></textarea></p>
      <p><input type="submit" value="Post Comment"></p>
    </form>

  </div>
  <div id="sidebar">
    <h3>Share</h3>
    <p>Embed this video:</p>
    <textarea rows="3" cols="30">&lt;video width="640" controls poster="<?php echo e($video['thumbnail']); ?>"&gt;&lt;source src="<?php echo $src; ?>"&gt;&lt;/video&gt;</textarea>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>