<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
include('../config/db.php');

// Delete header (and cascade deletes items)
if (isset($_GET['delete_header'])) {
    $hid = intval($_GET['delete_header']);
    $stmt = $conn->prepare("DELETE FROM result_headers WHERE id = ?");
    $stmt->bind_param("i", $hid);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_results.php");
    exit;
}

// List headers with student info
$res = $conn->query("SELECT rh.id, rh.student_id, rh.semester, rh.spi, rh.ppi, rh.cgpa, rh.result_class, s.name, s.enrollment_no FROM result_headers rh JOIN students s ON rh.student_id = s.id ORDER BY rh.published_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Results</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #367BF5;
      --danger-color: #e74c3c;
      --card-background: rgba(255, 255, 255, 0.7);
      --sidebar-background: rgba(255, 255, 255, 0.5);
      --text-color-dark: #121212;
      --text-color-light: #595959;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --border-color: rgba(255, 255, 255, 0.8);
      --table-border-color: #e0e0e0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);
      color: var(--text-color-dark);
      min-height: 100vh;
    }

    .dashboard-container { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }

    .sidebar { background: var(--sidebar-background); backdrop-filter: blur(15px); border-right: 1px solid var(--border-color); padding: 2rem 1.5rem; }
    .sidebar-header { font-size: 1.5rem; font-weight: 600; margin-bottom: 3rem; text-align: center; }
    .sidebar-header i { margin-right: 10px; }
    .sidebar-nav a { display: flex; align-items: center; color: var(--text-color-light); text-decoration: none; font-size: 1rem; font-weight: 500; padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem; transition: background 0.3s, color 0.3s; }
    .sidebar-nav a:hover, .sidebar-nav a.active { background-color: var(--primary-color); color: #fff; }
    .sidebar-nav a i { width: 20px; margin-right: 1rem; }

    .main-content { padding: 2rem 3rem; overflow-y: auto; }
    .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .main-header h2 { font-size: 2rem; font-weight: 600; }
    .header-actions .btn { text-decoration: none; color: #fff; background-color: var(--primary-color); padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 500; transition: background-color 0.3s; }
    .header-actions .btn:hover { background-color: #2a62c5; }
    .header-actions .btn i { margin-right: 8px; }

    .content-card { background: var(--card-background); padding: 2rem; border-radius: 14px; box-shadow: 0 4px 20px var(--shadow-color); }
    table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    thead tr { border-bottom: 2px solid var(--table-border-color); }
    th, td { padding: 1rem; text-align: left; }
    th { font-weight: 600; color: var(--text-color-light); }
    tbody tr { border-bottom: 1px solid var(--table-border-color); }
    tbody tr:nth-child(even) { background-color: rgba(0,0,0,0.03); }
    tbody tr:last-child { border-bottom: none; }
    
    .action-buttons { display: flex; gap: 0.5rem; }
    .action-btn { text-decoration: none; padding: 0.4rem 0.8rem; border-radius: 6px; color: #fff; font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s; display: inline-flex; align-items: center; }
    .action-btn:hover { opacity: 0.8; }
    .action-btn i { margin-right: 5px; }
    .action-btn.edit { background-color: var(--primary-color); }
    .action-btn.delete { background-color: var(--danger-color); }
    
    @media (max-width: 992px) { .dashboard-container { grid-template-columns: 1fr; } .main-content { padding: 2rem; } .main-header { flex-direction: column; align-items: flex-start; gap: 1rem; } }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <nav class="sidebar">
      <div>
        <div class="sidebar-header"><i class="fa-solid fa-user-shield"></i> SRMS Admin</div>
        <div class="sidebar-nav">
          <a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a>
          <a href="manage_students.php"><i class="fa-solid fa-users"></i> <span>Manage Students</span></a>
          <a href="verify.php"><i class="fa-solid fa-user-check"></i> <span>Verify Students</span></a>
          <a href="upload_result.php"><i class="fa-solid fa-file-arrow-up"></i> <span>Upload Result</span></a>
          <a href="manage_results.php" class="active"><i class="fa-solid fa-list-check"></i> <span>Manage Results</span></a>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <header class="main-header">
        <h2>Manage Results</h2>
        <div class="header-actions">
          <a href="upload_result.php" class="btn"><i class="fa-solid fa-plus"></i> Upload New Result</a>
        </div>
      </header>
      
      <div class="content-card">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Enrollment No</th>
              <th>Sem</th>
              <th>SPI</th>
              <th>PPI</th>
              <th>CGPA</th>
              <th>Class</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $res->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
                <td><?php echo intval($row['semester']); ?></td>
                <td><?php echo htmlspecialchars($row['spi'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['ppi'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['cgpa'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['result_class']); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="edit_result.php?id=<?php echo $row['id']; ?>" class="action-btn edit">
                      <i class="fa-solid fa-pencil"></i> Edit
                    </a>
                    <a href="manage_results.php?delete_header=<?php echo $row['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this result?')">
                      <i class="fa-solid fa-trash"></i> Delete
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($res->num_rows === 0): ?>
              <tr>
                <td colspan="8" style="text-align: center; padding: 2rem;">No results found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>