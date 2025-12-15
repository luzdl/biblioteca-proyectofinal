<?php
/**
 * Application bootstrap.
 *
 * Responsibilities:
 * - Ensure session is started.
 * - Load config (env/router/database).
 * - Load shared helpers (Input/Validator + auth helpers).
 *
 * All application pages should include this file instead of manually requiring
 * individual config/helpers to keep behavior consistent.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/router.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/input.php';
require_once __DIR__ . '/auth_helpers.php';
