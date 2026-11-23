<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $path = dirname(__DIR__) . '/config/config.php';
    if (!is_file($path)) {
        abort_request(503, 'Falta configurar la conexión a la base de datos. Consulta el archivo README.md.');
    }
    $config = require $path;
    if (!is_array($config)) {
        abort_request(503, 'La configuración de la base de datos no es válida.');
    }
    foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
        if (!array_key_exists($key, $config)) {
            abort_request(503, 'La configuración de la base de datos está incompleta.');
        }
    }
    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'], (int) $config['port'], $config['database']);
        $pdo = new PDO($dsn, (string) $config['username'], (string) $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('Error de conexión a la base de datos: ' . $exception->getMessage());
        abort_request(503, 'No es posible conectar con la base de datos en este momento.');
    }
    return $pdo;
}
