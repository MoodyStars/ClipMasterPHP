<?php
require_once __DIR__ . '/lib.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '' || $email === '') {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } elseif (find_user_by_username($username)) {
        $errors[] = "Username already taken.";
    } else {
        if (add_user($username, $password, $email)) {
            $_SESSION['user'] = ['username' => $username, 'email' => $email];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Failed to create user.";
        }
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Sign Up - ClipMaster</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Sign Up — it's Free</h2>
    <?php if (!empty($errors)): ?>
      <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>
    <form action="signup.php" method="post">
      <table class="formtable">
        <tr><td>Username:</td><td><input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" /></td></tr>
        <tr><td>Email:</td><td><input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" /></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password" /></td></tr>
        <tr><td>Confirm:</td><td><input type="password" name="confirm" /></td></tr>
        <tr><td></td><td><input type="submit" value="Create Account" /></td></tr>
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