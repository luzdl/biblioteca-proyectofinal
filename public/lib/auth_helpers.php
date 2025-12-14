<?php

function require_login(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        redirect('login');
    }
}

function current_role(): string
{
    return (string)($_SESSION['usuario_rol'] ?? '');
}

function require_role(array $roles): void
{
    require_login();

    $rol = current_role();
    if ($rol === '' || !in_array($rol, $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo '⛔ No tienes permiso para acceder a esta sección.';
        exit;
    }
}

function role_home_route(string $rol): string
{
    switch ($rol) {
        case 'administrador':
            return 'admin';
        case 'bibliotecario':
            return 'staff';
        default:
            return 'student';
    }
}

function redirect_to_role_home(): void
{
    require_login();
    $rol = current_role();
    redirect(role_home_route($rol));
}

function fetch_current_user(PDO $db): ?array
{
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }

    try {
        $stmt = $db->prepare('SELECT id, usuario, email, rol, profile_upload_id FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)$_SESSION['usuario_id']]);
        $u = $stmt->fetch();
    } catch (Exception $e) {
        $stmt = $db->prepare('SELECT id, usuario, email, rol FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)$_SESSION['usuario_id']]);
        $u = $stmt->fetch();
    }

    return $u ?: null;
}

function fetch_profile_upload(PDO $db, int $uploadId): ?array
{
    try {
        $stmt = $db->prepare('SELECT id, relative_path, mime_type, size_bytes FROM uploads WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $uploadId]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Exception $e) {
        return null;
    }
}

function profile_image_url(?array $user): string
{
    if (!$user) {
        return function_exists('url_for') ? url_for('img/user_placeholder.png') : '../img/user_placeholder.png';
    }

    $path = $user['relative_path'] ?? null;
    if (is_string($path) && $path !== '') {
        if (stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        if (function_exists('url_for')) {
            return url_for($normalized);
        }

        return $path;
    }

    return function_exists('url_for') ? url_for('img/user_placeholder.png') : '../img/user_placeholder.png';
}
