<?php $scripts = ['librarian']; ?>
<h1>Выдача и возврат</h1>

<div class="stats">
    <div><b><?= $stats['books'] ?></b><span>книг</span></div>
    <div><b><?= $stats['copies'] ?></b><span>экземпляров</span></div>
    <div><b><?= $stats['readers'] ?></b><span>читателей</span></div>
    <div><b><?= $stats['active_loans'] ?></b><span>на руках</span></div>
</div>

<section class="card">
    <h2>Выдать книгу</h2>
    <form id="issue-form" class="inline-form">
        <input type="email" id="reader-email" list="readers" placeholder="Email читателя" required>
        <datalist id="readers"></datalist>
        <input type="text" id="inventory" placeholder="Инвентарный номер, например INV-0002" required pattern="INV-[0-9]{4}(-[0-9]+)?">
        <button type="submit" class="btn">Выдать</button>
    </form>
    <div id="issue-result"></div>
</section>

<section>
    <h2>Книги на руках</h2>
    <table class="table" id="loans-table">
        <thead>
        <tr><th>Читатель</th><th>Книга</th><th>Инв. номер</th><th>Выдана</th><th>Вернуть до</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($loans as $l): ?>
            <?php $late = strtotime($l['due_at']) < time(); ?>
            <tr class="<?= $late ? 'row-late' : '' ?>" data-loan-id="<?= $l['id'] ?>">
                <td><?= e($l['full_name']) ?><br><span class="muted"><?= e($l['email']) ?></span></td>
                <td><?= e($l['title']) ?></td>
                <td><?= e($l['inventory_number']) ?></td>
                <td><?= format_date($l['issued_at']) ?></td>
                <td><?= format_date($l['due_at']) ?></td>
                <td><button class="btn btn-small return-btn">Принять</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
