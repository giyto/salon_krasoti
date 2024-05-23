<?php
session_start();

$host = 'localhost';
$dbname = 'salon';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Загрузка списка услуг
    $servicesQuery = $pdo->query("SELECT service_id, name, price FROM services");
    $services = $servicesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Загрузка списка мастеров и связанных с ними услуг
    $masterServiceQuery = $pdo->query("SELECT m.master_id, m.full_name, m.termination_date, ms.service_id FROM masters m JOIN masters_services ms ON m.master_id = ms.master_id");
    $masterServices = $masterServiceQuery->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_SESSION['user_id'])) {
        $client_id = $_SESSION['user_id'];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['check_availability'])) {
                // Проверка занятости мастера
                $master_id = $_POST['master_id'];
                $appointment_date_time = $_POST['appointment_date_time'];

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE master_id = :master_id AND appointment_date_time = :appointment_date_time");
                $stmt->bindParam(':master_id', $master_id);
                $stmt->bindParam(':appointment_date_time', $appointment_date_time);
                $stmt->execute();
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    echo 'busy';
                } else {
                    echo 'free';
                }
                exit;
            } else {
                // Создание новой записи на прием
                $service_id = $_POST['service_id'] ?? null;
                $master_id = $_POST['master_id'] ?? null;
                $date_time = $_POST['appointment_time'] ?? null;
                $price = $_POST['price'] ?? null;

                if ($service_id && $master_id && $date_time && $price) {
                    $currentDateTime = new DateTime();
                    $appointmentDateTime = new DateTime($date_time);

                    if ($appointmentDateTime < $currentDateTime) {
                        echo "<script>alert('Нельзя записаться на прошедшее время.'); window.history.back();</script>";
                        exit;
                    }

                    $dayOfWeek = $appointmentDateTime->format('N');
                    $time = $appointmentDateTime->format('H:i');

                    $workSchedule = [
                        1 => ['09:00', '20:00'], // Понедельник
                        2 => ['09:00', '20:00'], // Вторник
                        3 => ['09:00', '20:00'], // Среда
                        4 => ['09:00', '20:00'], // Четверг
                        5 => ['09:00', '20:00'], // Пятница
                        6 => ['10:00', '18:00'], // Суббота
                        7 => null // Воскресенье закрыто
                    ];

                    if (!$workSchedule[$dayOfWeek] || $time < $workSchedule[$dayOfWeek][0] || $time > $workSchedule[$dayOfWeek][1]) {
                        echo "<script>alert('В выбранное время салон не работает.'); window.history.back();</script>";
                        exit;
                    }

                    // Проверка занятости мастера
                    $stmt = $pdo->prepare("SELECT termination_date FROM masters WHERE master_id = :master_id");
                    $stmt->bindParam(':master_id', $master_id);
                    $stmt->execute();
                    $termination_date = $stmt->fetchColumn();

                    if ($termination_date) {
                        echo "<script>alert('Этот мастер больше не работает.'); window.history.back();</script>";
                        exit;
                    }

                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE master_id = :master_id AND appointment_date_time = :appointment_date_time");
                    $stmt->bindParam(':master_id', $master_id);
                    $stmt->bindParam(':appointment_date_time', $date_time);
                    $stmt->execute();
                    $count = $stmt->fetchColumn();

                    if ($count > 0) {
                        echo "<script>alert('Этот мастер уже занят в указанное время.'); window.history.back();</script>";
                        exit;
                    }

                    $stmt = $pdo->prepare("INSERT INTO appointments (service_id, master_id, client_id, appointment_date_time, price) VALUES (:service_id, :master_id, :client_id, :appointment_date_time, :price)");
                    $stmt->bindParam(':service_id', $service_id);
                    $stmt->bindParam(':master_id', $master_id);
                    $stmt->bindParam(':client_id', $client_id);
                    $stmt->bindParam(':appointment_date_time', $date_time);
                    $stmt->bindParam(':price', $price);

                    $stmt->execute();

                    echo "<script>alert('Вы успешно записаны на прием.'); window.location.href = 'my_appointments.php';</script>";
                } else {
                    echo "<script>alert('Пожалуйста, заполните все обязательные поля.'); window.history.back();</script>";
                }
            }
        }
    } 
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Запись на прием</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
 .container{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

    </style>
</head>
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
<body>
    <div class="container">
    <h2>Запись на прием</h2>
    <form action="" method="post">
        <label for="service">Услуга:</label>
        <select name="service_id" id="service" onchange="updatePrice();">
            <?php foreach ($services as $service) { ?>
                <option value="<?php echo $service['service_id']; ?>" data-price="<?php echo $service['price']; ?>"><?php echo $service['name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <label for="master">Мастер:</label>
        <select name="master_id" id="master">
            <!-- Мастера будут добавлены динамически через JavaScript -->
        </select>
        <br>
        <label for="appointment_time">Время приема:</label>
        <input type="datetime-local" id="appointment_time" name="appointment_time">
        <br>
        <!-- Скрытое поле для цены -->
        <input type="hidden" id="price" name="price">
        <br>
        <button type="submit" class="button">Записаться</button>
        
    </form>
    
    <script>
    function updatePrice() {
        var serviceSelect = document.getElementById('service');
        var priceInput = document.getElementById('price');
        var selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        priceInput.value = selectedOption.getAttribute('data-price');
    }

    function updateMasters() {
        var selectedServiceId = document.getElementById('service').value;
        var masters = <?php echo json_encode($masterServices); ?>;
        var masterSelect = document.getElementById('master');
        masterSelect.innerHTML = '';

        masters.forEach(function(master) {
            if (master.service_id == selectedServiceId && master.termination_date === null) {
                var option = document.createElement('option');
                option.value = master.master_id;
                option.textContent = master.full_name;
                masterSelect.appendChild(option);
            }
        });
    }

    document.getElementById('service').addEventListener('change', function() {
        updateMasters();
        updatePrice();
    });

    function validateAppointmentDateTime() {
        var appointmentDateTime = document.getElementById('appointment_time').value;
        var currentDateTime = new Date().toISOString().slice(0, 16);

        if (appointmentDateTime < currentDateTime) {
            alert('Нельзя записаться на прошедшее время.');
            return false;
        }

        var appointmentDate = new Date(appointmentDateTime);
        var dayOfWeek = appointmentDate.getDay();
        var time = appointmentDateTime.slice(11, 16);

        var workSchedule = {
            1: ['09:00', '20:00'], // Понедельник
            2: ['09:00', '20:00'], // Вторник
            3: ['09:00', '20:00'], // Среда
            4: ['09:00', '20:00'], // Четверг
            5: ['09:00', '20:00'], // Пятница
            6: ['10:00', '18:00'], // Суббота
            0: null // Воскресенье закрыто
        };

        if (!workSchedule[dayOfWeek] || time < workSchedule[dayOfWeek][0] || time > workSchedule[dayOfWeek][1]) {
            alert('В выбранное время салон не работает.');
            return false;
        }

        var masterId = document.getElementById('master').value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('check_availability=true&master_id=' + masterId + '&appointment_date_time=' + appointmentDateTime);

        if (xhr.responseText === 'busy') {
            alert('Этот мастер уже занят в указанное время.');
            return false;
        }

        return true;
    }

    window.onload = function() {
        updateMasters();
        updatePrice();
    };
    </script>
    </script>
</body>
</html>