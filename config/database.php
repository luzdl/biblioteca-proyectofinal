<?php

class Database
{
    private string $host = 'localhost';
    private string $db_name = 'biblioteca_digital'; // <-- pon aquí el nombre REAL de tu BD
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conn = null;

    public function getConnection(): PDO
    {
        // Si ya hay conexión, la reutilizamos
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
            // MIENTRAS ESTAMOS PROBANDO: muestra el error real
            die("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}
