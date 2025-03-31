<?php $scripts = ['admin']; ?>
<h1>Пользователи</h1>
<table class="table">
    <thead>
    <tr><th>ФИО</th><th>Email</th><th>Категория</th><th>Группа</th><th>Роль</th><th>Статус</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr data-user-id="<?= $u['id'] ?>">
            <td><?= e($u['full_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['category']) ?></td>
            <td><?= e($u['group_name'] ?? '') ?></td>
            <td>
                <select class="role-select">
                    <?php foreach (['reader' => 'Читатель', 'librarian' => 'Библиотекарь', 'admin' => 'Администратор'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $u['role'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <label><input type="checkbox" class="block-check" <?= $u['is_blocked'] ? 'checked' : '' ?>> заблокирован</label>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
