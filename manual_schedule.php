<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $user_id = $_SESSION['user_id'];
  $class = $_POST['class_name'];
  $day = $_POST['day_of_week'];
  $start = $_POST['start_time'];
  $end = $_POST['end_time'];

  $sql = "INSERT INTO routines (user_id, routine_name, day_of_week, start_time, end_time)
          VALUES ('$user_id', '$class', '$day', '$start', '$end')";
  if ($conn->query($sql)) {
    header("Location: dashboard.php?success=manual_added");
  } else {
    header("Location: dashboard.php?error=manual_failed");
  }
}
?>
