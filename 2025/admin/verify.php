<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
include('../config/db.php');

$action_msg = "";
// Approve or reject (delete)
if (isset($_GET['approve'])) {
    $sid = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE students SET verified = 1 WHERE id = ?");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $action_msg = "Student has been approved successfully.";
    $stmt->close();
} elseif (isset($_GET['reject'])) {
    $sid = intval($_GET['reject']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $action_msg = "Student has been rejected and removed.";
    $stmt->close();
}

// Fetch unverified students
$students = $conn->query("SELECT id, name, email, enrollment_no, program, branch, semester, created_at FROM students WHERE verified = 0 ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Students</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #367BF5;
      --success-color: #2ecc71;
      --danger-color: #e74c3c;
      --card-background: rgba(255, 255, 255, 0.7);
      --sidebar-background: rgba(255, 255, 255, 0.5);
      --text-color-dark: #121212;
      --text-color-light: #595959;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --border-color: rgba(255, 255, 255, 0.8);
      --table-border-color: #e0e0e0;
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
      border-right: 1px solid var(--border-color);
      padding: 2rem 1.5rem;
    }

    .sidebar-header {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 3rem;
      text-align: center;
    }
    .sidebar-header i { margin-right: 10px; }

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
    .main-header h2 { font-size: 2rem; font-weight: 600; }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 10px;
        color: #fff;
        background-color: var(--success-color);
        font-weight: 500;
    }

    /* --- Table Card --- */
    .content-card {
        background: var(--card-background);
        padding: 2rem;
        border-radius: 14px;
        box-shadow: 0 4px 20px var(--shadow-color);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    thead tr { border-bottom: 2px solid var(--table-border-color); }
    
    th, td {
        padding: 0.9rem;
        text-align: left;
    }
    th {
        font-weight: 600;
        color: var(--text-color-light);
    }

    tbody tr { border-bottom: 1px solid var(--table-border-color); }
    tbody tr:nth-child(even) { background-color: rgba(0,0,0,0.03); }
    tbody tr:last-child { border-bottom: none; }
    
    .action-buttons { display: flex; gap: 0.5rem; }
    
    .action-btn {
        text-decoration: none;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        color: #fff;
        font-weight: 500;
        font-size: 0.85rem;
        transition: opacity 0.3s;
        display: inline-flex;
        align-items: center;
    }
    .action-btn.approve { background-color: var(--success-color); }
    .action-btn.reject { background-color: var(--danger-color); }
    .action-btn:hover { opacity: 0.8; }
    .action-btn i { margin-right: 6px; }

    /* --- Responsive --- */
    @media (max-width: 992px) { /* Adjust sidebar for tablets and mobile */
        .dashboard-container { grid-template-columns: 1fr; }
        .sidebar { /* ... same responsive styles as dashboard ... */ }
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
          <a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a>
          <a href="manage_students.php"><i class="fa-solid fa-users"></i> <span>Manage Students</span></a>
          <a href="verify.php" class="active"><i class="fa-solid fa-user-check"></i> <span>Verify Students</span></a>
          <a href="upload_result.php"><i class="fa-solid fa-file-arrow-up"></i> <span>Upload Result</span></a>
          <a href="manage_results.php"><i class="fa-solid fa-list-check"></i> <span>Manage Results</span></a>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <header class="main-header">
        <h2>Verify Student Registrations</h2>
      </header>
      
      <?php if ($action_msg): ?>
        <div class="alert"><?php echo $action_msg; ?></div>
      <?php endif; ?>

      <div class="content-card">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Enrollment No</th>
              <th>Program</th>
              <th>Semester</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $students->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
                <td><?php echo htmlspecialchars($row['program']); ?></td>
                <td><?php echo intval($row['semester']); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="verify.php?approve=<?php echo $row['id']; ?>" class="action-btn approve"><i class="fa-solid fa-check"></i> Approve</a>
                    <a href="verify.php?reject=<?php echo $row['id']; ?>" class="action-btn reject" onclick="return confirm('Are you sure you want to reject and delete this student?')"><i class="fa-solid fa-times"></i> Reject</a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($students->num_rows === 0): ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 2rem;">There are no pending student verifications.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>