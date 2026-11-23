<?php
declare(strict_types=1);

/** @return array{id:int,nombre:string,correo:string}|null */
function current_user(): ?array
{
    $user = $_SESSION['usuario'] ?? null;
    if (!is_array($user) || !isset($user['id'], $user['nombre'], $user['correo'])
        || !is_int($user['id']) || $user['id'] < 1
        || !is_string($user['nombre']) || !is_string($user['correo'])) {
        return null;
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('login.php?acceso=1');
    }
    return $user;
}

function require_guest(): void
{
    if (current_user() !== null) {
        redirect('dashboard.php');
    }
}

/** @return array{id:int,nombre:string,correo:string}|null */
function authenticate(string $email, string $password): ?array
{
    $stmt = db()->prepare('SELECT id, nombre, correo, password FROM usuarios WHERE correo = :correo LIMIT 1');
    $stmt->execute(['correo' => $email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        return null;
    }
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $update = db()->prepare('UPDATE usuarios SET password = :hash WHERE id = :id');
        $update->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
    }
    return ['id' => (int) $user['id'], 'nombre' => $user['nombre'], 'correo' => $user['correo']];
}

function log_in(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = $user;
    $_SESSION['last_activity'] = time();
    unset($_SESSION['login_failures'], $_SESSION['login_blocked_until'], $_SESSION['expired']);
    // Regenerar token en el cambio de privilegios.
    unset($_SESSION['csrf_token']);
}

function log_out(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
    }
    session_destroy();
}
