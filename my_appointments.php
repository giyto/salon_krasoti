<?php
session_start();


$host = 'localhost';
$dbname = 'salon';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Запрос на выборку заявок текущего пользователя
    $stmt = $pdo->prepare("SELECT c.full_name, c.email, c.phone, a.status, a.price, a.appointment_date_time, s.name AS service_name, t.name_type AS type_name, m.full_name AS master_name
    FROM appointments a
    JOIN clients c ON a.client_id = c.client_id
    JOIN services s ON a.service_id = s.service_id
    JOIN type t ON s.id_type = t.id_type
    JOIN masters m ON a.master_id = m.master_id
    WHERE a.client_id = :client_id
    ORDER BY a.appointment_date_time DESC");

$stmt->execute(['client_id' => $_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки - Салон Красоты</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<style>
    body {
        font-family: Arial, sans-serif; /* Установка шрифта для всей страницы */
    }
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse; /* Убираем двойные границы между ячейками */
   
    }
    th, td {
        border: 1px solid #ccc; /* Цвет границы таблицы */
        padding: 8px; /* Отступы внутри ячеек */
        text-align: left; /* Выравнивание текста в ячейках */
    }
    th {
        background-color: #f9f9f9; /* Цвет фона заголовков таблицы */
    }
    tr:nth-child(even) {
        background-color: #f2f2f2; /* Цвет фона для четных строк таблицы */
    }
    
</style>
    <header>
        <div class="container">
            <h1>Beauty Lab Store</h1>
            <nav>
                <ul>
                <a href="index1.html" class="button navigation-button"><span>Главная</span></a>
<a href="services1.php" class="button navigation-button"><span>Услуги</span></a>
<a href="appointment.php" class="button navigation-button"><span>Запись на прием</span></a>
<a href="profile.html" class="button navigation-button"><span>Профиль</span></a>
<a href="my_appointments.php" class="button navigation-button"><span>Мои заявки</span></a>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <h2>Мои заявки</h2>
            <table>
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Статус заявки</th>
                        <th>Цена услуги</th>
                        <th>Услуга</th>
                        <th>Тип услуги</th>
                        <th>Специалист</th>
                        <th>Дата и время</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['full_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['email']) ?></td>
                        <td><?= htmlspecialchars($appointment['phone']) ?></td>
                        <td><?= htmlspecialchars($appointment['status']) ?></td>
                        <td><?= htmlspecialchars($appointment['price']) ?> руб.</td>
                        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['type_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['master_name']) ?></td>
                        <td><?= (new DateTime($appointment['appointment_date_time']))->format('H:i, d.m.Y') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        function updateTypeOptions() {
            fetch('get_types.php')
                .then(response => response.json())
                .then(data => {
                    const selectElement = document.getElementById('delete_type_id');
                    selectElement.innerHTML = '';
                    data.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id_type;
                        option.textContent = type.name_type;
                        selectElement.appendChild(option);
                    });
                });
        }

        // Вызываем функцию обновления списка типов услуг после загрузки страницы
        updateTypeOptions();
    </script>
</body>
</html>