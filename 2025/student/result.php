<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}
include('../config/db.php');

$student_id = $_SESSION['student_id'];

// --- Optimized Data Fetching ---

// 1. Fetch all result headers for the student
$headers = [];
$h_res = $conn->query("SELECT id, semester, spi, ppi, cgpa, result_class, published_at FROM result_headers WHERE student_id = $student_id ORDER BY semester ASC");
if ($h_res) {
    $headers = $h_res->fetch_all(MYSQLI_ASSOC);
}

// 2. If headers exist, fetch all associated items in a single query
$items_by_header = [];
if (!empty($headers)) {
    $header_ids = array_column($headers, 'id');
    $ids_placeholder = implode(',', array_fill(0, count($header_ids), '?'));
    $types = str_repeat('i', count($header_ids));

    $items_stmt = $conn->prepare("SELECT header_id, course_code, subject_name, theory_marks, practical_marks FROM result_items WHERE header_id IN ($ids_placeholder)");
    $items_stmt->bind_param($types, ...$header_ids);
    $items_stmt->execute();
    $items_res = $items_stmt->get_result();

    // 3. Group items by their header_id for easy lookup
    while ($item = $items_res->fetch_assoc()) {
        $items_by_header[$item['header_id']][] = $item;
    }
    $items_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Results</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #367BF5;
      --card-background: rgba(255, 255, 255, 0.7);
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
      padding: 2rem;
    }
    
    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    .page-header {
        background: var(--card-background);
        backdrop-filter: blur(15px);
        border: 1px solid var(--border-color);
        padding: 1.5rem 2rem;
        border-radius: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px var(--shadow-color);
    }
    .page-header h2 { font-size: 1.5rem; font-weight: 600; }
    .page-header a { text-decoration: none; color: #fff; background-color: var(--primary-color); padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 500; transition: background-color 0.3s; }
    .page-header a:hover { background-color: #2a62c5; }

    .no-results {
        background: var(--card-background);
        padding: 3rem;
        border-radius: 14px;
        text-align: center;
        font-size: 1.1rem;
        color: var(--text-color-light);
    }
    
    /* Accordion Styles */
    .accordion-item {
        background: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        margin-bottom: 1rem;
        box-shadow: 0 4px 20px var(--shadow-color);
        overflow: hidden;
    }

    .accordion-header {
        background: transparent;
        border: none;
        width: 100%;
        text-align: left;
        padding: 1.5rem 2rem;
        font-family: 'Poppins', sans-serif;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s;
    }
    .accordion-header:hover { background-color: rgba(255,255,255,0.4); }
    .accordion-header .icon { transition: transform 0.3s ease; }
    .accordion-header.active .icon { transform: rotate(180deg); }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out;
        border-top: 1px solid var(--border-color);
    }
    .content-padding { padding: 1.5rem 2rem; }
    
    .score-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .score-card { background: rgba(255,255,255,0.4); text-align: center; padding: 1rem; border-radius: 10px; }
    .score-card h4 { font-size: 1.5rem; font-weight: 600; color: var(--primary-color); }
    .score-card p { font-size: 0.9rem; font-weight: 500; color: var(--text-color-light); }
    
    table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    thead tr { border-bottom: 2px solid var(--table-border-color); }
    th, td { padding: 0.8rem; text-align: left; }
    th { font-weight: 600; color: var(--text-color-light); }
    tbody tr { border-bottom: 1px solid var(--table-border-color); }
    tbody tr:last-child { border-bottom: none; }
    td.marks { text-align: center; font-weight: 500; }
  </style>
</head>
<body>
  <div class="container">
    <header class="page-header">
      <h2>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h2>
      <a href="logout.php">Logout</a>
    </header>

    <?php if (empty($headers)): ?>
        <div class="no-results">
            <p>No results have been published for you yet. Please check back later.</p>
        </div>
    <?php else: ?>
      <div class="results-accordion">
        <?php foreach ($headers as $h): ?>
          <div class="accordion-item">
            <button class="accordion-header">
              <span>Semester <?php echo intval($h['semester']); ?> <small>(<?php echo htmlspecialchars($h['result_class']); ?>)</small></span>
              <i class="fas fa-chevron-down icon"></i>
            </button>
            <div class="accordion-content">
              <div class="content-padding">
                <div class="score-grid">
                  <div class="score-card"><h4><?php echo htmlspecialchars($h['spi'] ?? '-'); ?></h4><p>SPI</p></div>
                  <div class="score-card"><h4><?php echo htmlspecialchars($h['ppi'] ?? '-'); ?></h4><p>PPI</p></div>
                  <div class="score-card"><h4><?php echo htmlspecialchars($h['cgpa'] ?? '-'); ?></h4><p>CGPA</p></div>
                </div>
                <table>
                  <thead>
                    <tr><th>Course Code</th><th>Subject Name</th><th style="text-align: center;">Theory</th><th style="text-align: center;">Practical</th></tr>
                  </thead>
                  <tbody>
                    <?php 
                    // Use the pre-fetched items for this header
                    $current_items = $items_by_header[$h['id']] ?? [];
                    foreach ($current_items as $item): 
                    ?>
                      <tr>
                        <td><?php echo htmlspecialchars($item['course_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['subject_name']); ?></td>
                        <td class="marks"><?php echo htmlspecialchars($item['theory_marks'] ?? '-'); ?></td>
                        <td class="marks"><?php echo htmlspecialchars($item['practical_marks'] ?? '-'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>

  <script>
    const accordionHeaders = document.querySelectorAll(".accordion-header");

    accordionHeaders.forEach(header => {
      header.addEventListener("click", () => {
        // Toggle active class on the button
        header.classList.toggle("active");
        
        // Get the content panel
        const accordionContent = header.nextElementSibling;

        // Check if the panel is open
        if (accordionContent.style.maxHeight) {
          // It's open, so close it
          accordionContent.style.maxHeight = null;
        } else {
          // It's closed, so open it
          accordionContent.style.maxHeight = accordionContent.scrollHeight + "px";
        }
      });
    });
  </script>
</body>
</html>