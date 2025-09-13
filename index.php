<?php
require_once __DIR__ . '/lib.php';
$videos = load_data('videos.dat');
$featured = array_slice($videos, 0, 6);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>ClipMaster - Share your Memes with the world</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>

<div id="container">
  <div id="main">
    <h2>Featured Videos</h2>
    <div class="tiles">
      <?php if (empty($featured)): ?>
        <p>No videos yet. Be the first to <a href="upload.php">upload</a>!</p>
      <?php else: ?>
        <?php foreach ($featured as $v): ?>
          <div class="tile">
            <a href="video.php?id=<?php echo urlencode($v['id']); ?>">
              <img src="<?php echo htmlspecialchars($v['thumbnail'] ?: 'assets/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($v['title']); ?>" />
            </a>
            <div class="meta">
              <a href="video.php?id=<?php echo urlencode($v['id']); ?>"><?php echo htmlspecialchars($v['title']); ?></a>
              <div class="small"><?php echo htmlspecialchars($v['uploader']); ?> • <?php echo htmlspecialchars($v['category']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <p class="lead">Better than watch television, watch videos which you want, when you want it!</p>
  </div>

  <div id="sidebar">
    <h3>Upload</h3>
    <p><a href="upload.php">Quick Upload</a> — it's free.</p>

    <h3>Video Categories</h3>
    <ul class="cats">
      <?php foreach (get_categories() as $cat): ?>
        <li><a href="categories.php?cat=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a></li>
      <?php endforeach; ?>
    </ul>

    <h3>Recently Viewed</h3>
    <ul>
      <li>[1-4 of 12] (demo)</li>
    </ul>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>