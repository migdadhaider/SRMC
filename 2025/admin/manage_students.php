<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
include('../config/db.php');

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_students.php");
    exit;
}

// Fetch all students
$students = $conn->query("SELECT id, name, email, enrollment_no, branch, semester, verified FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #367BF5;
      --success-color: #2ecc71;
      --warning-color: #f39c12;
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

    .header-actions .btn {
        text-decoration: none;
        color: #fff;
        background-color: var(--primary-color);
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    .header-actions .btn:hover { background-color: #2a62c5; }
    .header-actions .btn i { margin-right: 8px; }

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
    
    .status {
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.75rem;
        color: #fff;
    }
    .status.verified { background-color: var(--success-color); }
    .status.pending { background-color: var(--warning-color); }

    .action-buttons { display: flex; gap: 0.5rem; }

    .action-btn {
        text-decoration: none;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        color: #fff;
        font-weight: 500;
        font-size: 0.85rem;
        transition: opacity 0.3s;
    }
    .action-btn.edit { background-color: var(--primary-color); }
    .action-btn.delete { background-color: var(--danger-color); }
    .action-btn:hover { opacity: 0.8; }
    .action-btn i { margin-right: 5px; }

    /* --- Responsive --- */
    @media (max-width: 992px) { /* Adjust sidebar for tablets and mobile */
        .dashboard-container { grid-template-columns: 1fr; }
        .sidebar { /* ... same responsive styles as dashboard ... */ }
        .main-content { padding: 2rem; }
        .main-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    }
    @media (max-width: 768px) {
        /* On small screens, make table more readable */
        table, thead, tbody, th, td, tr { display: block; }
        thead tr { position: absolute; top: -9999px; left: -9999px; }
        tr { border: 1px solid var(--table-border-color); border-radius: 8px; margin-bottom: 1rem; }
        td { border: none; border-bottom: 1px solid #eee; position: relative; padding-left: 50%; }
        td:before { position: absolute; top: 0.9rem; left: 0.9rem; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: 600; color: var(--text-color-light); }
        
        td:nth-of-type(1):before { content: "ID"; }
        td:nth-of-type(2):before { content: "Name"; }
        td:nth-of-type(3):before { content: "Email"; }
        td:nth-of-type(4):before { content: "Enrollment"; }
        td:nth-of-type(5):before { content: "Branch"; }
        td:nth-of-type(6):before { content: "Semester"; }
        td:nth-of-type(7):before { content: "Verified"; }
        td:nth-of-type(8):before { content: "Action"; }
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
          <a href="manage_students.php" class="active"><i class="fa-solid fa-users"></i> <span>Manage Students</span></a>
          <a href="verify.php"><i class="fa-solid fa-user-check"></i> <span>Verify Students</span></a>
          <a href="upload_result.php"><i class="fa-solid fa-file-arrow-up"></i> <span>Upload Result</span></a>
          <a href="manage_results.php"><i class="fa-solid fa-list-check"></i> <span>Manage Results</span></a>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <header class="main-header">
        <h2>Manage Students</h2>
        <div class="header-actions">
          <a href="add_student.php" class="btn"><i class="fa-solid fa-user-plus"></i> Add Student</a>
        </div>
      </header>
      
      <div class="content-card">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Enrollment No</th>
              <th>Branch</th>
              <th>Sem</th>
              <th>Verified</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($s = $students->fetch_assoc()): ?>
              <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo htmlspecialchars($s['name']); ?></td>
                <td><?php echo htmlspecialchars($s['email']); ?></td>
                <td><?php echo htmlspecialchars($s['enrollment_no']); ?></td>
                <td><?php echo htmlspecialchars($s['branch']); ?></td>
                <td><?php echo intval($s['semester']); ?></td>
                <td>
                  <span class="status <?php echo $s['verified'] ? 'verified' : 'pending'; ?>">
                    <?php echo $s['verified'] ? 'Yes' : 'No'; ?>
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <a href="edit_student.php?id=<?php echo $s['id']; ?>" class="action-btn edit"><i class="fa-solid fa-pencil"></i></a>
                    <a href="manage_students.php?delete=<?php echo $s['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this student?')"><i class="fa-solid fa-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($students->num_rows === 0): ?>
              <tr>
                <td colspan="8" style="text-align: center; padding: 2rem;">No students found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>