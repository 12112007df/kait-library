-- Библиотека КАИТ: структура базы данных (MySQL 8 / MariaDB 10.6+)
-- Импорт: mysql -u root -p kait_library < database/schema.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('reader', 'librarian', 'admin') NOT NULL DEFAULT 'reader',
    category ENUM('student', 'teacher', 'staff') NOT NULL DEFAULT 'student',
    group_name VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    is_blocked TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre_id INT NULL,
    year SMALLINT NULL,
    isbn VARCHAR(20) NULL,
    description TEXT NULL,
    cover VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (genre_id) REFERENCES genres(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Экземпляр книги: у одной книги может быть несколько физических экземпляров
CREATE TABLE IF NOT EXISTS copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    inventory_number VARCHAR(30) NOT NULL UNIQUE,
    status ENUM('available', 'issued', 'lost', 'repair') NOT NULL DEFAULT 'available',
    FOREIGN KEY (book_id) REFERENCES books(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

