<?php
session_start();
include 'db_connect.php';
require __DIR__ . '/vendor/autoload.php'; // Make sure Composer is installed

use Smalot\PdfParser\Parser;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['schedule_pdf'])) {
  $user_id = $_SESSION['user_id'];
  $file_name = basename($_FILES['schedule_pdf']['name']);
  $file_tmp = $_FILES['schedule_pdf']['tmp_name'];
  $target_dir = "uploads/";
  $target_file = $target_dir . $file_name;

  // Create uploads folder if it doesn't exist
  if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
  }

  // Move uploaded file
  if (move_uploaded_file($file_tmp, $target_file)) {
    // Optional: store upload record
    $conn->query("INSERT INTO uploaded_schedules (user_id, filename) VALUES ('$user_id', '$file_name')");

    // Parse PDF
    $parser = new Parser();
    $pdf = $parser->parseFile($target_file);
    $text = $pdf->getText();

    // Split into lines
    $lines = explode("\n", $text);
    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    echo "<h3>📄 Parsed Schedule:</h3><pre>";

    foreach ($lines as $line) {
      // Match time slot row
      if (preg_match('/^(\d{1,2}:\d{2}[APMapm]{2})-(\d{1,2}:\d{2}[APMapm]{2})\s+(.*)$/', $line, $matches)) {
        $start = date('H:i:s', strtotime($matches[1]));
        $end = date('H:i:s', strtotime($matches[2]));
        $cells = preg_split('/\s{2,}/', $matches[3]); // split by multiple spaces

        foreach ($cells as $i => $cell) {
          if (!empty(trim($cell)) && isset($days[$i])) {
            $class = $conn->real_escape_string(trim($cell));
            $day = $days[$i];

            $insert = "INSERT INTO routines (user_id, routine_name, day_of_week, start_time, end_time)
                       VALUES ('$user_id', '$class', '$day', '$start', '$end')";
            if ($conn->query($insert)) {
              echo "✅ Inserted: $class on $day from $start to $end\n";
            } else {
              echo "❌ Failed to insert: $class on $day\n";
            }
          }
        }
      }
    }

    echo "</pre>";
    echo "<a href='dashboard.php'>← Back to Dashboard</a>";
  } else {
    echo "<p style='color:red;'>❌ Upload failed. Please try again.</p>";
  }
}
?>
