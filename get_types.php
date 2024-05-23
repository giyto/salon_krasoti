<?php
$pdo = new PDO('mysql:host=localhost;dbname=salon', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$stmt = $pdo->query("SELECT id_type, name_type FROM type");
$types = $stmt->fetchAll();

echo json_encode($types);
?>