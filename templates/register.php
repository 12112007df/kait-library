<div class="auth-box">
    <h1>Регистрация читателя</h1>
    <form method="post" action="index.php?page=register" id="register-form">
        <label>ФИО
            <input type="text" name="full_name" required maxlength="150" value="<?= e($_POST['full_name'] ?? '') ?>">
            <?php if (!empty($errors['full_name'])): ?><span class="field-error"><?= e($errors['full_name']) ?></span><?php endif; ?>
        </label>
        <label>Email
            <input type="email" name="email" required maxlength="150" value="<?= e($_POST['email'] ?? '') ?>">
            <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
        </label>
        <label>Пароль (не менее 6 символов)
            <input type="password" name="password" required minlength="6">
            <?php if (!empty($errors['password'])): ?><span class="field-error"><?= e($errors['password']) ?></span><?php endif; ?>
        </label>
        <label>Категория
            <select name="category">
                <option value="student">Студент</option>
                <option value="teacher">Преподаватель</option>
            </select>
        </label>
        <label>Группа (для студентов)
            <input type="text" name="group_name" maxlength="20" pattern="[А-ЯA-Z]{2,5}[0-9]{3}" placeholder="ИСПО332">
        </label>
        <label>Телефон
            <input type="tel" name="phone" maxlength="20" placeholder="+7 900 000-00-00">
        </label>
        <button type="submit" class="btn btn-wide">Зарегистрироваться</button>
    </form>
</div>
