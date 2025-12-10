<?php
// admin_only.php

// Primero, verificamos que haya sesión (reutilizamos auth.php)
require_once __DIR__ . '/auth.php';

$rol = $_SESSION['usuario_rol'] ?? '';

if ($rol !== 'administrador') {
    // Sin permiso: puedes redirigir o mostrar mensaje
    header('HTTP/1.1 403 Forbidden');
    echo "No tienes permiso para acceder a este módulo.";
    exit;
}
