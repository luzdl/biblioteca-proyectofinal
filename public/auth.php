<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/router.php';

if (!isset($_SESSION['usuario_id'])) {
    redirect('login');
}
