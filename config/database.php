<?php

class Database
{
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conn = null;

    public function __construct()
    {
        // Cargar variables de entorno obligatorias
        $this->host = getenv('DB_HOST');
        $this->db_name = getenv('DB_NAME');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASS');

        if (!$this->host || !$this->db_name || !$this->username || $this->password === false) {
            throw new Exception('Faltan variables de entorno requeridas para la conexión a la base de datos.');
        }
    }

    public function getConnection(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}
