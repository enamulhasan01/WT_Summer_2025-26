<?php
session_start();
session_unset();
session_destroy();

// Redirect back to the main login page in the root directory
header("Location: ../login.php");
exit();
?>