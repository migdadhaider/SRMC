<?php
// Modern, cool, and minimal redesigned homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Result Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #367BF5;
      --secondary-color: #4A4A4A;
      --background-color: #F0F2F5;
      --card-background: rgba(255, 255, 255, 0.6);
      --text-color-dark: #121212;
      --text-color-light: #595959;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --border-color: rgba(255, 255, 255, 0.8);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);
      color: var(--text-color-dark);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 2rem;
    }

    .admin-login {
      position: absolute;
      top: 2rem;
      right: 2rem;
    }

    .main-container {
      width: 100%;
      max-width: 500px; /* Reduced max-width as there's only one card now */
      background: var(--card-background);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-radius: 20px;
      border: 1px solid var(--border-color);
      box-shadow: 0 8px 32px 0 var(--shadow-color);
      padding: 3rem;
      text-align: center;
    }

    header h1 {
      font-size: 2.5rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    header p {
      font-size: 1rem;
      color: var(--text-color-light);
      margin-bottom: 2.5rem;
    }

    .card h3 {
      font-size: 1.5rem;
      font-weight: 500;
      color: var(--secondary-color);
      margin-bottom: 1.5rem;
    }

    /* General Button Styles */
    .btn {
      display: inline-block; /* Changed for positioning */
      text-decoration: none;
      font-weight: 500;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    /* Card-specific Buttons */
    .card .btn {
      display: block; /* Overrides to stack them in the card */
      margin-bottom: 0.8rem;
    }
    
    .btn.primary {
      background-color: var(--primary-color);
      color: #fff;
    }
    
    .btn.primary:hover {
        background-color: #2a62c5;
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(54, 123, 245, 0.4);
    }

    .btn.secondary {
      background: transparent;
      color: var(--primary-color);
      border: 1px solid var(--primary-color);
    }

    .btn.secondary:hover {
        background-color: rgba(54, 123, 245, 0.1);
        transform: translateY(-3px);
    }

    footer {
      position: absolute; /* Positioned at the bottom */
      bottom: 2rem;
      text-align: center;
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
    }
    
    footer a {
        color: #fff;
        font-weight: 500;
        text-decoration: none;
    }
    
    footer a:hover {
        text-decoration: underline;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }
        .admin-login {
            top: 1rem;
            right: 1rem;
        }
        .main-container {
            padding: 2rem;
            margin-top: 5rem; /* Pushes content down from admin button */
        }
        header h1 {
            font-size: 2rem;
        }
        footer {
            position: relative; /* Let it flow naturally on mobile */
            margin-top: 2rem;
        }
    }
  </style>
</head>
<body>
  <!-- Admin button at top right -->
  <div style="position: absolute; top: 20px; right: 20px;">
    <a href="admin/login.php" class="btn primary">Admin Login</a>
  </div>

  <div class="main-container">
    <header>
      <h1>SRMS Portal</h1>
      <p>Your gateway to academic results and records.</p>
    </header>

    <section class="portal-section">
      <div class="card">
        <h3>Student Access</h3>
        <a href="student/login.php" class="btn primary">Login</a>
        <a href="student/register.php" class="btn secondary">Register</a>
      </div>
    </section>
  </div>
  
  <footer>
      <p>&copy; <?php echo date("Y"); ?> | A College Project by <a href="#">Your Name</a></p>
  </footer>
</body>

</html>