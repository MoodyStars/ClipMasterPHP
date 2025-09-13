<?php
require_once __DIR__ . '/lib.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $u = find_user_by_username($username);
    if (!$u || !verify_password($password, $u['password'])) {
        $errors[] = "Invalid username or password.";
    } else {
        $_SESSION['user'] = ['username' => $u['username'], 'email' => $u['email']];
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Log In - ClipMaster</title>
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include 'header.php'; ?>
<div id="container">
  <div id="main">
    <h2>Log In</h2>
    <?php if (!empty($errors)): ?>
      <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>
    <form action="login.php" method="post">
      <table class="formtable">
        <tr><td>Username:</td><td><input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" /></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password" /></td></tr>
        <tr><td></td><td><input type="submit" value="Log In" /></td></tr>
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