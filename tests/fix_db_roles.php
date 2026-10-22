<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();
$db->exec("UPDATE users SET role = 'user' WHERE username IN ('khach_alpha', 'khach_beta')");
$stmt = $db->query("SELECT id, username, role, name FROM users WHERE username IN ('khach_alpha', 'khach_beta')");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
