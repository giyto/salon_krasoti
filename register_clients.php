<?php
// Подключение к базе данных
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

// Обработка POST запроса
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = isset($_POST['login']) ? trim($_POST['login']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : null;


    if (!$login) {
        echo "Логин не может быть пустым.";
        exit;
    }
    if ($password !== $confirm_password) {
        echo "Пароли не совпадают.";
        exit;
    }
    // Проверка, является ли пользователь администратором, директором или пользователем
    $admin_usernames = array("administrator", "administrator1", "administrator2", "administrator3", "administrator4", "administrator5");
    $director_usernames = array("director", "director1", "director2", "director3", "director4", "director5");
    
    if (in_array($login, $admin_usernames)) {
        // Вставка данных в таблицу администраторов
        $role = 'Администратор';
    } elseif (in_array($login, $director_usernames)) {
        // Вставка данных в таблицу директоров
        $role = 'Директор';
    } else {
        // Вставка данных в таблицу клиентов
        $role = 'Пользователь';
    }

    if ($role === 'Администратор' || $role === 'Директор') {
        $sql = "INSERT INTO Admin (username, email, password, role) VALUES (:username, :email, :password, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $login);
        $stmt->bindParam(':email', $login); // Предположим, что email и username одинаковы
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
    } else {
        // Проверка уникальности логина
        $sql = "SELECT login FROM clients WHERE login = :login";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Логин уже используется. Пожалуйста, выберите другой логин.";
            exit;
        }

        // Вставка данных клиента
        $sql = "INSERT INTO clients (login, password) VALUES (:login, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $password);
    }

    // Выполнение запроса на добавление пользователя
    try {
        $stmt->execute();
        echo "<script>alert('Вы успешно зарегистрировались!'); window.location='login_appointment.html';</script>";
    } catch (PDOException $e) {
        echo "Ошибка при регистрации: " . $e->getMessage();
    }
} else {
    echo "Данные формы не были отправлены.";
}
?>