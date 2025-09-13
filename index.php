<?php
require_once __DIR__ . '/lib.php';
$user = current_user();
$page = max(1, intval($_GET['page'] ?? 1));
$q = trim($_GET['q'] ?? '');
$total = 0;
if ($q !== '') {
    $videos = search_videos($q, $page, $config['per_page'], $total);
} else {
    $videos = get_videos($page, $config['per_page'], $total);
}
$pages = max(1, (int)ceil($total / $config['per_page']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo e($config['site_name']); ?> - Home</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Featured Videos</h2>

    <form method="get" action="index.php" class="searchform">
      <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Search videos..." />
      <input type="submit" value="Search" />
    </form>

    <div class="tiles">
      <?php if (empty($videos)): ?>
        <p>No videos found. Try <a href="upload.php">uploading</a> one.</p>
      <?php else: foreach ($videos as $v): ?>
        <div class="tile">
          <a href="video.php?id=<?php echo urlencode($v['id']); ?>">
            <img src="<?php echo e($v['thumbnail'] ?: 'assets/placeholder.png'); ?>" alt="<?php echo e($v['title']); ?>" />
          </a>
          <div class="meta">
            <a href="video.php?id=<?php echo urlencode($v['id']); ?>"><?php echo e($v['title']); ?></a>
            <div class="small"><?php echo e($v['uploader']); ?> • <?php echo e($v['category']); ?></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page-1])); ?>">&laquo; Prev</a>
      <?php endif; ?>
      Page <?php echo $page; ?> of <?php echo $pages; ?>
      <?php if ($page < $pages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page+1])); ?>">Next &raquo;</a>
      <?php endif; ?>
    </div>

  </div>

  <div id="sidebar">
    <h3>Upload</h3>
    <?php if ($user): ?>
      <p><a href="upload.php">Quick Upload</a></p>
    <?php else: ?>
      <p><a href="signup.php">Sign Up</a> | <a href="login.php">Log In</a></p>
    <?php endif; ?>

    <h3>Video Categories</h3>
    <ul class="cats">
      <?php foreach (get_categories() as $cat): ?>
        <li><a href="categories.php?cat=<?php echo urlencode($cat); ?>"><?php echo e($cat); ?></a></li>
      <?php endforeach; ?>
    </ul>

    <h3>Recently Viewed</h3>
    <ul><li>[1-4 of 12] demo</li></ul>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>