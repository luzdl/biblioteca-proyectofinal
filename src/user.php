<?php
// src/User.php

class User
{
    public string $cip;
    public string $primerNombre;
    public ?string $segundoNombre;
    public string $primerApellido;
    public ?string $segundoApellido;
    public string $fechaNacimiento;
    public int $carreraId;
    public string $usuario;
    public string $email;
    private string $passwordHash;

    public function __construct(array $data)
    {
        $this->cip             = trim($data['cip'] ?? '');
        $this->primerNombre    = trim($data['primer_nombre'] ?? '');
        $this->segundoNombre   = trim($data['segundo_nombre'] ?? '') ?: null;
        $this->primerApellido  = trim($data['primer_apellido'] ?? '');
        $this->segundoApellido = trim($data['segundo_apellido'] ?? '') ?: null;
        $this->fechaNacimiento = $data['fecha_nacimiento'] ?? '';
        $this->carreraId       = (int)($data['carrera_id'] ?? 0);
        $this->usuario         = trim($data['usuario'] ?? '');
        $this->email           = trim($data['email'] ?? '');
        $password              = $data['password'] ?? '';

        if ($password === '') {
            throw new InvalidArgumentException("La contraseña no puede estar vacía.");
        }

        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Guarda el usuario en la BD.
     * Devuelve TRUE si va bien o un string con mensaje de error si falla.
     */
    public function save(PDO $db)
    {
        // 1. Verificar correo y CIP únicos
        $checkSql = "SELECT id, email, cip 
                     FROM usuarios 
                     WHERE email = :email OR cip = :cip 
                     LIMIT 1";

        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            ':email' => $this->email,
            ':cip'   => $this->cip,
        ]);

        if ($existing = $checkStmt->fetch()) {
            if ($existing['email'] === $this->email) {
                return "Ya existe un usuario registrado con ese correo electrónico.";
            }
            if ($existing['cip'] === $this->cip) {
                return "Ya existe un usuario registrado con esa cédula / CIP.";
            }
            return "Ya existe un usuario con los datos proporcionados.";
        }

        // 2. Insertar usuario
        $sql = "INSERT INTO usuarios 
                (cip, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
                 fecha_nacimiento, carrera_id, usuario, email, password_hash, created_at)
                VALUES 
                (:cip, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido,
                 :fecha_nacimiento, :carrera_id, :usuario, :email, :password_hash, NOW())";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':cip', $this->cip);
        $stmt->bindValue(':primer_nombre', $this->primerNombre);
        $stmt->bindValue(':segundo_nombre', $this->segundoNombre);
        $stmt->bindValue(':primer_apellido', $this->primerApellido);
        $stmt->bindValue(':segundo_apellido', $this->segundoApellido);
        $stmt->bindValue(':fecha_nacimiento', $this->fechaNacimiento);
        $stmt->bindValue(':carrera_id', $this->carreraId);
        $stmt->bindValue(':usuario', $this->usuario);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':password_hash', $this->passwordHash);

        $stmt->execute();

        return true;
    }
}
