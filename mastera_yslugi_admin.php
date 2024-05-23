<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "salon"; 

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Добавление услуги мастеру
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $selected_master_id = $_POST['master_id'];
    $selected_service_id = $_POST['service_id'];

    $insert_sql = "INSERT INTO masters_services (master_id, service_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ii", $selected_master_id, $selected_service_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Услуга успешно добавлена мастеру.";
    } else {
        $_SESSION['message'] = "Ошибка: " . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Удаление услуги мастера
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_master_service'])) {
    $delete_master_service_id = $_POST['delete_master_service_id'];

    $delete_sql = "DELETE FROM masters_services WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_master_service_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Услуга мастера успешно удалена.";
    } else {
        $_SESSION['message'] = "Ошибка: " . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Получение списка мастеров и услуг для формы
$masters_sql = "SELECT master_id, full_name FROM masters";
$services_sql = "SELECT service_id, name FROM services";
$masters_result = $conn->query($masters_sql);
$services_result = $conn->query($services_sql);

// Получение списка мастеров и их услуг
$masters_services_sql = "SELECT ms.id, m.full_name, s.name 
                         FROM masters_services ms
                         JOIN masters m ON ms.master_id = m.master_id
                         JOIN services s ON ms.service_id = s.service_id";
$masters_services_result = $conn->query($masters_services_sql);

// Поиск мастеров и услуг
$search_master = isset($_GET['search_master']) ? $_GET['search_master'] : '';
$search_service = isset($_GET['search_service']) ? $_GET['search_service'] : '';

$search_sql = "SELECT m.full_name AS master_name, s.name AS service_name
               FROM masters m
               JOIN masters_services ms ON m.master_id = ms.master_id
               JOIN services s ON ms.service_id = s.service_id
               WHERE m.full_name LIKE ? AND s.name LIKE ?
               ORDER BY m.full_name, s.name";

$stmt = $conn->prepare($search_sql);
$search_master_param = '%' . $search_master . '%';
$search_service_param = '%' . $search_service . '%';
$stmt->bind_param("ss", $search_master_param, $search_service_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Мастера и их услуги</title>
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
            padding: 10px; 
            display: flex; 
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
        h2 {
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

<div class="container">
    <div class="forms-section">
        <div class="search-section">
            <h2>Поиск мастеров и услуг</h2>
            <form method="GET">
                <label for="search_master">Поиск по ФИО мастера:</label>
                <input type="text" name="search_master" id="search_master" value="<?php echo htmlspecialchars($search_master); ?>">
                <label for="search_service">Поиск по названию услуги:</label>
                <input type="text" name="search_service" id="search_service" value="<?php echo htmlspecialchars($search_service); ?>">
                <button type="submit" class="button">Поиск</button>
            </form>
        </div>

        <div class="add-service-section">
            <h2>Добавить услугу мастеру</h2>
            <form method="POST">
                <label for="master_id">Выберите мастера:</label>
                <select name="master_id" id="master_id" required>
                    <option value="">--Выберите мастера--</option>
                    <?php
                    if ($masters_result->num_rows > 0) {
                        while($master = $masters_result->fetch_assoc()) {
                            echo '<option value="' . $master['master_id'] . '">' . htmlspecialchars($master['full_name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="service_id">Выберите услугу:</label>
                <select name="service_id" id="service_id" required>
                    <option value="">--Выберите услугу--</option>
                    <?php
                    if ($services_result->num_rows > 0) {
                        while($service = $services_result->fetch_assoc()) {
                            echo '<option value="' . $service['service_id'] . '">' . htmlspecialchars($service['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <button type="submit" name="add_service" class="button">Добавить услугу</button>
            </form>
        </div>


        <div class="delete-master-service-section">
            <h2>Удалить услугу мастера</h2>
            <form method="POST">
                <label for="delete_master_service_id">Выберите услугу мастера:</label>
                <select name="delete_master_service_id" id="delete_master_service_id" required>
                    <option value="">--Выберите услугу--</option>
                    <?php
                    if ($masters_services_result->num_rows > 0) {
                        while($ms = $masters_services_result->fetch_assoc()) {
                            echo '<option value="' . $ms['id'] . '">' . htmlspecialchars($ms['full_name']) . ' - ' . htmlspecialchars($ms['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <button type="submit" name="delete_master_service" class="button">Удалить услугу мастера</button>
            </form>
        </div>
    </div>

    <div class="results-section">
        <h2>Мастера и их услуги</h2>

        <table>
            <tr>
                <th>Мастер</th>
                <th>Услуги</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                $current_master = '';
                while($row = $result->fetch_assoc()) {
                    if ($row['master_name'] != $current_master) {
                        if ($current_master != '') {
                            echo '</ul></td></tr>';
                        }
                        $current_master = $row['master_name'];
                        echo '<tr><td>' . htmlspecialchars($current_master) . '</td><td><ul>';
                    }
                    echo '<li>' . htmlspecialchars($row['service_name']) . '</li>';
                }
                echo '</ul></td></tr>';
            } else {
                echo "<tr><td colspan='2'>Нет данных для отображения.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
