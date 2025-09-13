<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib.php';
$user = current_user();
?>
<div id="topbar">
  <div class="brand"><a href="index.php">ClipMaster</a></div>
  <div class="nav">
    <a href="index.php">Home</a> |
    <a href="upload.php">Upload Videos</a> |
    <a href="categories.php">Categories</a> |
    <a href="groups.php">Groups</a> |
    <a href="members.php">Members</a> |
    <a href="contact.php">Contact</a> |
    <a href="help.php">Help</a>
  </div>
  <div class="auth">
    <?php if ($user): ?>
      Welcome, <?php echo htmlspecialchars($user['username']); ?> |
      <a href="logout.php">Log Out</a>
    <?php else: ?>
      <a href="signup.php">Sign Up</a> | <a href="login.php">Log In</a>
    <?php endif; ?>
  </div>
</div>