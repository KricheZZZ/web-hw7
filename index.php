<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

// === АВТОРИЗАЦИЯ ===
$is_logged_in = isset($_SESSION['application_id']);
$user_id = $is_logged_in ? $_SESSION['application_id'] : null;

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

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
             error_log($e->getMessage());
            die("Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.");
        }
    }
    return $pdo;
}

function generate_unique_login($pdo) {
    do {
        $login = 'user_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
        $stmt = $pdo->prepare("SELECT id FROM application WHERE login = ?");
        $stmt->execute([$login]);
    } while ($stmt->fetch());
    return $login;
}

function generate_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

$allowed_languages = [
    'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python',
    'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'
];
$allowed_genders = ['male', 'female'];

// ====================== GET ======================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $messages = [];
    $errors = [];
    $values = [];

    $fields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'biography', 'contract_accepted', 'languages'];
    $name_parts = ['last_name', 'first_name', 'patronymic'];

    foreach ($fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
    }

    if ($errors['full_name']) $messages[] = '<div class="error-message">ФИО должно содержать только буквы и пробелы (макс. 150 символов).</div>';
    if ($errors['phone']) $messages[] = '<div class="error-message">Телефон должен содержать от 6 до 12 цифр, допускаются символы +, -, (, ), пробел.</div>';
    if ($errors['email']) $messages[] = '<div class="error-message">Введите корректный email.</div>';
    if ($errors['birth_date']) $messages[] = '<div class="error-message">Дата рождения должна быть в формате ГГГГ-ММ-ДД и не позже сегодняшнего дня.</div>';
    if ($errors['gender']) $messages[] = '<div class="error-message">Выберите пол.</div>';
    if ($errors['biography']) $messages[] = '<div class="error-message">Биография не должна превышать 10000 символов.</div>';
    if ($errors['contract_accepted']) $messages[] = '<div class="error-message">Необходимо подтвердить согласие.</div>';
    if ($errors['languages']) $messages[] = '<div class="error-message">Выберите хотя бы один язык программирования из списка.</div>';

    // Чтение частей ФИО
    foreach ($name_parts as $part) {
        $values[$part] = empty($_COOKIE[$part . '_value']) ? '' : $_COOKIE[$part . '_value'];
    }
    // Остальные поля
    $other_fields = ['phone', 'email', 'birth_date', 'gender', 'biography', 'contract_accepted', 'languages'];
    foreach ($other_fields as $field) {
        $values[$field] = empty($_COOKIE[$field . '_value']) ? '' : $_COOKIE[$field . '_value'];
    }
    if (!empty($_COOKIE['languages_value'])) {
        $values['languages'] = explode(',', $_COOKIE['languages_value']);
    } else {
        $values['languages'] = [];
    }
    $values['contract_accepted'] = !empty($_COOKIE['contract_accepted_value']) ? true : false;

    // Загрузка данных авторизованного пользователя
    if ($is_logged_in) {
        $has_validation_errors = false;
        foreach ($fields as $field) {
            if (!empty($_COOKIE[$field . '_error'])) $has_validation_errors = true;
        }
        if (!$has_validation_errors) {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM application WHERE id = ?");
            $stmt->execute([$user_id]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $name_parts_array = explode(' ', trim($row['full_name']), 3);
                $values['last_name'] = $name_parts_array[0] ?? '';
                $values['first_name'] = $name_parts_array[1] ?? '';
                $values['patronymic'] = $name_parts_array[2] ?? '';
                $values['phone'] = $row['phone'];
                $values['email'] = $row['email'];
                $values['birth_date'] = $row['birth_date'];
                $values['gender'] = $row['gender'];
                $values['biography'] = $row['biography'];
                $values['contract_accepted'] = $row['contract_accepted'] == 1;

                $values['languages'] = [];
                $lang_stmt = $pdo->prepare("
                    SELECT l.name 
                    FROM application_language al 
                    JOIN language l ON al.language_id = l.id 
                    WHERE al.application_id = ?
                ");
                $lang_stmt->execute([$user_id]);
                while ($l = $lang_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $values['languages'][] = $l['name'];
                }
            }
        }
    }

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 1);
        $messages[] = '<div class="success-message">Данные успешно сохранены!</div>';
    }
    if (!empty($_COOKIE['updated'])) {
        setcookie('updated', '', 1);
        $messages[] = '<div class="success-message">Данные успешно обновлены!</div>';
    }
    if (!empty($_COOKIE['generated_login'])) {
        $generated_login = $_COOKIE['generated_login'];
        $generated_password = $_COOKIE['generated_password'];
        setcookie('generated_login', '', 1);
        setcookie('generated_password', '', 1);
        $messages[] = '<div class="success-message credentials">
            <strong>Форма успешно отправлена!</strong><br>
            Ваш логин: <strong>' . htmlspecialchars($generated_login) . '</strong><br>
            Ваш пароль: <strong>' . htmlspecialchars($generated_password) . '</strong><br>
            <small>Сохраните их!</small>
        </div>';
    }

    $pdo = getDB();
    $languages_from_db = [];
    $stmt = $pdo->query("SELECT name FROM language ORDER BY name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $languages_from_db[] = $row['name'];
    }
    if (empty($languages_from_db)) {
        $languages_from_db = $allowed_languages;
    }

    include 'form.php';
    exit();
}

