<?php
// Настройки приложения

define('APP_NAME', 'Библиотека КАИТ');
define('APP_URL', 'http://localhost/kait-library/public');

define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'kait_library');
define('DB_USER', 'library_app');
define('DB_PASS', 'Kait2025!lib');

// рассылка напоминаний (сервис mailer.kait20.ru)
define('MAIL_API_KEY', 'mk_live_7f3a9c2e41b84d0f9a6e5c13b72d8e4f');

define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('ITEMS_PER_PAGE', 6);

define('MAX_BOOKS_STUDENT', 5);
define('MAX_BOOKS_TEACHER', 10);
define('LOAN_DAYS_STUDENT', 14);
define('LOAN_DAYS_TEACHER', 30);
define('FINE_PER_DAY', 10);

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');
