<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Салон красоты</title>
    <link rel="stylesheet" href="css/style.css">
    <style> 

    body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        header {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
        }
      
        
        
        h2 {
            font-size: 2em;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            background: #ff5f6d;
            width: 60%;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
        }

       
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
<body>
<div class="container">
        <h2>Добро пожаловать, администратор!</h2>
    </div>
</body>
</html>