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
    :root{
      --primary:#1e3a8a;
      --muted:#6b7280;
      --card:#ffffff;
      --soft:#f1f5f9;
      --accent:#e0f2fe;
      --success:#bbf7d0;
      --danger:#fecaca;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial;
      background: #f8fafc;
      margin: 0;
      padding: 28px;
      color: #0f172a;
    }

    .container {
      max-width: 1100px;
      margin: auto;
      background: transparent;
      padding: 0;
    }

    h2 { color: var(--primary); margin:0 0 6px 0 }
    h3 { margin: 0 0 10px 0 }

    .grid {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 20px;
      align-items: start;
    }

    .card {
      background: var(--card);
      border-radius: 12px;
      padding: 16px;
      box-shadow: 0 6px 20px rgba(2,6,23,0.06);
    }

    .welcome-row{ display:flex; justify-content:space-between; align-items:center; gap:12px }
    .profile { font-size:14px; color:var(--muted) }

    .stats { display:flex; gap:12px; margin-top:12px }
    .stat { background:var(--soft); padding:10px 12px; border-radius:8px; text-align:center; min-width:88px }
    .stat .num { font-weight:700; font-size:18px; color:var(--primary) }
    .stat .label { font-size:12px; color:var(--muted) }

    .routine, .log { background:var(--accent); padding:10px; margin:10px 0; border-radius:8px }

    .calendar-box { margin-top: 12px }

    .right-column .motiv { background: linear-gradient(135deg,#fef3c7,#fff7ed); border-radius:10px; padding:12px; margin-top:12px }

    .logout { display:inline-block; margin-top:18px; background-color: #ef4444; color:white; padding:8px 14px; border-radius:8px; text-decoration:none }

    @media (max-width: 880px){
      .grid{ grid-template-columns: 1fr }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="grid">
      <div class="left">
        <div class="card">
          <div class="welcome-row">
            <div>
              <h2>Welcome, <?php echo $full_name; ?> 👋</h2>
              <div class="profile">Today is <?php echo date('l, F j, Y'); ?>.</div>
            </div>
            <div style="text-align:right">
              <a class="logout" href="logout.php">Logout</a>
            </div>
          </div>

          <?php
          // Small summary stats
          $total_routines_sql = "SELECT COUNT(*) as cnt FROM routines WHERE user_id = $user_id";
          $total_routines = $conn->query($total_routines_sql)->fetch_assoc()['cnt'] ?? 0;
          $completed_count_sql = "SELECT COUNT(*) as cnt FROM task_logs WHERE user_id = $user_id AND status = 'completed'";
          $completed_count = $conn->query($completed_count_sql)->fetch_assoc()['cnt'] ?? 0;
          ?>

          <div class="stats">
            <div class="stat"><div class="num"><?php echo $total_routines; ?></div><div class="label">Total Routines</div></div>
            <div class="stat"><div class="num"><?php echo $completed_count; ?></div><div class="label">Tasks Completed</div></div>
            <div class="stat"><div class="num"><?php echo htmlspecialchars(date('g A')); ?></div><div class="label">Current Time</div></div>
          </div>
        </div>

        <div class="card" style="margin-top:16px">
          <h3>Your Routines for Today</h3>
          <?php
          if ($routine_result->num_rows > 0) {
            while ($row = $routine_result->fetch_assoc()) {
              echo "<div class='routine'><strong>" . htmlspecialchars($row['routine_name']) . "</strong><br>" .
                   "⏰ " . date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time'])) .
                   "</div>";
            }
          } else {
            echo "<p>No routines scheduled for today.</p>";
          }
          ?>
        </div>

        <div class="card calendar-box" style="margin-top:16px">
          <h3>📅 Your Class Schedule</h3>
          <!-- Calendar Output -->
          <?php echo $calendar; ?>
        </div>
      </div>

      <div class="right right-column">
        <div class="card">
          <h3>📋 Recent Task Logs</h3>
          <?php
          if ($log_result->num_rows > 0) {
            while ($log = $log_result->fetch_assoc()) {
              echo "<div class='log'>
                      <strong>" . htmlspecialchars($log['title']) . "</strong><br>
                      Status: " . htmlspecialchars($log['status']) . "<br>
                      Completed: " . date("F j, Y g:i A", strtotime($log['completed_at'])) . "
                    </div>";
            }
          } else {
            echo "<p>No recent task logs found.</p>";
          }
          ?>
        </div>

        <div class="card motiv" style="margin-top:14px">
          <h3>✨ Daily Motivation</h3>
          <p style="margin:6px 0">Focus on one task at a time — small consistent wins add up. Try to complete your next routine and reward yourself with a short break.</p>
          <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
            <div style="flex:1">
              <strong>Reward</strong>
              <div style="color:var(--muted);font-size:13px">Earn points for completed routines.</div>
            </div>
            <div style="font-size:22px">🏆</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
