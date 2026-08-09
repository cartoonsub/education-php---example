<?php
header('Content-Type: application/json; charset=utf-8');

// 1. Проверяем, что это действительно JSON
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    http_response_code(415); // Unsupported Media Type
    echo json_encode(['error' => 'Ожидался Content-Type: application/json']);
    exit;
}

// 2. Читаем сырое тело запроса ($_POST здесь будет пустым!)
$rawBody = file_get_contents('php://input');

// 3. Декодируем JSON
$data = json_decode($rawBody, true);

// 4. Проверяем, что декодирование прошло успешно
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Некорректный JSON: ' . json_last_error_msg()]);
    exit;
}

// 5. Достаём и валидируем поля
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$dishes   = $data['dish'] ?? [];
$agree    = (bool)($data['agree'] ?? false);
$avatar   = $data['avatar'] ?? null; // data:image/...;base64,... либо null

$errors = [];

if ($username === '') {
    $errors[] = 'username обязателен';
}
if (strlen($password) < 8) {
    $errors[] = 'password должен быть не короче 8 символов';
}
if (!is_array($dishes)) {
    $errors[] = 'dish должен быть массивом';
}
if (!$agree) {
    $errors[] = 'нужно принять правила (agree)';
}

if (!empty($errors)) {
    http_response_code(422); // Unprocessable Entity
    echo json_encode(['error' => 'Ошибка валидации', 'details' => $errors]);
    exit;
}

// 6. (Опционально) Если пришла картинка в base64 — декодируем и сохраняем
$avatarPath = null;
if ($avatar && preg_match('/^data:image\/(jpeg|png);base64,(.+)$/', $avatar, $m)) {
    $ext = $m[1] === 'jpeg' ? 'jpg' : 'png';
    $binary = base64_decode($m[2]);

    // проверяем реальный тип по содержимому, а не доверяем расширению из строки
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realType = $finfo->buffer($binary);
    if (!in_array($realType, ['image/jpeg', 'image/png'], true)) {
        http_response_code(422);
        echo json_encode(['error' => 'avatar: недопустимый тип файла']);
        exit;
    }

    $safeName = uniqid('avatar_', true) . '.' . $ext;
    $avatarPath = __DIR__ . '/uploads/' . $safeName;
    // file_put_contents($avatarPath, $binary); // раскомментировать, когда папка uploads/ готова
}

// 7. Успешный ответ
echo json_encode([
    'status' => 'ok',
    'received' => [
        'username' => $username,
        'dish'     => array_values($dishes),
        'agree'    => $agree,
        'avatar_saved' => $avatarPath !== null,
    ],
]);