// ====================== POST ======================
else {
    $errors = false;

    // Получаем три части ФИО
    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $patronymic = trim($_POST['patronymic'] ?? '');
    $full_name = trim($last_name . ' ' . $first_name . ' ' . $patronymic);
    $full_name = preg_replace('/\s+/', ' ', $full_name);

    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $biography = trim($_POST['biography'] ?? '');
    $contract_accepted = isset($_POST['contract_accepted']) ? 1 : 0;
    $languages = $_POST['languages'] ?? [];

    // Валидация ФИО (объединённого)
    if (empty($full_name) || !preg_match('/^[а-яА-Яa-zA-Z\s]+$/u', $full_name) || strlen($full_name) > 150) {
        setcookie('full_name_error', '1', time() + 24*3600);
        $errors = true;
    }
    setcookie('last_name_value', $last_name, time() + 30*24*3600);
    setcookie('first_name_value', $first_name, time() + 30*24*3600);
    setcookie('patronymic_value', $patronymic, time() + 30*24*3600);

    // Телефон
    if (empty($phone) || !preg_match('/^[\d\s\-\+\(\)]{6,12}$/', $phone)) {
        setcookie('phone_error', '1', time() + 24*3600);
        $errors = true;
    }
    setcookie('phone_value', $phone, time() + 30*24*3600);

    // Email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24*3600);
        $errors = true;
    }
    setcookie('email_value', $email, time() + 30*24*3600);

    // Дата рождения
    if (empty($birth_date)) {
        setcookie('birth_date_error', '1', time() + 24*3600);
        $errors = true;
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $birth_date);
        if (!$date || $date->format('Y-m-d') !== $birth_date || $date > new DateTime('today')) {
            setcookie('birth_date_error', '1', time() + 24*3600);
            $errors = true;
        }
    }
    setcookie('birth_date_value', $birth_date, time() + 30*24*3600);

    // Пол
    if (empty($gender) || !in_array($gender, $allowed_genders)) {
        setcookie('gender_error', '1', time() + 24*3600);
        $errors = true;
    }
    setcookie('gender_value', $gender, time() + 30*24*3600);

    // Биография
    if (strlen($biography) > 10000) {
        setcookie('biography_error', '1', time() + 24*3600);
        $errors = true;
    }
    setcookie('biography_value', $biography, time() + 30*24*3600);

    // Чекбокс
    if (!$contract_accepted) {
        setcookie('contract_accepted_error', '1', time() + 24*3600);
        $errors = true;
    }
    setcookie('contract_accepted_value', $contract_accepted ? '1' : '0', time() + 30*24*3600);

    // Языки
    if (empty($languages)) {
        setcookie('languages_error', '1', time() + 24*3600);
        $errors = true;
    } else {
        foreach ($languages as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                setcookie('languages_error', '1', time() + 24*3600);
                $errors = true;
                break;
            }
        }
    }
    setcookie('languages_value', implode(',', $languages), time() + 30*24*3600);

    if ($errors) {
        header('Location: index.php');
        exit();
    }

    try {
        $pdo = getDB();
        $pdo->beginTransaction();

        if ($is_logged_in) {
            $stmt = $pdo->prepare("
                UPDATE application 
                SET full_name = :full_name, phone = :phone, email = :email,
                    birth_date = :birth_date, gender = :gender, 
                    biography = :biography, contract_accepted = :contract_accepted
                WHERE id = :id
            ");
            $stmt->execute([
                ':full_name' => $full_name,
                ':phone' => $phone,
                ':email' => $email,
                ':birth_date' => $birth_date,
                ':gender' => $gender,
                ':biography' => $biography,
                ':contract_accepted' => $contract_accepted,
                ':id' => $user_id
            ]);
            $application_id = $user_id;

            $pdo->prepare("DELETE FROM application_language WHERE application_id = ?")->execute([$application_id]);
            setcookie('updated', '1', time() + 24*3600);
        } else {
            $login = generate_unique_login($pdo);
            $plain_password = generate_password();
            $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO application 
                (full_name, phone, email, birth_date, gender, biography, 
                 contract_accepted, login, password_hash)
                VALUES 
                (:full_name, :phone, :email, :birth_date, :gender, :biography, 
                 :contract_accepted, :login, :password_hash)
            ");
            $stmt->execute([
                ':full_name' => $full_name,
                ':phone' => $phone,
                ':email' => $email,
                ':birth_date' => $birth_date,
                ':gender' => $gender,
                ':biography' => $biography,
                ':contract_accepted' => $contract_accepted,
                ':login' => $login,
                ':password_hash' => $password_hash
            ]);
            $application_id = $pdo->lastInsertId();

            setcookie('generated_login', $login, time() + 3600);
            setcookie('generated_password', $plain_password, time() + 3600);
            setcookie('save', '1', time() + 24*3600);
        }

        // Сохранение языков
        $lang_map = [];
        $stmt = $pdo->query("SELECT id, name FROM language");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lang_map[$row['name']] = $row['id'];
        }
        $stmt = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
        foreach ($languages as $lang_name) {
            if (isset($lang_map[$lang_name])) {
                $stmt->execute([$application_id, $lang_map[$lang_name]]);
            }
        }

        $pdo->commit();

        $fields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'biography', 'contract_accepted', 'languages'];
        foreach ($fields as $field) {
            setcookie($field . '_error', '', 1);
        }
       

        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        setcookie('db_error', '1', time() + 24*3600);
        header('Location: index.php');
        exit();
    }
}
?>