<?php

class Input
{
    public static function hasPost(string $key): bool
    {
        return array_key_exists($key, $_POST);
    }

    public static function hasGet(string $key): bool
    {
        return array_key_exists($key, $_GET);
    }

    public static function postString(string $key, string $default = ''): string
    {
        $val = $_POST[$key] ?? null;
        if (!is_string($val)) {
            return $default;
        }
        return trim($val);
    }

    public static function postRawString(string $key, string $default = ''): string
    {
        $val = $_POST[$key] ?? null;
        if (!is_string($val)) {
            return $default;
        }
        return $val;
    }

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

    public static function getString(string $key, string $default = ''): string
    {
        $val = $_GET[$key] ?? null;
        if (!is_string($val)) {
            return $default;
        }
        return trim($val);
    }

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

    public static function file(string $key): ?array
    {
        $f = $_FILES[$key] ?? null;
        return is_array($f) ? $f : null;
    }
}

class Validator
{
    private array $errors = [];

    private function strlenSafe(string $value): int
    {
        return function_exists('mb_strlen') ? (int)mb_strlen($value) : (int)strlen($value);
    }

    public function add(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

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

    public function email(string $field, string $value, string $message): self
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function minInt(string $field, int $value, int $min, string $message): self
    {
        if ($value < $min) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function minLen(string $field, string $value, int $min, string $message): self
    {
        if ($this->strlenSafe($value) < $min) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function maxLen(string $field, string $value, int $max, string $message): self
    {
        if ($this->strlenSafe($value) > $max) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function ok(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        if (empty($this->errors)) {
            return '';
        }
        $first = reset($this->errors);
        return is_string($first) ? $first : '';
    }
}
