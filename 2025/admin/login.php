<?php
session_start();
include('../config/db.php');

$error = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();
        if (password_verify($pass, $hash)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_user'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
    $stmt->close();
}
?>
<?php
// Define a sample error for demonstration purposes.
// In a real scenario, this is set by your validation logic.
// $error = "Access Denied. Please check your credentials.";
$error = null; // Set to null to see the default state.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #367BF5;
      --card-background: rgba(255, 255, 255, 0.65);
      --text-color-dark: #121212;
      --text-color-light: #595959;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --border-color: rgba(255, 255, 255, 0.8);
      --input-bg-color: rgba(255, 255, 255, 0.5);
      --error-color: #e74c3c;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 2rem;
    }

    .login-container {
      width: 100%;
      max-width: 450px;
      background: var(--card-background);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-radius: 20px;
      border: 1px solid var(--border-color);
      box-shadow: 0 8px 32px 0 var(--shadow-color);
      padding: 3rem;
      text-align: center;
    }

    h2 {
      font-size: 2.2rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--text-color-dark);
    }

    .error {
      background-color: var(--error-color);
      color: #fff;
      padding: 0.8rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }

    form {
      text-align: left;
    }

    .input-group {
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--text-color-light);
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.9rem;
      border: 1px solid #ddd;
      background: var(--input-bg-color);
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(54, 123, 245, 0.3);
    }

    button {
      width: 100%;
      padding: 0.9rem;
      border: none;
      border-radius: 8px;
      background-color: var(--primary-color);
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button:hover {
      background-color: #2a62c5;
      transform: translateY(-3px);
      box-shadow: 0 4px 15px rgba(54, 123, 245, 0.4);
    }

    .links {
      margin-top: 1.5rem;
      font-size: 0.9rem;
    }

    .links a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: text-decoration 0.2s;
    }

    .links a:hover {
      text-decoration: underline;
    }

  </style>
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>
    
    <?php if (isset($error) && $error): ?>
      <div class='error'><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>

      <button type="submit">Login</button>
    </form>

    <div class="links">
      <a href="../index.php">&larr; Back to Home</a>
    </div>
  </div>
</body>
</html>