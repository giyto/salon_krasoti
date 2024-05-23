<?php
$pdo = new PDO('mysql:host=localhost;dbname=salon', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Обработка добавления, удаления типов услуг и услуг
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_type']) && !empty($_POST['new_type_name'])) {
        $stmt = $pdo->prepare("INSERT INTO type (name_type) VALUES (:new_type_name)");
        $stmt->execute(['new_type_name' => $_POST['new_type_name']]);
        $message = "<p>Новый тип услуги добавлен.</p>";
    } elseif (isset($_POST['delete_type']) && !empty($_POST['delete_type_id'])) {
        $checkServices = $pdo->prepare("SELECT COUNT(*) FROM services WHERE id_type = :id_type");
        $checkServices->execute(['id_type' => $_POST['delete_type_id']]);
        if ($checkServices->fetchColumn() > 0) {
            $message = "<p>Сначала удалите все услуги этого типа.</p>";
        } else {
            $stmt = $pdo->prepare("DELETE FROM type WHERE id_type = :delete_type_id");
            $stmt->execute(['delete_type_id' => $_POST['delete_type_id']]);
            $message = "<p>Тип услуги удален.</p>";
        }
    } elseif (isset($_POST['add_service'])) {
        $service_name = $_POST['service_name'];
        $service_price = $_POST['service_price'];
        $service_type_id = $_POST['service_type_id'];
        if (!empty($service_name) && !empty($service_price) && !empty($service_type_id)) {
            $stmt = $pdo->prepare("INSERT INTO services (name, price, id_type) VALUES (:name, :price, :id_type)");
            $stmt->execute(['name' => $service_name, 'price' => $service_price, 'id_type' => $service_type_id]);
            $message = "<p>Новая услуга добавлена.</p>";
        }
    } elseif (isset($_POST['delete_service']) && !empty($_POST['delete_service_id'])) {
        $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = :service_id");
        $stmt->execute(['service_id' => $_POST['delete_service_id']]);
        $message = "<p>Услуга удалена.</p>";
    }
}

$types = $pdo->query("SELECT id_type, name_type FROM type")->fetchAll();
$services = $pdo->query("SELECT service_id, name FROM services")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Lab Store</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; display: flex; }
        .forms-section { flex: 1; padding: 10px; display: flex; flex-direction: column; }
        .results-section { flex: 2; padding: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        input, select, button { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #45a049; }
        
    </style>
</head>
<body>
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
<main class="container">
    <div class="forms-section">
        <div class="search-section">
            <h2>Поиск и управление услугами</h2>
            <form action="" method="POST">
                <label for="new_type_name">Добавить новый тип услуги:</label>
                <input type="text" id="new_type_name" name="new_type_name" placeholder="Название типа услуги">
                <button type="submit" name="submit_type" class="button">Добавить тип</button>
                <hr>
                <label for="delete_type_id">Удалить тип услуги:</label>
                <select id="delete_type_id" name="delete_type_id">
                    <?php foreach ($types as $type) { echo "<option value=\"{$type['id_type']}\">{$type['name_type']}</option>"; } ?>
                </select>
                <button type="submit" name="delete_type" class="button">Удалить тип</button>
            </form>
            <form action="" method="GET">
                <label for="type_search">Поиск по типу услуги:</label>
                <input type="text" id="type_search" name="type_search" placeholder="Введите тип услуги..." value="<?php echo htmlspecialchars($_GET['type_search'] ?? '') ?>">
                <label for="name_search">Поиск по названию услуги:</label>
                <input type="text" id="name_search" name="name_search" placeholder="Введите название услуги..." value="<?php echo htmlspecialchars($_GET['name_search'] ?? '') ?>">
                <label for="price_search">Максимальная цена:</label>
                <input type="text" id="price_search" name="price_search" placeholder="Введите максимальную цену..." value="<?php echo htmlspecialchars($_GET['price_search'] ?? '') ?>">
                <button type="submit" class="button">Поиск</button>
            </form>
        </div>
        <div class="add-service-section">
            <h2>Добавить услугу мастеру</h2>
            <form action="" method="POST">
                <label for="service_name">Название услуги:</label>
                <input type="text" id="service_name" name="service_name" placeholder="Введите название услуги">
                <label for="service_price">Стоимость услуги:</label>
                <input type="number" id="service_price" name="service_price" placeholder="Введите стоимость услуги">
                <label for="service_type_id">Тип услуги:</label>
                <select id="service_type_id" name="service_type_id">
                    <?php foreach ($types as $type) { echo "<option value=\"{$type['id_type']}\">{$type['name_type']}</option>"; } ?>
                </select>
                <button type="submit" name="add_service" class="button">Добавить услугу</button>
                <hr>
                <label for="delete_service_id">Удалить услугу:</label>
                <select id="delete_service_id" name="delete_service_id">
                    <?php foreach ($services as $service) { echo "<option value=\"{$service['service_id']}\">{$service['name']}</option>"; } ?>
                </select>
                <button type="submit" name="delete_service" class="button">Удалить услугу</button>
            </form>
        </div>
    </div>
    <div class="results-section">
        <?php if (!empty($message)) echo $message; // Отображение сообщения о статусе действий ?>
        <?php
        // Обработка поиска и отображение результатов
        if (!empty($_GET)) {
            $stmt = $pdo->prepare("SELECT t.name_type, s.name, s.price FROM services AS s JOIN type AS t ON s.id_type = t.id_type WHERE t.name_type LIKE ? AND s.name LIKE ? AND s.price <= ?");
            $stmt->execute([
                "%{$_GET['type_search']}%",
                "%{$_GET['name_search']}%",
                $_GET['price_search'] ?: 999999
            ]);
            if ($stmt->rowCount() > 0) {
                echo "<table><tr><th>Тип услуги</th><th>Название услуги</th><th>Стоимость</th></tr>";
                foreach ($stmt as $row) {
                    echo "<tr><td>" . htmlspecialchars($row['name_type']) . "</td><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['price']) . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Услуги по заданным критериям не найдены.</p>";
            }
        }
        ?>
    </div>
</main>
</body>
</html>
