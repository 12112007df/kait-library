<h1>Должники</h1>
<p><a class="btn btn-light" href="index.php?page=debtors&export=1">Выгрузить в Excel (CSV)</a></p>

<?php if (!$debtors): ?>
    <p class="muted">Должников нет.</p>
<?php endif; ?>

<?php foreach ($debtors as $d): ?>
    <div class="card debtor">
        <h3><?= $d['user']['full_name'] ?> <span class="muted"><?= $d['user']['group_name'] ?></span></h3>
        <p class="muted"><?= $d['user']['email'] ?> · <?= $d['user']['phone'] ?? 'телефон не указан' ?></p>
        <table class="table">
            <tr><th>Книга</th><th>Инв. номер</th><th>Срок</th><th>Просрочка</th><th>Штраф</th></tr>
            <?php foreach ($d['books'] as $b): ?>
                <tr>
                    <td><?= $b['title'] ?></td>
                    <td><?= $b['inventory_number'] ?></td>
                    <td><?= format_date($b['due_at']) ?></td>
                    <td><?= $b['days_late'] ?> <?= plural($b['days_late'], 'день', 'дня', 'дней') ?></td>
                    <td><?= $b['fine'] ?> руб.</td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p><b>Итого штраф: <?= $d['total_fine'] ?> руб.</b></p>
    </div>
<?php endforeach; ?>
