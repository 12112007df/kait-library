<?php

class ReviewRepository
{
    public static function forBook($bookId)
    {
        return Database::fetchAll(
            'SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.book_id = ? ORDER BY r.created_at DESC',
            [$bookId]
        );
    }

    public static function add($bookId, $userId, $rating, $text)
    {
        return Database::insert(
            'INSERT INTO reviews (book_id, user_id, rating, text, created_at) VALUES (?, ?, ?, ?, ?)',
            [$bookId, $userId, $rating, $text, date('Y-m-d H:i:s')]
        );
    }

    public static function delete($id)
    {
        return Database::execute('DELETE FROM reviews WHERE id = ?', [$id]);
    }
}
