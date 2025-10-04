<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($sql);

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['role'] = $user['role'];
      header("Location: dashboard.php");
      exit();
    } else {
      header("Location: pages/login.html?error=wrongpassword");
      exit();
    }
  } else {
    header("Location: pages/login.html?error=notfound");
    exit();
  }
}
?>
