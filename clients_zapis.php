<?php
session_start();
require 'vendor/autoload.php'; // Подключаем автозагрузчик Composer, если необходимо

// Подключение к базе данных
$pdo = new PDO('mysql:host=localhost;dbname=salon', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Обработка изменения статуса записи через AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];

    // Обновление статуса в базе данных
    $updateStmt = $pdo->prepare("UPDATE appointments SET status = :status WHERE appointment_id = :appointment_id");
    $updateStmt->execute(['status' => $status, 'appointment_id' => $appointment_id]);

    // Возвращаем JSON-ответ
    echo json_encode(['success' => true]);
    exit;
}

// Получение данных о записях на прием
$stmt = $pdo->prepare("
    SELECT 
        appointments.appointment_id,
        clients.full_name AS client,
        services.name AS service,
        masters.full_name AS master,
        appointments.appointment_date_time,
        appointments.status
    FROM 
        appointments
    JOIN 
        clients ON appointments.client_id = clients.client_id
    JOIN 
        services ON appointments.service_id = services.service_id
    JOIN 
        masters ON appointments.master_id = masters.master_id
");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список записей на прием</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; display: flex; }
        .search-section, .results-section { flex: 1; padding: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        input, select, button { width: 90%; padding: 8px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #45a049; }
        h1 { text-align: center; }
        .active-filter { background-color: #45a049; }
    </style>
</head>
<header>
    <div class="container" style="flex-direction: column;">
        <h1>Beauty Lab Store</h1>
        <nav>
            <ul>
                <a href="administrator_page.php" class="button navigation-button"><span>Главная</span></a>
                <a href="clients_zapis.php" class="button navigation-button"><span>Клиенты запись</span></a>
                <a href="get_services.php" class="button navigation-button"><span>Услуги</span></a>
                <a href="mastera_admin.php" class="button navigation-button"><span>Добавить мастера</span></a>
                <a href="mastera_yslugi_admin.php" class="button navigation-button"><span>Добавить услугу мастеру</span></a>
                <a href="ot4eti_admin.php" class="button navigation-button"><span>Отчеты</span></a>
                <a href="logout.php" class="button navigation-button"><span>Выход</span></a>                
            </ul>
        </nav>
    </div>
</header>
<body>
    <div class="container">
        <div class="search-section">
            <h1>Фильтры</h1>
            <button id="allBtn" class="button active-filter" data-filter="all">Все</button>
            <button id="activeBtn" class="button" data-filter="активна">Активные</button>
            <button id="cancelledBtn" class="button" data-filter="отменена">Отмененные</button>
            <button id="completedBtn" class="button" data-filter="выполнена">Выполненные</button>
        </div>
        <div class="results-section">
            <h1>Список записей на прием</h1>
            <table>
                <thead>
                    <tr>
                        <th>Клиент</th>
                        <th>Услуга</th>
                        <th>Мастер</th>
                        <th>Дата и время</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr data-status="<?= htmlspecialchars($appointment['status']) ?>" data-appointment-id="<?= htmlspecialchars($appointment['appointment_id']) ?>">
                            <td><?= htmlspecialchars($appointment['client']) ?></td>
                            <td><?= htmlspecialchars($appointment['service']) ?></td>
                            <td><?= htmlspecialchars($appointment['master']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date_time']) ?></td>
                            <td>
                                <select name="status" class="status-select" data-appointment-id="<?= htmlspecialchars($appointment['appointment_id']) ?>">
                                    <option value="активна" <?= $appointment['status'] === 'активна' ? 'selected' : '' ?>>активна</option>
                                    <option value="отменена" <?= $appointment['status'] === 'отменена' ? 'selected' : '' ?>>отменена</option>
                                    <option value="выполнена" <?= $appointment['status'] === 'выполнена' ? 'selected' : '' ?>>выполнена</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // Обработчик изменения статуса
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const appointmentId = this.dataset.appointmentId;
                const newStatus = this.value;

                fetch(location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=update_status&appointment_id=${appointmentId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Статус успешно обновлен');
                        location.reload(); // Перезагрузка страницы
                    } else {
                        alert('Ошибка при обновлении статуса');
                    }
                })
                .catch(error => console.error('Ошибка:', error));
            });
        });

        // Функция для фильтрации записей по статусу
        function filterStatus(status) {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            document.querySelectorAll('.button').forEach(btn => btn.classList.remove('active-filter'));
            document.querySelector(`[data-filter="${status}"]`).classList.add('active-filter');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('allBtn').addEventListener('click', () => filterStatus('all'));
            document.getElementById('activeBtn').addEventListener('click', () => filterStatus('активна'));
            document.getElementById('cancelledBtn').addEventListener('click', () => filterStatus('отменена'));
            document.getElementById('completedBtn').addEventListener('click', () => filterStatus('выполнена'));
        });
    </script>
</body>
</html>
