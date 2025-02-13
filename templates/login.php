<div class="auth-box">
    <h1>Вход</h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=login">
        <label>Email
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </label>
        <label>Пароль
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-wide">Войти</button>
    </form>
    <p class="muted">Нет аккаунта? <a href="index.php?page=register">Зарегистрируйтесь</a></p>
</div>
