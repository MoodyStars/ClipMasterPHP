<?php
require_once __DIR__ . '/lib.php';
$id = $_GET['id'] ?? '';
$video = get_video($id);
if (!$video) {
    header("HTTP/1.0 404 Not Found");
    echo "Video not found.";
    exit;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title><?php echo htmlspecialchars($video['title']); ?> - ClipMaster</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2><?php echo htmlspecialchars($video['title']); ?></h2>

    <!-- HTML5 video with legacy embed/object fallback for older browsers -->
    <div class="player">
      <?php
      $src = htmlspecialchars($video['filename']);
      $ext = strtolower(pathinfo($video['filename'], PATHINFO_EXTENSION));
      $mime = 'application/octet-stream';
      if ($ext === 'mp4') $mime = 'video/mp4';
      if ($ext === 'webm') $mime = 'video/webm';
      if ($ext === 'ogg' || $ext === 'ogv') $mime = 'video/ogg';
      ?>
      <!-- Modern browsers -->
      <video width="480" height="270" controls>
        <source src="<?php echo $src; ?>" type="<?php echo $mime; ?>">
        <!-- Fallback: object/embed for older browsers (2007-era) -->
        <object width="480" height="270" data="<?php echo $src; ?>">
          <param name="src" value="<?php echo $src; ?>">
          <embed src="<?php echo $src; ?>" width="480" height="270"></embed>
        </object>
      </video>
      <p class="small">If playback fails, download the file: <a href="<?php echo $src; ?>">Download video</a></p>
    </div>

    <div class="video-meta">
      <p><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
      <p class="small">Uploaded by <?php echo htmlspecialchars($video['uploader']); ?> • Category: <?php echo htmlspecialchars($video['category']); ?></p>
    </div>
  </div>

  <div id="sidebar">
    <h3>Share</h3>
    <p>Embed this video in your website:</p>
    <textarea rows="3" cols="30">&lt;video width="480" controls&gt;&lt;source src="<?php echo $src; ?>"&gt;&lt;/video&gt;</textarea>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>