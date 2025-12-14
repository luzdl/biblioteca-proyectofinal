<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/router.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/auth_helpers.php';
