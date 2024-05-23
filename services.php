<?php
// Стартуем сессию для возможного использования в авторизации
session_start();

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'salon';
$user = 'root';
$password = '';

try {
    // Создание объекта PDO для подключения к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Запрос для получения типов услуг
    $stmtTypes = $pdo->query("SELECT * FROM type ORDER BY name_type");
    $types = $stmtTypes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список услуг - Салон красоты</title>
    <link rel="stylesheet" href="Css/style.css">
    <style>
        /* Базовые стили для списка услуг */
       
        .container1 {
            width: 80%;
            margin: auto;
            padding-bottom: 20px;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-top: 10px;
        }
        ul li {
            background: #fff3e0; /* светло-оранжевый фон */
            border-left: 5px solid #ff7043; /* ярко-оранжевый цвет для границ */
            margin-bottom: 10px;
            padding: 15px 20px;
            font-size: 18px;
            transition: all 0.5s ease;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        ul li:hover {
            background-color: #ffe0b2;
            transform: scale(1.03);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        h2 {
            color: #ec407a;
            padding-bottom: 5px;
            border-bottom: 2px solid #ff7043;
            margin-top: 20px;
            transition: color 0.3s ease;
        }
        h2:hover {
            color: #ff4081;
        }
       
    @media (max-width: 768px) {
        .container {
            width: 95%; /* Увеличение ширины контейнера для меньших экранов */
            padding: 10px; /* Уменьшение внутреннего отступа */
        }

        ul li {
            font-size: 16px; /* Уменьшение размера шрифта для улучшения читаемости */
            padding: 12px 15px; /* Уменьшение внутренних отступов элементов списка */
            flex-direction: right; /* Элементы списка теперь становятся вертикальными */
            align-items: middle; /* Выравнивание элементов списка в начало */
        }

        ul li:hover {
            transform: scale(1.01); /* Уменьшение масштаба анимации наведения для мобильных устройств */
        }

        h2 {
            font-size: 18px; /* Уменьшение размера заголовков */
        }

       
    }

    @media (max-width: 480px) {
        ul li {
            padding: 10px 10px; /* Дальнейшее уменьшение отступов для очень маленьких экранов */
        }

        h2 {
            font-size: 16px; /* Ещё меньше размер заголовков для улучшения читаемости */
        }

      
    }
   
    </style>
</head>
<body>
<header>
        <div class="container">
            <h1>Beauty Lab Store</h1>
            <nav>
                <ul>
                    <a href="index.html" class="button navigation-button"><span>Главная</span></a>
<a href="services.php" class="button navigation-button"><span>Услуги</span></a>
<a href="login_appointment.html" class="button navigation-button"><span>Личный кабинет</span></a>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container1">
            <?php foreach ($types as $type): ?>
                <h2><?= htmlspecialchars($type['name_type']) ?></h2>
                <ul>
                    <?php
                    // Запрос для получения услуг по текущему типу
                    $stmtServices = $pdo->prepare("SELECT * FROM services WHERE id_type = ? ORDER BY name");
                    $stmtServices->execute([$type['id_type']]);
                    $services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($services as $service):
                    ?>
                        <li>
                            <?= htmlspecialchars($service['name']) ?> - <?= number_format($service['price'], 2) ?> руб.
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>
    </main>
    <footer>
        <div class="container">
            <p>Контактная информация: +7 123 456-78-90, salon@example.com</p>
        </div>
    </footer>
</body>
</html>