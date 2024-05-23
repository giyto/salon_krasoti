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

// Получение данных об оказанных услугах для каждого мастера
$stmt = $pdo->prepare("
    SELECT 
        masters.full_name AS specialist,
        COUNT(appointments.appointment_id) AS service_count,
        SUM(appointments.price) AS total_cost
    FROM appointments
    JOIN masters ON appointments.master_id = masters.master_id
    $whereSql
    GROUP BY masters.full_name
");
$stmt->execute($params);
$results = $stmt->fetchAll();

$totalServices = array_sum(array_column($results, 'service_count'));
$totalCost = array_sum(array_column($results, 'total_cost'));

// Подготовка данных для диаграммы
$labels = [];
$data = [];

foreach ($results as $result) {
    $labels[] = htmlspecialchars($result['specialist']);
    $data[] = $result['service_count'];
}

$labelsJson = json_encode($labels);
$dataJson = json_encode($data);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет об объеме работы мастеров</title>
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
            padding-right: 20px; 
        }
        .results-section { 
            flex: 2; 
            padding-left: 20px; 
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
        .chart-container {
            width: 100%;
            margin: 20px 0;
            padding: 20px 0;
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
                <button type="submit" class="button">Сформировать отчет</button>
            </form>
        </div>
        <div class="results-section">
            <h1>Отчет об объеме работы мастеров за период с <?= htmlspecialchars($dateFrom) ?> по <?= htmlspecialchars($dateTo) ?></h1>
            <table>
                <thead>
                    <tr>
                        <th>ФИО Мастера</th>
                        <th>Количество услуг</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?= htmlspecialchars($result['specialist']) ?></td>
                                <td><?= htmlspecialchars($result['service_count']) ?></td>
                                <td><?= htmlspecialchars($result['total_cost']) ?> руб.</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td>Итого:</td>
                            <td><?= htmlspecialchars($totalServices) ?></td>
                            <td><?= htmlspecialchars($totalCost) ?> руб.</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">Данные отсутствуют</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="chart-container">
                <canvas id="servicesChart"></canvas>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('servicesChart').getContext('2d');
        var servicesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $labelsJson ?>,
                datasets: [{
                    label: 'Количество услуг',
                    data: <?= $dataJson ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
