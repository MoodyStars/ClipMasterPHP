<?php
require_once __DIR__ . '/lib.php';
$cat = $_GET['cat'] ?? '';
$videos = load_data('videos.dat');
$filtered = [];
if ($cat) {
    foreach ($videos as $v) {
        if (strcasecmp($v['category'], $cat) == 0) $filtered[] = $v;
    }
} else {
    $filtered = $videos;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Categories - ClipMaster</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Video Categories</h2>
    <?php if ($cat): ?>
      <h3><?php echo htmlspecialchars($cat); ?></h3>
      <?php if (empty($filtered)): ?>
        <p>No videos in this category yet.</p>
      <?php else: ?>
        <ul class="list">
          <?php foreach ($filtered as $v): ?>
            <li><a href="video.php?id=<?php echo urlencode($v['id']); ?>"><?php echo htmlspecialchars($v['title']); ?></a> <span class="small">by <?php echo htmlspecialchars($v['uploader']); ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    <?php else: ?>
      <ul class="cats">
        <?php foreach (get_categories() as $c): ?>
          <li><a href="categories.php?cat=<?php echo urlencode($c); ?>"><?php echo htmlspecialchars($c); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  <div id="sidebar">
    <h3>Featured</h3>
    <p>See our featured videos on the home page.</p>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>