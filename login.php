<!-- pages/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ShareToLive | Login</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
  <div class="container login-box">
    <h1 class="title">Welcome to ShareToLive</h1>
    <div class="form-group">
      <!-- pages/login.php -->
<form method="POST" action="../includes/auth_login.php">
  <h2>🔐 Login to ShareToLive</h2>

  <label for="role">Role:</label>
  <select name="role" required>
    <option value="">-- Select Role --</option>
    <option value="admin">Admin</option>
    <option value="donator">Donator</option>
    <option value="inneed">InNeed</option>
  </select><br>

  <label>Email:</label>
  <input type="email" name="email" required><br>

  <label>Password:</label>
  <input type="password" name="password" required><br>

  <button type="submit">Login</button>
</form>

      <p>or</p>
      <div id="g_id_onload"
           data-client_id="YOUR_GOOGLE_CLIENT_ID"
           data-login_uri="auth_google.php"
           data-auto_prompt="false">
      </div>
      <div class="g_id_signin" data-type="standard"></div>
      <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
  </div>
</body>
</html>
