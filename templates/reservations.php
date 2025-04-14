<?php $scripts = ['reservations']; ?>
<h1>Бронирования</h1>
<?php if (!$reservations): ?>
    <p class="muted">Активных бронирований нет.</p>
<?php else: ?>
    <table class="table">
        <thead>
        <tr><th>Дата</th><th>Читатель</th><th>Книга</th><th>Свободно</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr data-id="<?= $r['id'] ?>">
                <td><?= format_date($r['created_at']) ?></td>
                <td><?= e($r['full_name']) ?><br><span class="muted"><?= e($r['email']) ?> <?= e($r['group_name'] ?? '') ?></span></td>
                <td><?= e($r['title']) ?><br><span class="muted"><?= e($r['author']) ?></span></td>
                <td><?= BookRepository::availableCount($r['book_id']) ?></td>
                <td><button class="btn btn-small btn-light cancel-btn">Отменить</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Популярные книги</h2>
<table class="table">
    <thead><tr><th>Книга</th><th>Автор</th><th>Выдач</th></tr></thead>
    <tbody>
    <?php foreach ($popular as $p): ?>
        <tr><td><?= e($p['title']) ?></td><td><?= e($p['author']) ?></td><td><?= $p['loans'] ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
