<?php
$servername = "localhost"; // замените на ваш сервер
$username = "root"; // замените на ваше имя пользователя
$password = ""; // замените на ваш пароль
$dbname = "salon"; // замените на ваше имя базы данных

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение списка мастеров для формы
$masters_sql = "SELECT master_id, full_name FROM masters";
$masters_result = $conn->query($masters_sql);

$master_id = isset($_POST['master_id']) ? $_POST['master_id'] : null;
$date = isset($_POST['date']) ? $_POST['date'] : null;
$completed_master_id = isset($_POST['completed_master_id']) ? $_POST['completed_master_id'] : null;

if ($master_id && $date) {
    // SQL-запрос для получения данных
    $sql = "SELECT clients.full_name AS client, services.name AS service, appointments.appointment_date_time AS date_time, appointments.status AS status
            FROM appointments
            JOIN clients ON appointments.client_id = clients.client_id
            JOIN services ON appointments.service_id = services.service_id
            WHERE appointments.master_id = ? AND DATE(appointments.appointment_date_time) = ?
            ORDER BY appointments.appointment_date_time";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $master_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}

if ($completed_master_id) {
    // SQL-запрос для получения данных со статусом "выполнена"
    $completed_sql = "SELECT appointments.appointment_date_time AS date_time, services.name AS service, clients.full_name AS client, appointments.price AS price
                      FROM appointments
                      JOIN clients ON appointments.client_id = clients.client_id
                      JOIN services ON appointments.service_id = services.service_id
                      WHERE appointments.master_id = ? AND appointments.status = 'выполнена'
                      ORDER BY appointments.appointment_date_time";

    $completed_stmt = $conn->prepare($completed_sql);
    $completed_stmt->bind_param("i", $completed_master_id);
    $completed_stmt->execute();
    $completed_result = $completed_stmt->get_result();
} else {
    $completed_result = null;
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Отчет о предоставленных услугах</title>
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
        h2 {
            text-align: center;
        }
        
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
    <div class="forms-section">
        <div class="search-section">
            <h2>Отчет о занятости специалиста</h2>
            <form method="POST">
                <label for="master_id">Выберите специалиста:</label>
                <select name="master_id" id="master_id" required>
                    <option value="">--Выберите специалиста--</option>
                    <?php
                    if ($masters_result->num_rows > 0) {
                        while($master = $masters_result->fetch_assoc()) {
                            echo '<option value="' . $master['master_id'] . '"' . ($master['master_id'] == $master_id ? ' selected' : '') . '>' . htmlspecialchars($master['full_name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="date">Выберите дату:</label>
                <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($date); ?>" required>
                <input type="hidden" name="completed_master_id" value="<?php echo htmlspecialchars($completed_master_id); ?>">
                <button type="submit" class="button">Сформировать отчет</button>
            </form>
        </div>

        <div class="search-section">
            <h2>Сведения об оказании услуг мастером</h2>
            <form method="POST">
                <label for="completed_master_id">Выберите специалиста:</label>
                <select name="completed_master_id" id="completed_master_id" required>
                    <option value="">--Выберите специалиста--</option>
                    <?php
                    if ($masters_result->num_rows > 0) {
                        $masters_result->data_seek(0); // Сбрасываем указатель результата
                        while($master = $masters_result->fetch_assoc()) {
                            echo '<option value="' . $master['master_id'] . '"' . ($master['master_id'] == $completed_master_id ? ' selected' : '') . '>' . htmlspecialchars($master['full_name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <input type="hidden" name="master_id" value="<?php echo htmlspecialchars($master_id); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                <button type="submit" class="button">Сформировать отчет</button>
            </form>
        </div>
    </div>

    <div class="results-section">
        <h2>Отчет о занятости специалиста</h2>

        <?php
        if ($result && $result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>Клиент</th><th>Услуга</th><th>Дата</th><th>Время</th><th>Статус</th></tr>';

            // Вывод данных каждой строки
            while($row = $result->fetch_assoc()) {
                $date_time = explode(' ', $row['date_time']);
                $date = $date_time[0];
                $time = $date_time[1];

                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['client']) . '</td>';
                echo '<td>' . htmlspecialchars($row['service']) . '</td>';
                echo '<td>' . htmlspecialchars($date) . '</td>';
                echo '<td>' . htmlspecialchars($time) . '</td>';
                echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo "Нет данных для отображения.";
        }
        ?>

        <h2>Сведения об оказании услуг мастером</h2>

        <?php
        if ($completed_result && $completed_result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>Дата</th><th>Наименование услуги</th><th>ФИО клиента</th><th>Стоимость</th></tr>';

            // Вывод данных каждой строки
            while($row = $completed_result->fetch_assoc()) {
                $date_time = explode(' ', $row['date_time']);
                $date = $date_time[0];
                $time = $date_time[1];

                echo '<tr>';
                echo '<td>' . htmlspecialchars($date) . '</td>';
                echo '<td>' . htmlspecialchars($row['service']) . '</td>';
                echo '<td>' . htmlspecialchars($row['client']) . '</td>';
                echo '<td>' . htmlspecialchars(number_format($row['price'], 2)) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo "Нет данных для отображения.";
        }

        $conn->close();
        ?>
    </div>
</div>

</body>
</html>
