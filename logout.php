<?php
session_start();
session_unset(); // clears all session variables
session_destroy(); // ends the session
header("Location: pages/login.html"); // redirect to login
exit();
?>
