<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
include('../config/db.php');

$msg = "";
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: manage_students.php");
    exit;
}

// Student fetch
$stmt = $conn->prepare("SELECT id, name, email, enrollment_no, branch, semester, verified FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$student = $res->fetch_assoc();
$stmt->close();

if (!$student) {
    // To prevent rendering a broken page, we can set a specific error message and not show the form.
    $msg = "Error: Student with the specified ID was not found.";
}

// Update process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $enroll = trim($_POST['enrollment_no']);
    $branch = trim($_POST['branch']);
    $semester = intval($_POST['semester']);
    $verified = isset($_POST['verified']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, enrollment_no=?, branch=?, semester=?, verified=? WHERE id=?");
    $stmt->bind_param("ssssiii", $name, $email, $enroll, $branch, $semester, $verified, $id);

    if ($stmt->execute()) {
        $msg = "Student details updated successfully!";
        // Fetch updated details to show immediately on the form
        $student['name'] = $name;
        $student['email'] = $email;
        $student['enrollment_no'] = $enroll;
        $student['branch'] = $branch;
        $student['semester'] = $semester;
        $student['verified'] = $verified;
    } else {
        $msg = "Update failed: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Student</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #367BF5;
      --success-color: #2ecc71;
      --card-background: rgba(255, 255, 255, 0.7);
      --sidebar-background: rgba(255, 255, 255, 0.5);
      --text-color-dark: #121212;
      --text-color-light: #595959;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --border-color: rgba(255, 255, 255, 0.8);
      --input-bg-color: rgba(255, 255, 255, 0.5);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);
      color: var(--text-color-dark);
      min-height: 100vh;
    }

    .dashboard-container { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
    
    /* --- Sidebar --- */
    .sidebar { background: var(--sidebar-background); backdrop-filter: blur(15px); border-right: 1px solid var(--border-color); padding: 2rem 1.5rem; }
    .sidebar-header { font-size: 1.5rem; font-weight: 600; margin-bottom: 3rem; text-align: center; }
    .sidebar-header i { margin-right: 10px; }
    .sidebar-nav a { display: flex; align-items: center; color: var(--text-color-light); text-decoration: none; font-size: 1rem; font-weight: 500; padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem; transition: background 0.3s, color 0.3s; }
    .sidebar-nav a:hover, .sidebar-nav a.active { background-color: var(--primary-color); color: #fff; }
    .sidebar-nav a i { width: 20px; margin-right: 1rem; }

    /* --- Main Content --- */
    .main-content { padding: 2rem 3rem; overflow-y: auto; }
    .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .main-header h2 { font-size: 2rem; font-weight: 600; }
    .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: 10px; color: #fff; background-color: var(--success-color); font-weight: 500; }
    .alert.error { background-color: #e74c3c; }

    .content-card { background: var(--card-background); padding: 2.5rem; border-radius: 14px; box-shadow: 0 4px 20px var(--shadow-color); }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem 2rem; }
    .input-group { display: flex; flex-direction: column; }
    .full-width { grid-column: 1 / -1; }

    label { font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color-light); }
    input[type="text"], input[type="email"], input[type="number"] { width: 100%; padding: 0.8rem; border: 1px solid #ddd; background: var(--input-bg-color); border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
    input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(54, 123, 245, 0.3); }
    
    /* --- Toggle Switch --- */
    .toggle-switch { position: relative; display: inline-block; width: 60px; height: 34px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked + .slider { background-color: var(--success-color); }
    input:checked + .slider:before { transform: translateX(26px); }

    .btn { text-decoration: none; padding: 0.8rem 1.5rem; border: none; border-radius: 8px; font-weight: 500; font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; }
    .btn i { margin-right: 8px; }
    .btn-primary { background-color: var(--primary-color); color: #fff; }
    .btn-primary:hover { background-color: #2a62c5; }
    .btn-secondary { background-color: rgba(0,0,0,0.05); color: var(--text-color-light); border: 1px solid #ccc; }
    .btn-secondary:hover { background-color: rgba(0,0,0,0.1); }
    #form-actions { margin-top: 2rem; text-align: right; }

    @media (max-width: 992px) { .dashboard-container { grid-template-columns: 1fr; } /* ... sidebar ... */ }
    @media (max-width: 768px) { .main-content { padding: 2rem 1.5rem; } .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <nav class="sidebar">
      <div>
        <div class="sidebar-header"><i class="fa-solid fa-user-shield"></i> SRMS Admin</div>
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
        <h2>Edit Student</h2>
        <a href="manage_students.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
      </header>
      
      <?php if ($msg && !$student): // Show error if student not found ?>
        <div class="alert error"><?php echo htmlspecialchars($msg); ?></div>
      <?php elseif ($msg && $student): // Show success message ?>
        <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <div class="content-card">
        <?php if ($student): ?>
        <form method="post">
          <div class="form-grid">
            <div class="input-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
            </div>
            <div class="input-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            <div class="input-group">
              <label for="enrollment_no">Enrollment No</label>
              <input type="text" id="enrollment_no" name="enrollment_no" value="<?php echo htmlspecialchars($student['enrollment_no']); ?>" required>
            </div>
            <div class="input-group">
              <label for="branch">Branch</label>
              <input type="text" id="branch" name="branch" value="<?php echo htmlspecialchars($student['branch']); ?>" required>
            </div>
            <div class="input-group">
              <label for="semester">Current Semester</label>
              <input type="number" id="semester" name="semester" min="1" max="12" value="<?php echo intval($student['semester']); ?>" required>
            </div>
            <div class="input-group">
                <label>Account Status</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="verified" value="1" <?php echo ($student['verified'] ? 'checked' : ''); ?>>
                        <span class="slider"></span>
                    </label>
                    <span>Verified</span>
                </div>
            </div>
          </div>
          <div id="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update Student</button>
          </div>
        </form>
        <?php else: ?>
            <p style="text-align: center;">The requested student could not be found. Please return to the student list.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>