<?php
require_once __DIR__ . '/lib.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['_csrf'] ?? '')) $errors[] = "Invalid form submission.";
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } elseif (find_user_by_username($username)) {
        $errors[] = "Username already taken.";
    } else {
        if (add_user($username, $password, $email)) {
            $u = find_user_by_username($username);
            login_user_session($u);
            header('Location: index.php'); exit;
        } else {
            $errors[] = "Failed to create user (maybe username taken).";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sign Up - <?php echo e($config['site_name']); ?></title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Sign Up — it's Free</h2>
    <?php if ($errors): ?>
      <div class="error"><?php echo implode('<br>', array_map('e', $errors)); ?></div>
    <?php endif; ?>
    <form action="signup.php" method="post">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <table class="formtable">
        <tr><td>Username:</td><td><input type="text" name="username" value="<?php echo e($_POST['username'] ?? '') ?>"></td></tr>
        <tr><td>Email:</td><td><input type="text" name="email" value="<?php echo e($_POST['email'] ?? '') ?>"></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password"></td></tr>
        <tr><td>Confirm:</td><td><input type="password" name="confirm"></td></tr>
        <tr><td></td><td><input type="submit" value="Create Account"></td></tr>
      </table>
    </form>
  </div>
  <div id="sidebar">
    <h3>Why Sign Up?</h3>
    <ul>
      <li>Unlimited video hosting (demo)</li>
      <li>Quickly upload and share</li>
      <li>Put videos on your space</li>
    </ul>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>