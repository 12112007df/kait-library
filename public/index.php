<?php
/**
 * Точка входа для страниц сайта: index.php?page=...
 */
require_once __DIR__ . '/../src/bootstrap.php';

$page = $_GET['page'] ?? 'catalog';

switch ($page) {
    case 'catalog':
        render('catalog', [
            'title' => 'Каталог',
            'genres' => BookRepository::genres(),
        ]);
        break;

    case 'book':
        $book = BookRepository::find((int)($_GET['id'] ?? 0));
        if (!$book) {
            http_response_code(404);
            render('error', ['title' => 'Не найдено', 'message' => 'Книга не найдена']);
            break;
        }
        render('book', ['title' => $book['title'], 'book' => $book]);
        break;

    default:
        http_response_code(404);
        render('error', ['title' => 'Не найдено', 'message' => 'Страница не найдена']);
}
