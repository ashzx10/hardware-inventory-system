<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Manila');

/* Update these values if your local MySQL account is different. */
const DB_HOST = '127.0.0.1';
const DB_NAME = 'primebuild';
const DB_USER = 'root';
const DB_PASS = '';

/** Create and reuse the PDO connection to the configured MySQL database. */
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}
