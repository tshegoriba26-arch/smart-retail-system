<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$result = $auth->logout();

// Redirect to homepage
header('Location: index.php');
exit;
?>