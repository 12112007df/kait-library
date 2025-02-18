<?php

/**
 * Выдача и возврат книг.
 */
class LoanService
{
    /**
     * Выдать экземпляр книги читателю.
     * Возвращает ['ok' => bool, 'message' => string, 'due_at' => date, 'warnings' => []]
     */
    public static function issue($readerEmail, $inventoryNumber, $librarianId)
    {
        $warnings = [];

        $reader = Database::fetchOne('SELECT * FROM users WHERE email = ?', [$readerEmail]);
        if (!$reader) {
            return ['ok' => false, 'message' => 'Читатель не найден'];
        }
        if ($reader['is_blocked'] == 1) {
            return ['ok' => false, 'message' => 'Читатель заблокирован'];
        }

        $copy = Database::fetchOne('SELECT * FROM copies WHERE inventory_number = ?', [$inventoryNumber]);
        if (!$copy) {
            return ['ok' => false, 'message' => 'Экземпляр не найден'];
        }
        if ($copy['status'] != 'available') {
            if ($copy['status'] == 'issued') {
                return ['ok' => false, 'message' => 'Экземпляр уже выдан'];
            } elseif ($copy['status'] == 'repair') {
                return ['ok' => false, 'message' => 'Экземпляр на ремонте'];
            } else {
                return ['ok' => false, 'message' => 'Экземпляр списан или утерян'];
            }
        }

        // срок возврата
        if ($reader['category'] == 'student') {
            $days = LOAN_DAYS_STUDENT;
        } else {
            $days = LOAN_DAYS_TEACHER;
        }
        $due = strtotime('+' . $days . ' days');
        $dueAt = date('Y-m-d', $due);

        Database::insert(
            'INSERT INTO loans (copy_id, user_id, librarian_id, issued_at, due_at) VALUES (?, ?, ?, ?, ?)',
            [$copy['id'], $reader['id'], $librarianId, date('Y-m-d'), $dueAt]
        );
        Database::execute("UPDATE copies SET status = 'issued' WHERE id = ?", [$copy['id']]);

        return [
            'ok' => true,
            'message' => 'Книга выдана до ' . format_date($dueAt),
            'due_at' => $dueAt,
            'warnings' => $warnings,
        ];
    }
}
