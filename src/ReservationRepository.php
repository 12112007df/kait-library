<?php

class ReservationRepository
{
    public static function active()
    {
        return Database::fetchAll(
            "SELECT r.*, b.title, b.author, u.full_name, u.email, u.group_name
             FROM reservations r
             JOIN books b ON b.id = r.book_id
             JOIN users u ON u.id = r.user_id
             WHERE r.status = 'active'
             ORDER BY r.created_at"
        );
    }

    public static function forUser($userId)
    {
        return Database::fetchAll(
            "SELECT r.*, b.title, b.author
             FROM reservations r
             JOIN books b ON b.id = r.book_id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC",
            [$userId]
        );
    }

    public static function cancel($id)
    {
        return Database::execute("UPDATE reservations SET status = 'cancelled' WHERE id = ?", [$id]);
    }

    /**
     * Позиция читателя в очереди на книгу (1 — первый).
     */
    public static function queuePosition($bookId, $userId)
    {
        $rows = Database::fetchAll(
            "SELECT user_id FROM reservations WHERE book_id = ? AND status = 'active' ORDER BY created_at",
            [$bookId]
        );
        $pos = 1;
        foreach ($rows as $row) {
            if ($row['user_id'] == $userId) {
                return $pos;
            }
            $pos++;
        }
        return null;
    }
}
