<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
redirect('login.php');
?>