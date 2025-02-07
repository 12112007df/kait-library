<?php $scripts = ['catalog']; ?>
<h1>Каталог книг</h1>

<form id="search-form" class="search">
    <input type="text" name="q" id="q" placeholder="Название или автор" maxlength="100">
    <select name="genre" id="genre">
        <option value="">Все жанры</option>
        <?php foreach ($genres as $g): ?>
            <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="sort" id="sort">
        <option value="b.title">По названию</option>
        <option value="b.author">По автору</option>
        <option value="b.year DESC">Сначала новые</option>
    </select>
    <button type="submit" class="btn">Найти</button>
</form>

<p id="found" class="muted"></p>
<div id="books" class="grid"></div>
<div id="pager" class="pager"></div>
