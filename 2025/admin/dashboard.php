<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include('../config/db.php');

// basic counts for dashboard
$student_count = 0; $pending = 0; $results_count = 0;
$r = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc(); if ($r) $student_count = $r['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM students WHERE verified = 0")->fetch_assoc(); if ($r) $pending = $r['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM result_headers")->fetch_assoc(); if ($r) $results_count = $r['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #367BF5;
      --card-background: rgba(255, 255, 255, 0.7);
      --sidebar-background: rgba(255, 255, 255, 0.5);
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
      min-height: 100vh;
    }

    .dashboard-container {
      display: grid;
      grid-template-columns: 260px 1fr;
      min-height: 100vh;
    }

    /* --- Sidebar --- */
    .sidebar {
      background: var(--sidebar-background);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-right: 1px solid var(--border-color);
      padding: 2rem 1.5rem;
      display: flex;
      flex-direction: column;
    }

    .sidebar-header {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 3rem;
      text-align: center;
    }
    .sidebar-header i {
        margin-right: 10px;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      color: var(--text-color-light);
      text-decoration: none;
      font-size: 1rem;
      font-weight: 500;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 0.5rem;
      transition: background 0.3s, color 0.3s;
    }

    .sidebar-nav a:hover, .sidebar-nav a.active {
      background-color: var(--primary-color);
      color: #fff;
    }

    .sidebar-nav a i {
      width: 20px;
      margin-right: 1rem;
    }

    /* --- Main Content --- */
    .main-content {
      padding: 2rem 3rem;
      overflow-y: auto;
    }

    .main-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .main-header h2 {
      font-size: 2rem;
      font-weight: 600;
    }

    .user-info a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
    }

    /* --- Stat Cards --- */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--card-background);
      padding: 1.5rem;
      border-radius: 14px;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      box-shadow: 0 4px 20px var(--shadow-color);
    }
    
    .stat-card .icon {
        font-size: 2.5rem;
        color: var(--primary-color);
    }

    .stat-card .info h3 {
        font-size: 2rem;
        font-weight: 600;
    }
    .stat-card .info p {
        color: var(--text-color-light);
    }

    /* --- Quick Actions --- */
    .actions-card {
        background: var(--card-background);
        padding: 2rem;
        border-radius: 14px;
        box-shadow: 0 4px 20px var(--shadow-color);
    }
    .actions-card h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    .actions-grid a {
        background-color: rgba(54, 123, 245, 0.1);
        color: var(--primary-color);
        text-decoration: none;
        padding: 1rem;
        border-radius: 10px;
        text-align: center;
        font-weight: 500;
        transition: background-color 0.3s, color 0.3s;
    }
    .actions-grid a:hover {
        background-color: var(--primary-color);
        color: #fff;
    }
    
    /* --- Responsive Design --- */
    @media (max-width: 992px) {
        .dashboard-container {
            grid-template-columns: 1fr;
        }
        .sidebar {
            grid-row: 1; /* Make sidebar appear on top */
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-right: none;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-header {
            margin-bottom: 0;
            font-size: 1.2rem;
        }
        .sidebar-nav {
            display: flex;
            gap: 0.5rem;
        }
        .sidebar-nav a {
            margin-bottom: 0;
            padding: 0.5rem 1rem;
        }
        .sidebar-nav a span { display: none; } /* Hide text on mobile nav */
        .sidebar-nav a i { margin-right: 0; }
        
        .main-content { padding: 2rem; }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <nav class="sidebar">
      <div>
        <div class="sidebar-header">
            <i class="fa-solid fa-user-shield"></i> SRMS Admin
        </div>
        <div class="sidebar-nav">
          <a href="#" class="active"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a>
          <a href="manage_students.php"><i class="fa-solid fa-users"></i> <span>Manage Students</span></a>
          <a href="verify.php"><i class="fa-solid fa-user-check"></i> <span>Verify Students</span></a>
          <a href="upload_result.php"><i class="fa-solid fa-file-arrow-up"></i> <span>Upload Result</span></a>
          <a href="manage_results.php"><i class="fa-solid fa-list-check"></i> <span>Manage Results</span></a>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <header class="main-header">
        <h2>Dashboard</h2>
        <div class="user-info">
          <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></span> | <a href="logout.php">Logout</a>
        </div>
      </header>
      
      <section class="stats-grid">
        <div class="stat-card">
          <div class="icon"><i class="fa-solid fa-graduation-cap"></i></div>
          <div class="info">
            <h3><?php echo $student_count; ?></h3>
            <p>Total Students</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="icon"><i class="fa-solid fa-hourglass-half"></i></div>
          <div class="info">
            <h3><?php echo $pending; ?></h3>
            <p>Pending Verifications</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="icon"><i class="fa-solid fa-square-poll-vertical"></i></div>
          <div class="info">
            <h3><?php echo $results_count; ?></h3>
            <p>Published Results</p>
          </div>
        </div>
      </section>

      <section class="actions-card">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="manage_students.php">Manage Students</a>
            <a href="verify.php">Verify Students</a>
            <a href="upload_result.php">Upload Result</a>
            <a href="manage_results.php">Manage Results</a>
        </div>
      </section>
    </main>
  </div>
</body>
</html>