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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
       
        h2 {
            font-size: 2em;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 10px;
            background: #ff5f6d;
            width: 70%;
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
                    <a href="director_page.php" class="button navigation-button"><span>Главная</span></a>
                    <a href="ot4eti_dicercot.php" class="button navigation-button"><span>Общие отчеты</span></a>
                    <a href="ot4eti_dicercot1.php" class="button navigation-button"><span>Объем работы мастеров</span></a>
                    <a href="ot4eti_dicercot2.php" class="button navigation-button"><span>Объем оказанных услуг</span></a>
                    <a href="logout.php" class="button navigation-button"><span>Выход</span></a>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <h2>Добро пожаловать, Директор!</h2>
    </div>
</body>
</html>
