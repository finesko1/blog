<?php

require_once 'Request.php';
require_once __DIR__ . '/../../database/connectDatabase.php';

// Организовываем подключение к БД
$connection = connectDatabase::getConnection('localhost', 'blog', 'root', '');

try
{
    // Получение записей
    $postsResponse = Request::sendRequest("https://jsonplaceholder.typicode.com/posts");
    $postsResponse = json_decode($postsResponse);

    // Получение комментариев
    $commentsResponse = Request::sendRequest("https://jsonplaceholder.typicode.com/comments");
    $commentsResponse = json_decode($commentsResponse);

    // Сохранение записей и комментариев как единое целое
    $connection->beginTransaction();

    $statementPosts = $connection->prepare("INSERT INTO posts (id, userId, title, body) VALUES (:id, :userId, :title, :body)");
    foreach($postsResponse as $post)
    {
        $statementPosts->execute([
           'id' => $post->id,
           'userId' => $post->userId,
           'title' => $post->title,
           'body' => $post->body
        ]);
        $lastPostId = $connection->lastInsertId();
    }
    $lastPostResponseId = end($postsResponse)->id;
    if ($lastPostId != $lastPostResponseId)
    {
        throw new Exception("Последняя запись публикации в БД($lastPostId) 
            не совпадает с последней полученной записью с сайта($lastPostResponseId)");
    }

    $statementComments = $connection->prepare("INSERT INTO comments (id, postId, name, email, body) VALUES (:id, :postId, :name, :email, :body)");
    foreach($commentsResponse as $comment)
    {
        $statementComments->execute([
           'id' => $comment->id,
           'postId' => $comment->postId,
           'name' => $comment->name,
           'email' => $comment->email,
           'body' => $comment->body
        ]);
        $lastCommentId = $connection->lastInsertId();
    }
    $lastCommentResponseId = end($commentsResponse)->id;
    if ($lastCommentId != $lastCommentResponseId)
    {
        throw new Exception("Последняя запись публикации в БД($lastCommentId) 
            не совпадает с последней полученной записью с сайта($lastCommentResponseId)");
    }

    $connection->commit();

    echo "Загружено '$lastPostId' публикаций и '$lastCommentId' комментариев \n";
}
catch(Exception $e)
{
    $connection->rollBack();
    var_dump($e->getMessage());
}
