<?php
echo '<body style="font-family:monospace; margin-left:auto; margin-right:auto; max-width:800px; padding:20px;">';
echo '<h1>POST-запрос</h1>';


echo '<pre>';
var_export($_POST);
echo '<br>';
var_export($_FILES);
echo '</pre>';


echo '<h2>Примеры:</h2>';
echo '<pre>';

$name = $_POST['username']; // может вызвать ошибку, если ключа нет
$name = $_POST['username'] ?? ''; // безопасно, если ключа нет - оператор объединения с null

$password = $_POST['password'] ?? '';

// это не закрывает вопрос отсутствия нужного поля
if (empty($name) || empty($password)) {
    echo 'Имя пользователя или пароль не указаны';
} else {
    echo "Имя пользователя: $name Пароль: $password";
}

// альтерантивный вариант проверки
$name = is_string($name) ? trim($name) : ''; // проверить является ли значение строкой. 

echo '<br>';
$hobbies = filter_input(INPUT_POST, 'hobbies', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
var_dump($hobbies);
$dishes = filter_input(INPUT_POST, 'dish', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
var_dump($dishes);

// 1. Проверка Email
$email = filter_input(INPUT_POST, 'user_email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    echo "Неверный формат почты";
} elseif ($email === null) {
    echo "Вы не заполнили поле почты!";
} else {
    echo "Почта принята: " . $email;
}

echo '<br>';

// 2. Проверка числа (например, возраст от 18)
$options = ['options' => ['min_range' => 18]];
$age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, $options);
if ($age === null) {
    echo "Вы не заполнили поле возраста!"; // Поля вообще нет
} elseif ($age === false) {
    echo "Неверный формат. Возраст должен быть числом от 18."; // Ввели дичь или число < 18
} else {
    echo "Возраст принят: " . $age;
}

echo '<h2>Файлы</h2>';

// Примечание: Если файл больше лимита post_max_size в php.ini, сервер оборвет соединение, 
// и массив $_FILES (как и $_POST) будет абсолютно пустым.

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['avatar']['tmp_name'];
    
    // Проверка реального типа файла
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realType = finfo_file($finfo, $tmpPath);

    if ($realType === 'image/jpeg' || $realType === 'image/png') {
        // Генерируем безопасное имя
        $safeName = uniqid('img_', true) . '.jpg';
        $destination = __DIR__ . '/uploads/' . $safeName;
        
        if (move_uploaded_file($tmpPath, $destination)) {
            echo "Файл успешно загружен: $safeName";
        } else {
            echo "Ошибка при перемещении файла.";
        }
    } else {
        echo "Неверный тип файла. Допустимы только JPEG и PNG.";
    }
} else {
    echo "Файл не загружен или произошла ошибка при загрузке.";
    echo '<br>';
    echo 'Ошибка загрузки файла: ' . $_FILES['avatar']['error'];
}

echo '</pre>';
