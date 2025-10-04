<?php
session_start();
include 'db_connect.php';
include 'Calendar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: pages/login.html");
  exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
$full_name = $user['first_name'] . ' ' . $user['last_name'];

$day = date('l');
$routine_sql = "SELECT routine_name, start_time, end_time FROM routines WHERE user_id = $user_id AND day_of_week = '$day'";
$routine_result = $conn->query($routine_sql);

$calendar = new Calendar();
$routine_events = "SELECT routine_name, day_of_week FROM routines WHERE user_id = $user_id";
$routine_events_result = $conn->query($routine_events);
while ($row = $routine_events_result->fetch_assoc()) {
  $weekday = $row['day_of_week'];
  $date = date('Y-m-d', strtotime("next $weekday"));
  $calendar->add_event($row['routine_name'], $date, 1, 'green');
}

$log_sql = "SELECT activities.title, task_logs.status, task_logs.completed_at 
            FROM task_logs 
            JOIN activities ON task_logs.activity_id = activities.activity_id 
            WHERE task_logs.user_id = $user_id 
            ORDER BY task_logs.completed_at DESC 
            LIMIT 5";
$log_result = $conn->query($log_sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Solitask Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="calendar.css" rel="stylesheet" type="text/css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f8fafc;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 700px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    h2 {
      color: #1e3a8a;
    }

    .routine, .log {
      background: #e0f2fe;
      padding: 10px;
      margin: 10px 0;
      border-radius: 8px;
    }

    .logout {
      display: inline-block;
      margin-top: 20px;
      background-color: #ef4444;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
    }

    .logout:hover {
      background-color: #dc2626;
    }

    .calendar-box {
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Welcome, <?php echo $full_name; ?> 👋</h2>
    <p>Today is <?php echo date('l, F j, Y'); ?>.</p>

    <h3>Your Routines for Today:</h3>
    <?php
    if ($routine_result->num_rows > 0) {
      while ($row = $routine_result->fetch_assoc()) {
        echo "<div class='routine'><strong>" . $row['routine_name'] . "</strong><br>" .
             "⏰ " . date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time'])) .
             "</div>";
      }
    } else {
      echo "<p>No routines scheduled for today.</p>";
    }
    ?>

    <h3>📋 Recent Task Logs:</h3>
    <?php
    if ($log_result->num_rows > 0) {
      while ($log = $log_result->fetch_assoc()) {
        echo "<div class='log'>
                <strong>" . $log['title'] . "</strong><br>
                Status: " . $log['status'] . "<br>
                Completed: " . date("F j, Y g:i A", strtotime($log['completed_at'])) . "
              </div>";
      }
    } else {
      echo "<p>No recent task logs found.</p>";
    }
    ?>

    <h3>📄 Upload Your Class Schedule (PDF)</h3>
<form method="POST" enctype="multipart/form-data" action="upload_schedule.php">
  <input type="file" name="schedule_pdf" accept=".pdf" required><br><br>
  <button type="submit">Upload PDF</button>
</form>


<div class="calendar-box">
  <h3>📅 Manage Your Class Schedule</h3>

  <!-- PDF Upload -->
  <form method="POST" enctype="multipart/form-data" action="upload_schedule.php" style="margin-bottom:10px;">
    <label>Upload PDF Schedule:</label><br>
    <input type="file" name="schedule_pdf" accept=".pdf" required>
    <button type="submit">Upload</button>
  </form>

  <!-- Manual Input -->
  <form method="POST" action="manual_schedule.php">
    <label>Manual Class Entry:</label><br>
    <input type="text" name="class_name" placeholder="Class Name" required><br>
    <select name="day_of_week">
      <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
      <option>Thursday</option><option>Friday</option><option>Saturday</option><option>Sunday</option>
    </select><br>
    <input type="time" name="start_time" required>
    <input type="time" name="end_time" required>
    <button type="submit">Add Class</button>
  </form>

  <!-- Calendar Output -->
  <?php echo $calendar; ?>
</div>


    <a class="logout" href="logout.php">Logout</a>
  </div>
</body>
</html>
