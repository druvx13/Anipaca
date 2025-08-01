<?php
session_start();
require_once('../_config.php');

if (!isset($_COOKIE['userID'])) {
    header('Location: /login');
    exit;
}

$user_id = $_COOKIE['userID'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    header('Location: /home');
    exit;
}
?>
