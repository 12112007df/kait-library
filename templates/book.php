<?php $scripts = ['book']; ?>
<div class="book-page" data-book-id="<?= $book['id'] ?>">
    <div class="book-cover">
        <?php if ($book['cover']): ?>
            <img src="uploads/<?= e($book['cover']) ?>" alt="Обложка">
        <?php else: ?>
            <div class="cover-placeholder"><?= e(mb_substr($book['title'], 0, 1)) ?></div>
        <?php endif; ?>
    </div>
    <div class="book-info">
        <h1><?= e($book['title']) ?></h1>
        <p class="author"><?= e($book['author']) ?></p>
        <table class="props">
            <tr><th>Жанр</th><td><?= e($book['genre'] ?? '—') ?></td></tr>
            <tr><th>Год</th><td><?= e($book['year'] ?? '—') ?></td></tr>
            <tr><th>ISBN</th><td><?= e($book['isbn'] ?? '—') ?></td></tr>
            <tr><th>Рейтинг</th><td><?= $book['rating']['cnt'] ? $book['rating']['avg'] . ' (' . $book['rating']['cnt'] . ')' : 'нет оценок' ?></td></tr>
            <tr><th>В наличии</th><td><?= $book['available'] ?> из <?= count($book['copies']) ?></td></tr>
        </table>
        <p><?= nl2br(e($book['description'])) ?></p>

        <?php if ($user): ?>
            <button id="reserve-btn" class="btn">Забронировать</button>
            <span id="reserve-msg" class="muted"></span>
        <?php else: ?>
            <p class="muted"><a href="index.php?page=login">Войдите</a>, чтобы забронировать книгу или оставить отзыв.</p>
        <?php endif; ?>
    </div>
</div>

<section class="reviews">
    <h2>Отзывы</h2>
    <?php if ($user): ?>
        <form id="review-form" class="review-form">
            <label>Оценка
                <select name="rating" id="rating">
                    <option value="5">5 — отлично</option>
                    <option value="4">4 — хорошо</option>
                    <option value="3">3 — нормально</option>
                    <option value="2">2 — плохо</option>
                    <option value="1">1 — ужасно</option>
                </select>
            </label>
            <textarea name="text" id="review-text" rows="3" maxlength="1000" required placeholder="Ваш отзыв"></textarea>
            <button type="submit" class="btn">Отправить</button>
        </form>
    <?php endif; ?>
    <div id="reviews"></div>
</section>
