<?php
session_start(); // Darouri bach l-PHP i-3ref ana session bghiti t-sed
session_unset(); // Kiy-mseh ga3 l-variables (user_id, user_name...)
session_destroy(); // Kiy-9te3 l-session kamla

header("Location: login.php"); // Kiy-sift l-user l-page dial login
exit();
?>