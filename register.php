<!-- pages/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | ShareToLive</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="container login-box">
    <h1 class="title">Create Account</h1>
    <form method="POST" action="../includes/auth_register.php">
  <label for="role">Role:</label>
  <select name="role" required>
    <option value="">Select Role</option>
    <option value="admin">Admin</option>
    <option value="donator">Donator</option>
    <option value="inneed">InNeed</option>
  </select><br>

  <label for="name">Full Name:</label>
  <input type="text" name="name" required><br>

  <label for="email">Email:</label>
  <input type="email" name="email" required><br>

  <label for="password">Password:</label>
  <input type="password" name="password" required><br>

  <label for="confirm_password">Confirm Password:</label>
  <input type="password" name="confirm_password" required><br>

  <button type="submit">Register</button>
</form>

    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>
