<?php
session_start();

if (isset($_SESSION['application_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$errors = [];
$login_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password_input)) {
        $errors[] = 'Введите логин и пароль';
    } else {
        function getDB() {
            static $pdo = null;
            if ($pdo === null) {
                $db_host = 'localhost';
                $db_user = 'u82315';
                $db_pass = '6926251';
                $db_name = 'u82315';
                try {
                    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    die("Ошибка подключения к БД: " . $e->getMessage());
                }
            }
            return $pdo;
        }

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id, password_hash FROM application WHERE login = ?");
        $stmt->execute([$login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password_input, $user['password_hash'])) {
            $_SESSION['application_id'] = $user['id'];
            header('Location: index.php');
            exit();
        } else {
            $errors[] = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Задание 7</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<video autoplay muted loop id="bgVideo">
    <source src="background.mp4" type="video/mp4">
</video>

<header class="site-header">
    <div class="header-content">
        <h1>Вход в систему</h1>
        <p>Введите логин и пароль, полученные при отправке анкеты</p>
    </div>
</header>

<div class="container">
    <?php if (!empty($errors)): ?>
        <div class="messages">
            <?php foreach ($errors as $err): ?>
                <div class="error-message"><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Логин</label>
            <input type="text" name="login" value="<?= htmlspecialchars($login_input) ?>" required>
        </div>
        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Войти</button>
    </form>

    <div class="back-link">
        <a href="index.php">← Вернуться к анкете</a>
        <a href="view.php">📊 Просмотреть анкеты</a>
        <a href="admin.php">⚙️ Админ-панель</a>
    </div>

    <div class="auth-hint">
        Нет аккаунта?<br>Заполните форму на главной странице — логин и пароль будут сгенерированы автоматически.
    </div>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; 2026 ЛАБА 7.</p>
    </div>
</footer>
</body>
</html>