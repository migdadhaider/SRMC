<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
include('../config/db.php');

$msg = "";
// Basic single-semester result upload (creates header then result_items)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    $semester = intval($_POST['semester']);    
    $result_class = $_POST['result_class'] ?? 'Internal';

    // create header
    $stmt = $conn->prepare("INSERT INTO result_headers (student_id, semester, result_class) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $student_id, $semester, $result_class);
    if ($stmt->execute()) {
        $header_id = $stmt->insert_id;
        $stmt->close();

        // Expecting multiple subjects from form arrays
        $codes = $_POST['course_code'] ?? [];
        $names = $_POST['subject_name'] ?? [];
        $theory = $_POST['theory_marks'] ?? [];
        $practical = $_POST['practical_marks'] ?? [];

        $ins = $conn->prepare("INSERT INTO result_items (header_id, course_code, subject_name, theory_marks, practical_marks) VALUES (?, ?, ?, ?, ?)");
        foreach ($codes as $i => $c) {
            $c = trim($c);
            $n = trim($names[$i] ?? '');
            $t = intval($theory[$i] ?? 0);
            $p = intval($practical[$i] ?? 0);
            if ($c && $n) {
                $ins->bind_param("issii", $header_id, $c, $n, $t, $p);
                $ins->execute();
            }
        }
        $ins->close();

        $msg = "Result uploaded successfully. SPI/PPI/CGPA can be calculated via the result view.";
    } else {
        $msg = "Error creating result header: " . $stmt->error;
    }
}

// fetch students for dropdown
$students = $conn->query("SELECT id, name, enrollment_no FROM students WHERE verified = 1 ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Result</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome CDN for icons -->
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
    .main-header { margin-bottom: 2rem; }
    .main-header h2 { font-size: 2rem; font-weight: 600; }
    
    .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: 10px; color: #fff; background-color: var(--success-color); font-weight: 500; }

    .content-card { background: var(--card-background); padding: 2.5rem; border-radius: 14px; box-shadow: 0 4px 20px var(--shadow-color); }
    
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .input-group { display: flex; flex-direction: column; }

    label { font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color-light); }
    input, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; background: var(--input-bg-color); border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
    input:focus, select:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(54, 123, 245, 0.3); }

    hr { border: 0; height: 1px; background-color: #ddd; margin: 2rem 0; }
    
    #subjects-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    #subjects-header h3 { font-size: 1.5rem; font-weight: 600; }

    .subject-entry { background: rgba(255,255,255,0.4); border: 1px solid #eee; border-radius: 10px; padding: 1.5rem; margin-bottom: 1rem; }
    .subject-grid { display: grid; grid-template-columns: 1fr 2fr 100px 100px auto; gap: 1rem; align-items: flex-end; }
    
    .btn { text-decoration: none; padding: 0.8rem 1.5rem; border: none; border-radius: 8px; font-weight: 500; font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; }
    .btn i { margin-right: 8px; }
    
    .btn-primary { background-color: var(--primary-color); color: #fff; }
    .btn-primary:hover { background-color: #2a62c5; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    .btn-secondary { background-color: transparent; color: var(--primary-color); border: 1px solid var(--primary-color); }
    .btn-secondary:hover { background-color: rgba(54, 123, 245, 0.1); }

    .btn-danger { background-color: var(--danger-color); color: #fff; padding: 0.6rem; }
    .btn-danger i { margin: 0; }

    #form-actions { margin-top: 2rem; text-align: right; }

    @media (max-width: 1200px) {
        .subject-grid { grid-template-columns: 1fr 2fr; }
        .subject-grid .input-group { grid-column: span 1; }
        .subject-grid .input-group.marks { grid-column: span 1; }
        .subject-grid .remove-btn-wrapper { grid-column: 1 / -1; justify-self: end; }
    }
    @media (max-width: 992px) { .dashboard-container { grid-template-columns: 1fr; } /* ... sidebar styles ... */ }
    @media (max-width: 768px) { .main-content { padding: 2rem 1.5rem; } .content-card { padding: 1.5rem; } .subject-grid { grid-template-columns: 1fr; } }

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
          <a href="verify.php"><i class="fa-solid fa-user-check"></i> <span>Verify Students</span></a>
          <a href="upload_result.php" class="active"><i class="fa-solid fa-file-arrow-up"></i> <span>Upload Result</span></a>
          <a href="manage_results.php"><i class="fa-solid fa-list-check"></i> <span>Manage Results</span></a>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <header class="main-header">
        <h2>Upload New Result</h2>
      </header>
      
      <?php if ($msg): ?>
        <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <div class="content-card">
        <form method="POST">
          <div class="form-grid">
            <div class="input-group">
              <label for="student_id">Student</label>
              <select name="student_id" id="student_id" required>
                <option value="">-- Select Student --</option>
                <?php while ($s = $students->fetch_assoc()): ?>
                  <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name'] . " (" . $s['enrollment_no'] . ")"); ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="input-group">
              <label for="semester">Semester</label>
              <input type="number" name="semester" id="semester" min="1" max="12" placeholder="e.g., 5" required>
            </div>

            <div class="input-group">
              <label for="result_class">Result Class</label>
              <select name="result_class" id="result_class">
                <option value="Internal">Internal</option>
                <option value="Remedial">Remedial</option>
                <option value="External">External</option>
              </select>
            </div>
          </div>
          
          <hr>
          
          <div id="subjects-header">
              <h3>Subjects & Marks</h3>
              <button type="button" class="btn btn-secondary" onclick="addSubject()"><i class="fa-solid fa-plus"></i> Add Subject</button>
          </div>
          
          <div id="subjects-container">
            <div class="subject-entry">
              <div class="subject-grid">
                <div class="input-group">
                  <label>Course Code</label>
                  <input type="text" name="course_code[]" placeholder="e.g., CE301" required>
                </div>
                <div class="input-group">
                  <label>Subject Name</label>
                  <input type="text" name="subject_name[]" placeholder="e.g., Database Systems" required>
                </div>
                <div class="input-group marks">
                  <label>Theory</label>
                  <input type="number" name="theory_marks[]" min="0" max="200" placeholder="Marks">
                </div>
                <div class="input-group marks">
                  <label>Practical</label>
                  <input type="number" name="practical_marks[]" min="0" max="200" placeholder="Marks">
                </div>
                <div class="remove-btn-wrapper">
                    <button type="button" class="btn btn-danger" onclick="removeSubject(this)"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>
            </div>
          </div>

          <div id="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Result</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    function addSubject() {
      const container = document.getElementById("subjects-container");
      const firstBlock = container.querySelector(".subject-entry");
      const clone = firstBlock.cloneNode(true);
      clone.querySelectorAll("input").forEach(input => input.value = "");
      container.appendChild(clone);
    }

    function removeSubject(btn) {
      const container = document.getElementById("subjects-container");
      if (container.querySelectorAll(".subject-entry").length > 1) {
        btn.closest(".subject-entry").remove();
      } else {
        alert("At least one subject is required.");
      }
    }
  </script>
</body>
</html>