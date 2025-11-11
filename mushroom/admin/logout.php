<?php
require_once __DIR__ . '/../config/config.php';

// Destroy admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

// Redirect to admin login
redirect(SITE_URL . '/admin/login.php');
?>
