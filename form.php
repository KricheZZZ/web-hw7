<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 7 — Анкета (админ-панель)</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<video autoplay muted loop id="bgVideo">
    <source src="background.mp4" type="video/mp4">
</video>

<header class="site-header">
    <div class="header-content">
        <h1>Анкета пользователя</h1>
        <p>Заполните форму – при первой отправке будут сгенерированы логин и пароль</p>
    </div>
</header>

<div class="container">
    <?php if ($is_logged_in): ?>
        <div class="logged-in">
            ✅ Вы авторизованы (ID: <?= htmlspecialchars($user_id) ?>)
            <a href="index.php?logout=1" class="logout-link">Выйти</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
        <div class="messages">
            <?php foreach ($messages as $msg): ?>
                <?= $msg ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php">
        <!-- Блок ФИО: три поля в одну строку -->
        <div class="form-row">
            <div class="form-group half">
                <label for="last_name">Фамилия *</label>
                <input type="text" id="last_name" name="last_name"
                       value="<?= htmlspecialchars($values['last_name'] ?? '') ?>"
                       <?= !empty($errors['full_name']) ? 'class="error"' : '' ?>>
            </div>
            <div class="form-group half">
                <label for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name"
                       value="<?= htmlspecialchars($values['first_name'] ?? '') ?>"
                       <?= !empty($errors['full_name']) ? 'class="error"' : '' ?>>
            </div>
            <div class="form-group half">
                <label for="patronymic">Отчество</label>
                <input type="text" id="patronymic" name="patronymic"
                       value="<?= htmlspecialchars($values['patronymic'] ?? '') ?>"
                       <?= !empty($errors['full_name']) ? 'class="error"' : '' ?>>
            </div>
        </div>
        <?php if (!empty($errors['full_name'])): ?>
            <span class="field-error" style="margin-top: -15px; display: block; margin-bottom: 15px;">Некорректное ФИО (только буквы и пробелы, максимум 150 символов)</span>
        <?php endif; ?>

        <!-- Пол и дата рождения в одной строке -->
        <div class="form-row">
            <div class="form-group half">
                <label>Пол *</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="gender" value="male"
                            <?= ($values['gender'] ?? '') === 'male' ? 'checked' : '' ?>
                            <?= !empty($errors['gender']) ? 'class="error"' : '' ?>> Мужской
                    </label>
                    <label>
                        <input type="radio" name="gender" value="female"
                            <?= ($values['gender'] ?? '') === 'female' ? 'checked' : '' ?>
                            <?= !empty($errors['gender']) ? 'class="error"' : '' ?>> Женский
                    </label>
                </div>
                <?php if (!empty($errors['gender'])): ?>
                    <span class="field-error">Выберите пол</span>
                <?php endif; ?>
            </div>
            <div class="form-group half">
                <label for="birth_date">Дата рождения *</label>
                <input type="date" id="birth_date" name="birth_date"
                       value="<?= htmlspecialchars($values['birth_date'] ?? '') ?>"
                       <?= !empty($errors['birth_date']) ? 'class="error"' : '' ?>>
                <?php if (!empty($errors['birth_date'])): ?>
                    <span class="field-error">Некорректная дата</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Телефон и email в одной строке -->
        <div class="form-row">
            <div class="form-group half">
                <label for="phone">Телефон *</label>
                <input type="tel" id="phone" name="phone"
                       value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                       <?= !empty($errors['phone']) ? 'class="error"' : '' ?>>
                <?php if (!empty($errors['phone'])): ?>
                    <span class="field-error">Некорректный телефон</span>
                <?php endif; ?>
            </div>
            <div class="form-group half">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                       <?= !empty($errors['email']) ? 'class="error"' : '' ?>>
                <?php if (!empty($errors['email'])): ?>
                    <span class="field-error">Некорректный email</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="languages">Любимые языки программирования * (выберите один или несколько)</label>
            <select id="languages" name="languages[]" multiple size="6"
                    <?= !empty($errors['languages']) ? 'class="error"' : '' ?>>
                <?php foreach ($languages_from_db as $lang): ?>
                    <option value="<?= htmlspecialchars($lang) ?>"
                        <?= in_array($lang, $values['languages'] ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['languages'])): ?>
                <span class="field-error">Выберите хотя бы один допустимый язык</span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="biography">Биография</label>
            <textarea id="biography" name="biography" rows="6"
                <?= !empty($errors['biography']) ? 'class="error"' : '' ?>><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
            <?php if (!empty($errors['biography'])): ?>
                <span class="field-error">Биография слишком длинная</span>
            <?php endif; ?>
        </div>

        <div class="form-group checkbox">
            <label>
                <input type="checkbox" name="contract_accepted" value="1"
                    <?= !empty($values['contract_accepted']) ? 'checked' : '' ?>
                    <?= !empty($errors['contract_accepted']) ? 'class="error"' : '' ?>>
                Я ознакомлен(а) с контрактом *
            </label>
            <?php if (!empty($errors['contract_accepted'])): ?>
                <span class="field-error">Необходимо подтвердить согласие</span>
            <?php endif; ?>
        </div>

        <button type="submit"><?= $is_logged_in ? 'Сохранить изменения' : 'Сохранить' ?></button>
    </form>

    <div class="back-link">
        <a href="login.php">🔑 Войти в систему</a>
        <a href="view.php">📊 Просмотреть сохранённые анкеты</a>
        <a href="admin.php">⚙️ АДМИН ПАНЕЛЬ</a>
        <a href="l7.html">👀 Лабораторная работа №7 (АУДИТ)</a>
    </div>

    <?php if (!$is_logged_in): ?>
        <div class="auth-hint">
            <small>(Для редактирования данных нужна авторизация)</small>
        </div>
    <?php endif; ?>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; 2026 ЛАБА 7.</p>
    </div>
</footer>
</body>
</html>