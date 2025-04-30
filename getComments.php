<?php

require_once __DIR__ . '/database/connectDatabase.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $searchWord = $data['searchWord'] ?? null;

    if ($searchWord) {
        // Организовываем подключение к БД
        $connection = connectDatabase::getConnection('localhost', 'blog', 'root', '');

        // отобрать комментарии с вхождением подстроки
        // выбрать запись, к которой принадлежит комментарий
        // вывести заголовок записи + комментарий
        $comments = $connection->query("SELECT postId, body FROM comments");
        $response = [];
        // Построчная обработка
        while ($body = $comments->fetch(PDO::FETCH_ASSOC))
        {
            if (strpos($body['body'], $searchWord) !== false)
            {
                $response[] = array(
                    'id' => $body['postId'],
                    'title' => $connection->query("SELECT title FROM posts WHERE id = " . $body['postId'])->fetch(PDO::FETCH_ASSOC)['title'],
                    'body' => $body['body']
                );
            }
        }

        // Возвращаем результат в формате JSON
        echo json_encode([
            'success' => true,
            'data' => $response,
            'searchWord' => $searchWord
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Не найден образец поиска'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Поддерживается только POST запрос'
    ]);
}
