<?php
require_once 'includes/session.php';

// Destroy session and redirect to login
session_destroy();
header('Location: login.php');
exit();
?>
