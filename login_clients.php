<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'salon';
$username = 'root';
$password = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

session_start(); // Начало сессии для сохранения данных пользователя

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Не удалось подключиться к базе данных: " . $e->getMessage());
}

// Обработка данных формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Подготовка запроса на поиск пользователя среди администраторов и директоров
    $sql = "SELECT username, password, role FROM Admin WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $login);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Сравнение пароля (без хеширования)
        if ($password === $user['password']) {
            // Проверка роли и редирект
            if ($user['role'] == 'Администратор') {
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: administrator_page.php"); // Путь к странице администратора
                exit;
            } elseif ($user['role'] == 'Директор') {
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: director_page.php"); // Путь к странице директора
                exit;
            }
        } else {
            echo "Неверный пароль!";
            exit;
        }
    }

    // Подготовка запроса на поиск пользователя среди клиентов
    $sql = "SELECT client_id, password FROM clients WHERE login = :login";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':login', $login);
    $stmt->execute();

    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['client_id'];
            header("Location: profile.html"); // Путь к странице профиля клиента
            exit;
        } else {
            echo "Неверный пароль!";
            exit;
        }
    }

    // Если пользователь не найден ни среди администраторов/директоров, ни среди клиентов
    echo "Пользователь не найден!";
} else {
    echo "Некорректный запрос.";
}
?>