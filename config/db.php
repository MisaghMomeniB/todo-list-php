<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_list');
define('DB_USER', 'root');
define('DB_PASS', ''); # Sec
define('DB_CHARSET', 'utf8mb4');

function getDbConnection(): PDO
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $conn;
}
