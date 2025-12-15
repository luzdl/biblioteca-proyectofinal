<?php

/**
 * Centralized request input helpers.
 *
 * Note:
 * - These helpers normalize types and trimming for server-side validation.
 * - They do NOT perform HTML escaping. Continue using htmlspecialchars() at render time.
 * - They are intentionally conservative (fallback to defaults on invalid types).
 */
class Input
{
    /** Returns true if a POST key exists (even if empty). */
    public static function hasPost(string $key): bool
    {
        return array_key_exists($key, $_POST);
    }

    /** Returns true if a GET key exists (even if empty). */
    public static function hasGet(string $key): bool
    {
        return array_key_exists($key, $_GET);
    }

    /**
     * Read a POST value as a trimmed string.
     * Non-string inputs (arrays, objects) fall back to $default.
     */
    public static function postString(string $key, string $default = ''): string
    {
        $val = $_POST[$key] ?? null;
        if (!is_string($val)) {
            return $default;
        }
        return trim($val);
    }

    /**
     * Read a POST value as raw string (no trim).
     * Useful for passwords or tokens where leading/trailing spaces may matter.
     */
    public static function postRawString(string $key, string $default = ''): string
    {
        $val = $_POST[$key] ?? null;
        if (!is_string($val)) {
            return $default;
        }
        return $val;
    }

    /**
     * Read a POST value as integer.
     * Accepts numeric strings like "42" or "-1"; otherwise returns $default.
     */
    public static function postInt(string $key, int $default = 0): int
    {
        $val = $_POST[$key] ?? null;
        if (is_int($val)) {
            return $val;
        }
        if (is_string($val)) {
            $val = trim($val);
            if ($val === '') {
                return $default;
            }
            if (preg_match('/^-?\d+$/', $val) === 1) {
                return (int)$val;
            }
        }
        return $default;
    }

    /** Read a GET value as a trimmed string. */
    public static function getString(string $key, string $default = ''): string
    {
        $val = $_GET[$key] ?? null;
        if (!is_string($val)) {
            return $default;
        }
        return trim($val);
    }

    /** Read a GET value as integer. */
    public static function getInt(string $key, int $default = 0): int
    {
        $val = $_GET[$key] ?? null;
        if (is_int($val)) {
            return $val;
        }
        if (is_string($val)) {
            $val = trim($val);
            if ($val === '') {
                return $default;
            }
            if (preg_match('/^-?\d+$/', $val) === 1) {
                return (int)$val;
            }
        }
        return $default;
    }

    /**
     * Read a $_FILES entry.
     * Returns null if the key is missing or not an array.
     */
    public static function file(string $key): ?array
    {
        $f = $_FILES[$key] ?? null;
        return is_array($f) ? $f : null;
    }
}

/**
 * Minimal rule-based validator.
 *
 * Stores errors keyed by field name so forms can show per-field messages.
 * Designed for CRUD usage: validate inputs, then proceed with PDO prepared statements.
 */
class Validator
{
    private array $errors = [];

    /** Prefer mb_strlen if available; fall back to strlen to avoid hard dependency on mbstring. */
    private function strlenSafe(string $value): int
    {
        return function_exists('mb_strlen') ? (int)mb_strlen($value) : (int)strlen($value);
    }

    /** Add an error for a field (first error wins). */
    public function add(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    /** Ensure a value is present (non-empty string / non-null). */
    public function required(string $field, $value, string $message): self
    {
        $ok = true;
        if ($value === null) {
            $ok = false;
        } elseif (is_string($value) && trim($value) === '') {
            $ok = false;
        } elseif (is_int($value) && $value === 0) {
            $ok = false;
        }

        if (!$ok) {
            $this->add($field, $message);
        }

        return $this;
    }

    /** Validate email format (only if non-empty). */
    public function email(string $field, string $value, string $message): self
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, $message);
        }
        return $this;
    }

    /** Ensure an integer is >= $min. */
    public function minInt(string $field, int $value, int $min, string $message): self
    {
        if ($value < $min) {
            $this->add($field, $message);
        }
        return $this;
    }

    /** Ensure a string length is >= $min. */
    public function minLen(string $field, string $value, int $min, string $message): self
    {
        if ($this->strlenSafe($value) < $min) {
            $this->add($field, $message);
        }
        return $this;
    }

    /** Ensure a string length is <= $max. */
    public function maxLen(string $field, string $value, int $max, string $message): self
    {
        if ($this->strlenSafe($value) > $max) {
            $this->add($field, $message);
        }
        return $this;
    }

    /** True when no errors were recorded. */
    public function ok(): bool
    {
        return empty($this->errors);
    }

    /** Get all errors keyed by field name. */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Convenience: get the first error message (or empty string). */
    public function firstError(): string
    {
        if (empty($this->errors)) {
            return '';
        }
        $first = reset($this->errors);
        return is_string($first) ? $first : '';
    }
}
