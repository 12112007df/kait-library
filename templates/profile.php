<h1>Личный кабинет</h1>
<p><?= e($user['full_name']) ?> · <?= e($user['email']) ?><?= $user['group_name'] ? ' · ' . e($user['group_name']) : '' ?></p>

<h2>Мои книги</h2>
<?php if (!$loans): ?>
    <p class="muted">Вы ещё не брали книг.</p>
<?php else: ?>
    <table class="table">
        <thead>
        <tr><th>Книга</th><th>Инв. номер</th><th>Выдана</th><th>Вернуть до</th><th>Возвращена</th><th>Штраф</th></tr>
        </thead>
        <tbody>
        <?php foreach ($loans as $l): ?>
            <?php $late = !$l['returned_at'] && strtotime($l['due_at']) < time(); ?>
            <tr class="<?= $late ? 'row-late' : '' ?>">
                <td><?= e($l['title']) ?><br><span class="muted"><?= e($l['author']) ?></span></td>
                <td><?= e($l['inventory_number']) ?></td>
                <td><?= format_date($l['issued_at']) ?></td>
                <td><?= format_date($l['due_at']) ?><?= $late ? ' <b>просрочена</b>' : '' ?></td>
                <td><?= format_date($l['returned_at']) ?></td>
                <td><?= $l['fine'] > 0 ? $l['fine'] . ' руб.' : '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Мои бронирования</h2>
<?php if (!$reservations): ?>
    <p class="muted">Бронирований нет.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Книга</th><th>Дата</th><th>Статус</th><th>Очередь</th></tr></thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= e($r['title']) ?><br><span class="muted"><?= e($r['author']) ?></span></td>
                <td><?= format_date($r['created_at']) ?></td>
                <td><?= ['active' => 'ожидает', 'done' => 'выдана', 'cancelled' => 'отменена'][$r['status']] ?></td>
                <td><?= $r['status'] === 'active' ? ReservationRepository::queuePosition($r['book_id'], $user['id']) : '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
