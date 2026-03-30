<?php

declare(strict_types=1);

/**
 * Retourne une connexion PDO MySQL.
 * Priorite aux variables d'environnement Docker:
 * DB_HOST, DB_PORT, DB_DATABASE, DB_USER, DB_PASSWORD.
 */
function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: 'iran_info_site';
    $user = getenv('DB_USER') ?: 'app_user';
    $password = getenv('DB_PASSWORD') ?: 'app_password';

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);

    return $pdo;
}
