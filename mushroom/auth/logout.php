<?php
require_once __DIR__ . '/../config/config.php';

// Destroy session
session_destroy();

// Redirect to home page
redirect(SITE_URL);
?>
