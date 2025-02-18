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

    case 'login':
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Auth::attempt(input('email'), input('password'))) {
                redirect('catalog');
            }
            $error = 'Неверный email или пароль';
        }
        render('login', ['title' => 'Вход', 'error' => $error]);
        break;

    case 'register':
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $v = new Validator($_POST);
            $v->required('full_name')->required('email')->required('password')->minLength('password', 6);
            if ($v->fails()) {
                $errors = $v->errors();
            } else {
                $id = Auth::register($_POST);
                $_SESSION['user_id'] = $id;
                flash('Добро пожаловать в библиотеку!');
                redirect('catalog');
            }
        }
        render('register', ['title' => 'Регистрация', 'errors' => $errors]);
        break;

    case 'logout':
        Auth::logout();
        redirect('catalog');
        break;

    case 'librarian':
        Auth::requireRole(['librarian', 'admin']);
        render('librarian', [
            'title' => 'Выдача и возврат',
            'loans' => LoanService::activeLoans(),
            'stats' => ReportService::stats(),
        ]);
        break;

    default:
        http_response_code(404);
        render('error', ['title' => 'Не найдено', 'message' => 'Страница не найдена']);
}
