<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'salon';
$username = 'root';
$password = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Обработка AJAX запроса
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service_id'])) {
    $serviceId = $_POST['service_id'];

    $stmt = $pdo->prepare("SELECT m.master_id, m.full_name FROM masters m JOIN services s ON m.specialization = s.name WHERE s.service_id = ?");
    $stmt->execute([$serviceId]);
    $masters = $stmt->fetchAll();

    echo json_encode($masters);
    exit;
}
?>