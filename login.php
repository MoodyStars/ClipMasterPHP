<?php
require_once __DIR__ . '/lib.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['_csrf'] ?? '')) $errors[] = "Invalid form submission.";
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $u = find_user_by_username($username);
    if (!$u || !verify_password($password, $u['password'])) {
        $errors[] = "Invalid username or password.";
    } else {
        login_user_session($u);
        header('Location: index.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Log In - <?php echo e($config['site_name']); ?></title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Log In</h2>
    <?php if ($errors): ?><div class="error"><?php echo implode('<br>', array_map('e', $errors)); ?></div><?php endif; ?>
    <form action="login.php" method="post">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <table class="formtable">
        <tr><td>Username:</td><td><input type="text" name="username" value="<?php echo e($_POST['username'] ?? '') ?>"></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password"></td></tr>
        <tr><td></td><td><input type="submit" value="Log In"></td></tr>
      </table>
    </form>
  </div>
  <div id="sidebar">
    <h3>Need an account?</h3>
    <p><a href="signup.php">Sign Up</a> — it's free.</p>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>