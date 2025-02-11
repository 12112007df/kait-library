<?php
/**
 * JSON API для клиентских скриптов: api.php?action=...
 */
require_once __DIR__ . '/../src/bootstrap.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'books.search':
            $books = BookRepository::search(
                $_GET['q'] ?? '',
                $_GET['genre'] ?? '',
                $_GET['sort'] ?? 'b.title'
            );
            $page = max(1, (int)($_GET['p'] ?? 1));
            $total = count($books);
            $items = array_slice($books, ($page - 1) * ITEMS_PER_PAGE, ITEMS_PER_PAGE);
            json_response([
                'ok' => true,
                'items' => $items,
                'total' => $total,
                'pages' => (int)ceil($total / ITEMS_PER_PAGE),
                'page' => $page,
            ]);
            break;

        case 'book.get':
            $book = BookRepository::find((int)($_GET['id'] ?? 0));
            if (!$book) {
                json_error('Книга не найдена', 404);
            }
            json_response(['ok' => true, 'book' => $book]);
            break;

        default:
            json_error('Неизвестное действие', 404);
    }
} catch (Exception $ex) {
    json_error($ex->getMessage(), 500);
}
