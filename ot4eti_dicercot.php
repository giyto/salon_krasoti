<?php
// Подключение к базе данных
$pdo = new PDO('mysql:host=localhost;dbname=salon', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Обработка формы фильтрации
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$whereClauses = ['appointments.status = "выполнена"'];
$params = [];

if ($dateFrom) {
    $whereClauses[] = 'appointments.appointment_date_time >= :date_from';
    $params[':date_from'] = $dateFrom;
}

if ($dateTo) {
    $whereClauses[] = 'appointments.appointment_date_time <= :date_to';
    $params[':date_to'] = $dateTo;
}

$whereSql = '';
if ($whereClauses) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Получение данных об оказанных услугах из базы данных
$stmt = $pdo->prepare("
    SELECT 
        services.name AS service,
        masters.full_name AS specialist,
        clients.full_name AS client,
        appointments.appointment_date_time AS date,
        appointments.price AS cost
    FROM appointments
    JOIN services ON appointments.service_id = services.service_id
    JOIN masters ON appointments.master_id = masters.master_id
    JOIN clients ON appointments.client_id = clients.client_id
    $whereSql
");
$stmt->execute($params);
$services = $stmt->fetchAll();

$totalCost = array_sum(array_column($services, 'cost'));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет об оказании услуг</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px; 
            display: flex; 
        }
        .forms-section { 
            flex: 1; 
             
            flex-direction: column;
        }
        .results-section { 
            flex: 2; 
            padding: 10px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f9f9f9; 
        }
        tr:nth-child(even) { 
            background-color: #f2f2f2; 
        }
        h1 {
            text-align: center;
        }
        input, select, button { 
            width: 100%; 
            padding: 8px; 
            margin-top: 5px; 
            box-sizing: border-box; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
        }
        button { 
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            cursor: pointer; 
            transition: background-color 0.3s; 
        }
        button:hover { 
            background-color: #45a049; 
        }
        .total-row td {
            font-weight: bold;
        }
    </style>
</head>
<header>
    <div class="container" style="flex-direction: column;">
        <h1>Beauty Lab Store</h1>
        <nav>
            <ul>
                <a href="director_page.php" class="button navigation-button"><span>Главная</span></a>
                <a href="ot4eti_dicercot.php" class="button navigation-button"><span>Общие отчеты</span></a>
                <a href="ot4eti_dicercot1.php" class="button navigation-button"><span>Объем работы мастеров</span></a>
                <a href="ot4eti_dicercot2.php" class="button navigation-button"><span>Объем оказанных услуг</span></a>
                <a href="logout.php" class="button navigation-button"><span>Выход</span></a>
            </ul>
        </nav>
    </div>
</header>
<body>
    <div class="container">
        <div class="forms-section">
            <h1>Фильтр отчета</h1>
            <form method="get" action="">
                <label for="date_from">Дата с:</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                <label for="date_to">Дата по:</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                <button type="submit"  class="button">Сформировать отчет</button>
            </form>
        </div>
        <div class="results-section">
        <h1>Отчет об оказаниии услуг за период с <?= htmlspecialchars($dateFrom) ?> по <?= htmlspecialchars($dateTo) ?></h1>
            <table>
                <thead>
                    <tr>
                        <th>Услуга</th>
                        <th>Специалист</th>
                        <th>Клиент</th>
                        <th>Дата</th>
                        <th>Стоимость</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?= htmlspecialchars($service['service']) ?></td>
                                <td><?= htmlspecialchars($service['specialist']) ?></td>
                                <td><?= htmlspecialchars($service['client']) ?></td>
                                <td><?= htmlspecialchars($service['date']) ?></td>
                                <td><?= htmlspecialchars($service['cost']) ?> руб.</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="4">Итого:</td>
                            <td><?= htmlspecialchars($totalCost) ?> руб.</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Данные отсутствуют</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
