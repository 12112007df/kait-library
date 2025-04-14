<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="logo" href="index.php">📚 <?= e(APP_NAME) ?></a>
    <nav>
        <a href="index.php?page=catalog">Каталог</a>
        <?php if ($user): ?>
            <a href="index.php?page=profile">Мои книги</a>
            <?php if (in_array($user['role'], ['librarian', 'admin'])): ?>
                <a href="index.php?page=librarian">Выдача</a>
                <a href="index.php?page=reservations">Бронирования</a>
                <a href="index.php?page=debtors">Должники</a>
                <a href="index.php?page=admin_books">Книги</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="index.php?page=admin_users">Пользователи</a>
            <?php endif; ?>
            <span class="user-name"><?= e($user['full_name']) ?></span>
            <a href="index.php?page=logout" class="btn btn-light">Выйти</a>
        <?php else: ?>
            <a href="index.php?page=login" class="btn btn-light">Войти</a>
            <a href="index.php?page=register" class="btn">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <?php if ($msg = flash()): ?>
        <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>
    <?= $content ?>
</main>

<footer class="footer">
    © <?= date('Y') ?> ГБПОУ КАИТ № 20 · Библиотека
</footer>

<script>
    window.APP = {
        user: <?= json_encode($user ? ['id' => $user['id'], 'role' => $user['role'], 'name' => $user['full_name']] : null, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="assets/js/api.js"></script>
<?php foreach (($scripts ?? []) as $s): ?>
<script src="assets/js/<?= $s ?>.js"></script>
<?php endforeach; ?>
</body>
</html>
