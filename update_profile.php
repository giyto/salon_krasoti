<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_appointment.html');
    exit;
}

$host = 'localhost';
$dbname = 'salon';
$username = 'root';
$password = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Не удалось подключиться к базе данных: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'] ?? '';
    $birth_date = $_POST['birth_date'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';

    // Проверка на уникальность телефона и email
    $checkSql = "SELECT client_id FROM clients WHERE (phone = :phone OR email = :email) AND client_id != :client_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':phone', $phone);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->bindParam(':client_id', $_SESSION['user_id']);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        echo "Телефон или Email уже используется другим пользователем.";
        exit;
    }

    $sql = "UPDATE clients SET full_name = :full_name, birth_date = :birth_date, gender = :gender, phone = :phone, email = :email WHERE client_id = :client_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':birth_date', $birth_date);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':client_id', $_SESSION['user_id']);

    try {
        $stmt->execute();
        header('Location: profile.html?update=success'); // Перенаправление с параметром
        exit;
    } catch (PDOException $e) {
        echo "Ошибка обновления данных: " . $e->getMessage();
    }
}
?>