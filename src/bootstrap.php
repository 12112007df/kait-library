<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/BookRepository.php';
require_once __DIR__ . '/LoanService.php';
require_once __DIR__ . '/UserRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
