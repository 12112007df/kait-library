<?php $scripts = ['admin']; ?>
<h1>Книги</h1>
<?php if ($message): ?>
    <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>

<section class="card">
    <h2>Добавить книгу</h2>
    <form method="post" action="index.php?page=admin_books" class="form-grid">
        <label>Название <input type="text" name="title" required></label>
        <label>Автор <input type="text" name="author" required></label>
        <label>Жанр
            <select name="genre_id">
                <option value="">—</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Год <input type="number" name="year" min="1800" max="2100"></label>
        <label>ISBN <input type="text" name="isbn"></label>
        <label>Экземпляров <input type="number" name="copies" value="1" min="1" max="50"></label>
        <label class="span-2">Описание <textarea name="description" rows="3"></textarea></label>
        <button type="submit" class="btn">Добавить</button>
    </form>
</section>

<section>
    <h2>Все книги</h2>
    <table class="table">
        <thead><tr><th>Название</th><th>Автор</th><th>Жанр</th><th>В наличии</th><th>Обложка</th></tr></thead>
        <tbody>
        <?php foreach ($books as $b): ?>
            <tr data-book-id="<?= $b['id'] ?>">
                <td><a href="index.php?page=book&id=<?= $b['id'] ?>"><?= e($b['title']) ?></a></td>
                <td><?= e($b['author']) ?></td>
                <td><?= e($b['genre'] ?? '') ?></td>
                <td><?= $b['available'] ?></td>
                <td>
                    <input type="file" class="cover-input" accept="image/*">
                    <span class="cover-status muted"><?= $b['cover'] ? e($b['cover']) : '' ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
