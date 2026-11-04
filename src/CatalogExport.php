<?php

class CatalogExport
{
    private const MAX_ROWS = 10000;
    private const SORTS = [
        'b.title' => 'b.title ASC, b.id ASC',
        'b.author' => 'b.author ASC, b.id ASC',
        'b.year DESC' => 'b.year DESC, b.id ASC',
    ];

    public static function filters(array $input): array
    {
        $query = self::stringValue($input, 'q');
        if (!mb_check_encoding($query, 'UTF-8')) {
            throw new InvalidArgumentException('Поисковая строка должна быть в UTF-8.');
        }
        if (mb_strlen($query, 'UTF-8') > 100) {
            throw new InvalidArgumentException('Поисковая строка длиннее 100 символов.');
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $query)) {
            throw new InvalidArgumentException('Поисковая строка содержит управляющие символы.');
        }
        $genre = self::stringValue($input, 'genre');
        if ($genre !== '' && (!ctype_digit($genre) || strlen($genre) > 9 || (int)$genre < 1)) {
            throw new InvalidArgumentException('Некорректный номер жанра.');
        }
        $sort = self::stringValue($input, 'sort', 'b.title');
        if (!array_key_exists($sort, self::SORTS)) {
            throw new InvalidArgumentException('Некорректный порядок сортировки.');
        }
        return ['q' => trim($query), 'genre' => $genre, 'sort' => $sort];
    }

    private static function stringValue(array $input, string $key, string $default = ''): string
    {
        $value = $input[$key] ?? $default;
        if (!is_string($value)) {
            throw new InvalidArgumentException('Параметр ' . $key . ' должен быть строкой.');
        }
        return $value;
    }

    public static function rows(array $input): array
    {
        $filters = self::filters($input);
        $conditions = [];
        $params = [];
        if ($filters['q'] !== '') {
            $pattern = '%' . strtr($filters['q'], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $conditions[] = "(b.title LIKE ? ESCAPE '!' OR b.author LIKE ? ESCAPE '!')";
            $params[] = $pattern;
            $params[] = $pattern;
        }
        if ($filters['genre'] !== '') {
            $conditions[] = 'b.genre_id = ?';
            $params[] = (int)$filters['genre'];
        }
        $sql = "SELECT b.id, b.title, b.author, g.name AS genre, b.year, b.isbn,
                COUNT(c.id) AS total_copies,
                SUM(CASE WHEN c.status = 'available' THEN 1 ELSE 0 END) AS available,
                SUM(CASE WHEN c.status = 'issued' THEN 1 ELSE 0 END) AS issued,
                SUM(CASE WHEN c.status = 'repair' THEN 1 ELSE 0 END) AS repair,
                SUM(CASE WHEN c.status = 'lost' THEN 1 ELSE 0 END) AS lost
                FROM books b
                LEFT JOIN genres g ON g.id = b.genre_id
                LEFT JOIN copies c ON c.book_id = b.id";
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' GROUP BY b.id, b.title, b.author, g.name, b.year, b.isbn';
        $sql .= ' ORDER BY ' . self::SORTS[$filters['sort']];
        $sql .= ' LIMIT ' . (self::MAX_ROWS + 1);
        $rows = Database::fetchAll($sql, $params);
        if (count($rows) > self::MAX_ROWS) {
            throw new InvalidArgumentException('Слишком много книг для выгрузки. Уточните фильтры.');
        }
        return $rows;
    }

    private static function textCell($value): string
    {
        $value = str_replace("\0", '', (string)($value ?? ''));
        if (preg_match('/^[\s]*[=+\-@]/u', $value)) {
            return "'" . $value;
        }
        return $value;
    }

    private static function writeRow($stream, array $row): void
    {
        if (fputcsv($stream, $row, ';', '"', '') === false) {
            throw new RuntimeException('Не удалось сформировать строку CSV.');
        }
    }

    public static function csv(array $input): string
    {
        $rows = self::rows($input);
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new RuntimeException('Не удалось подготовить CSV.');
        }
        try {
            if (fwrite($stream, "\xEF\xBB\xBF") === false) {
                throw new RuntimeException('Не удалось записать заголовок файла.');
            }
            self::writeRow($stream, [
                'ID', 'Название', 'Автор', 'Жанр', 'Год', 'ISBN',
                'Всего экземпляров', 'Доступно', 'Выдано', 'В ремонте', 'Утеряно',
            ]);
            foreach ($rows as $book) {
                self::writeRow($stream, [
                    (int)$book['id'],
                    self::textCell($book['title']),
                    self::textCell($book['author']),
                    self::textCell($book['genre']),
                    $book['year'] === null ? '' : (int)$book['year'],
                    self::textCell($book['isbn']),
                    (int)$book['total_copies'],
                    (int)$book['available'],
                    (int)$book['issued'],
                    (int)$book['repair'],
                    (int)$book['lost'],
                ]);
            }
            rewind($stream);
            $content = stream_get_contents($stream);
            if ($content === false) {
                throw new RuntimeException('Не удалось прочитать CSV.');
            }
            return $content;
        } finally {
            fclose($stream);
        }
    }

    public static function download(array $input): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Allow: GET');
            json_error('Для выгрузки используется GET.', 405);
        }
        if (!Auth::check()) {
            json_error('Сначала войдите в систему.', 401);
        }
        if (!Auth::hasRole(['librarian', 'admin'])) {
            json_error('Выгрузка доступна библиотекарю и администратору.', 403);
        }
        try {
            $csv = self::csv($input);
        } catch (InvalidArgumentException $error) {
            json_error($error->getMessage(), 422);
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="catalog.csv"');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        echo $csv;
        exit;
    }
}
