<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=u986241231_autobox;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== USERS ===\n";
$users = $pdo->query("SELECT id, name, role, email, qr_token, is_active FROM users")->fetchAll();
print_r($users);

echo "\n=== KEYS ===\n";
$keys = $pdo->query("SELECT id, key_name, room_name, slot_number, status, is_active FROM `keys`")->fetchAll();
print_r($keys);

echo "\n=== ACCESS LOGS (COUNT: " . $pdo->query("SELECT COUNT(*) FROM access_logs")->fetchColumn() . ") ===\n";
$logs = $pdo->query("SELECT * FROM access_logs ORDER BY id DESC LIMIT 10")->fetchAll();
print_r($logs);

echo "\n=== TRANSACTIONS (COUNT: " . $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn() . ") ===\n";
$txs = $pdo->query("SELECT * FROM transactions ORDER BY id DESC LIMIT 10")->fetchAll();
print_r($txs);
