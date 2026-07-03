<?php

class Credencial {
    private string $email;
    private string $passwordHash;
    private string $estadoCuenta;

    public function __construct(string $email, string $passwordHash, string $estadoCuenta = 'activo') {
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->estadoCuenta = $estadoCuenta;
    }

    public function validarAcceso(string $pass): bool {
        return password_verify($pass, $this->passwordHash);
    }

    public function bloquear(): void {
        $this->estadoCuenta = 'bloqueado';
    }

    // Getters
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getEstadoCuenta(): string { return $this->estadoCuenta; }

    // Setters
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPasswordHash(string $passwordHash): void { $this->passwordHash = $passwordHash; }
}
