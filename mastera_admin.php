<?php
session_start();

$host = 'localhost';
$dbname = 'salon';
$user = 'root';
$password = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Обработка запроса на добавление нового мастера
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_master'])) {
    $insertStmt = $pdo->prepare("INSERT INTO masters (full_name, specialization, gender, phone, birth_date, hire_date, termination_date) VALUES (:full_name, :specialization, :gender, :phone, :birth_date, :hire_date, :termination_date)");
    $result = $insertStmt->execute([
        'full_name' => $_POST['new_full_name'],
        'specialization' => $_POST['new_specialization'],
        'gender' => $_POST['new_gender'],
        'phone' => $_POST['new_phone'],
        'birth_date' => $_POST['new_birth_date'],
        'hire_date' => $_POST['new_hire_date'],
        'termination_date' => isset($_POST['new_termination_date']) ? $_POST['new_termination_date'] : null
    ]);

    if ($result) {
        $_SESSION['message'] = 'Мастер успешно добавлен!';
    } else {
        $_SESSION['message'] = 'Ошибка при добавлении мастера.';
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Перезагрузка страницы для предотвращения повторной отправки формы
    exit;
}

// Обработка запроса на обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_master'])) {
    $updateStmt = $pdo->prepare("UPDATE masters SET full_name = :full_name, specialization = :specialization, gender = :gender, phone = :phone, birth_date = :birth_date, hire_date = :hire_date, termination_date = :termination_date WHERE master_id = :master_id");
    $updateStmt->execute([
        'master_id' => $_POST['master_id'],
        'full_name' => $_POST['full_name'],
        'specialization' => $_POST['specialization'],
        'gender' => $_POST['gender'],
        'phone' => $_POST['phone'],
        'birth_date' => $_POST['birth_date'],
        'hire_date' => $_POST['hire_date'],
        'termination_date' => $_POST['termination_date'] ?: null
    ]);
    header("Location: " . $_SERVER['PHP_SELF']); // Перезагрузка страницы для предотвращения повторной отправки формы
    exit;
}

// Загрузка данных мастеров для отображения
$query = "SELECT master_id, full_name, specialization, birth_date, gender, phone, hire_date, termination_date FROM masters WHERE 1 = 1";
$params = [];

if (!empty($_GET['full_name'])) {
    $query .= " AND full_name LIKE :full_name";
    $params['full_name'] = '%' . $_GET['full_name'] . '%';
}
if (!empty($_GET['specialization'])) {
    $query .= " AND specialization LIKE :specialization";
    $params['specialization'] = '%' . $_GET['specialization'] . '%';
}
if (!empty($_GET['gender'])) {
    $query .= " AND gender = :gender";
    $params['gender'] = $_GET['gender'];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$masters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список мастеров</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
       body { 
        font-family: Arial, sans-serif; 
    }
    .container { 
        max-width: 1600px; 
        margin: 0 auto; 
        padding: 20px; 
        display: flex; 
        flex-direction: column; 
    }
    .flex-container { 
        display: flex; 
        justify-content: space-between; /* Убедитесь, что элементы внутри распределены с балансом */
    }
    .form-container { 
        flex: 0 1 30%; /* Ограничиваем ширину контейнера с формой, чтобы он не занимал слишком много места */
        margin: 5px; 
    }
    .table-container {
        flex: 0 1 70%; /* Увеличиваем доступное пространство для таблицы */
        margin: 5px;
        padding-right: 0; /* Уменьшаем правый отступ, если он вам мешает */
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
    input, select, button { 
        width: 100%; 
        padding: 8px; 
        margin-top: 5px; 
        box-sizing: border-box; 
        border: 1px solid #ccc; 
        border-radius: 4px; 
    }
    button { 
        cursor: pointer; 
    }
    label { 
        font-weight: bold; 
        margin-bottom: 5px; 
        display: block; 
    }

    
    </style>
</head>
<body>
<?php if (isset($_SESSION['message'])): ?>
    <script>
        alert('<?= $_SESSION['message'] ?>');
        <?php unset($_SESSION['message']); ?>
    </script>
<?php endif; ?>
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
<main>
    <div class="container">
        <div class="flex-container">
            <div class="form-container">
                <form method="POST">
                    <label for="new_full_name">ФИО:</label>
                    <input type="text" name="new_full_name" placeholder="ФИО" required>
                    <label for="new_specialization">Специализация:</label>
                    <input type="text" name="new_specialization" placeholder="Специализация" required>
                    <label for="new_gender">Пол:</label>
                    <select name="new_gender">
                        <option value="муж">Мужской</option>
                        <option value="жен">Женский</option>
                    </select>
                    <label for="new_phone">Телефон:</label>
                    <input type="text" name="new_phone" placeholder="Телефон" required>
                    <label for="new_birth_date">Дата рождения:</label>
                    <input type="date" name="new_birth_date" required>
                    <label for="new_hire_date">Дата начала работы:</label>
                    <input type="date" name="new_hire_date" required>
                    <button type="submit" name="add_master" class="button">Добавить мастера</button>
                </form>
                <form method="GET">
                    <label for="full_name">Поиск по ФИО:</label>
                    <input type="text" name="full_name" placeholder="Поиск по ФИО" value="<?= htmlspecialchars($_GET['full_name'] ?? '') ?>">
                    <label for="specialization">Поиск по специализации:</label>
                    <input type="text" name="specialization" placeholder="Поиск по специализации" value="<?= htmlspecialchars($_GET['specialization'] ?? '') ?>">
                    <label for="gender">Пол:</label>
                    <select name="gender">
                        <option value="">Выберите пол</option>
                        <option value="муж"<?= isset($_GET['gender']) && $_GET['gender'] == 'муж' ? ' selected' : '' ?>>Мужской</option>
                        <option value="жен"<?= isset($_GET['gender']) && $_GET['gender'] == 'жен' ? ' selected' : '' ?>>Женский</option>
                    </select>
                    <button type="submit" class="button">Поиск</button>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Специализация</th>
                            <th>Дата рождения</th>
                            <th>Пол</th>
                            <th>Телефон</th>
                            <th>Дата начала работы</th>
                            <th>Дата увольнения</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($masters as $master): ?>
                        <tr>
                            <form method="POST">
                                <td><?= htmlspecialchars($master['master_id']) ?><input type="hidden" name="master_id" value="<?= $master['master_id'] ?>"></td>
                                <td><input type="text" name="full_name" value="<?= htmlspecialchars($master['full_name']) ?>"></td>
                                <td><input type="text" name="specialization" value="<?= htmlspecialchars($master['specialization']) ?>"></td>
                                <td><input type="date" name="birth_date" value="<?= htmlspecialchars((new DateTime($master['birth_date']))->format('Y-m-d')) ?>"></td>
                                <td>
                                    <select name="gender">
                                        <option value="муж"<?= $master['gender'] === 'муж' ? ' selected' : '' ?>>Мужской</option>
                                        <option value="жен"<?= $master['gender'] === 'жен' ? ' selected' : '' ?>>Женский</option>
                                    </select>
                                </td>
                                <td><input type="text" name="phone" value="<?= htmlspecialchars($master['phone']) ?>"></td>
                                <td><input type="date" name="hire_date" value="<?= htmlspecialchars((new DateTime($master['hire_date']))->format('Y-m-d')) ?>"></td>
                                <td><input type="date" name="termination_date" value="<?= $master['termination_date'] ? htmlspecialchars((new DateTime($master['termination_date']))->format('Y-m-d')) : '' ?>"></td>
                                <td><button type="submit" class="button">Обновить</button></td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
