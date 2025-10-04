<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];

  $check = "SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($check);

  if ($result->num_rows > 0) {
    header("Location: pages/register.html?error=exists");
    exit();
  }

  $sql = "INSERT INTO users (email, password, first_name, last_name)
          VALUES ('$email', '$password', '$first_name', '$last_name')";

  if ($conn->query($sql) === TRUE) {
    header("Location: pages/login.html?success=registered");
    exit();
  } else {
    header("Location: pages/register.html?error=failed");
    exit();
  }
}
?>
