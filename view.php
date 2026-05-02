<?php
$db_user = 'u82315';
$db_pass = '6926251';   
$db_name = 'u82315';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT a.*, GROUP_CONCAT(l.name SEPARATOR ', ') AS languages
        FROM application a
        LEFT JOIN application_language al ON a.id = al.application_id
        LEFT JOIN language l ON al.language_id = l.id
        GROUP BY a.id
        ORDER BY a.id DESC
    ");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохранённые анкеты</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0,0,0,0.2);
            border-radius: 16px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #3a86ff;
        }
        th {
            background: #0a2a3e;
            color: #87cefa;
            font-weight: bold;
        }
        tr:hover td {
            background: rgba(58,134,255,0.1);
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        .back-link a {
            background: #3a86ff;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
        }
        .back-link a:hover {
            background: #2666cc;
        }
    </style>
</head>
<body>
<video autoplay muted loop id="bgVideo">
    <source src="background.mp4" type="video/mp4">
</video>

<header class="site-header">
    <div class="header-content">
        <h1>Сохранённые анкеты</h1>
    </div>
</header>

<div class="container">
    <p>Всего записей: <?= count($applications) ?></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Биография</th>
                <th>Согласие</th>
                <th>Языки</th>
                <th>Дата создания</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?= htmlspecialchars($app['id']) ?></td>
                    <td><?= htmlspecialchars($app['full_name']) ?></td>
                    <td><?= htmlspecialchars($app['phone']) ?></td>
                    <td><?= htmlspecialchars($app['email']) ?></td>
                    <td><?= htmlspecialchars($app['birth_date']) ?></td>
                    <td><?= $app['gender'] === 'male' ? 'Мужской' : 'Женский' ?></td>
                    <td><?= nl2br(htmlspecialchars($app['biography'])) ?></td>
                    <td><?= $app['contract_accepted'] ? '✅ Да' : '❌ Нет' ?></td>
                    <td><?= htmlspecialchars($app['languages']) ?></td>
                    <td><?= htmlspecialchars($app['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="back-link">
        <a href="index.php">← Вернуться к форме</a>
        <a href="admin.php">⚙️ Админ-панель</a>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; 2026 ЛАБА 7.</p>
    </div>
</footer>
</body>
</html>