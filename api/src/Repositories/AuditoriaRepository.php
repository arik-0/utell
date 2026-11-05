<?php
require_once __DIR__ . '/../Models/Auditoria.php';

class AuditoriaRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    /**
     * Registra un evento de trazabilidad en `log_auditoria` (RNF06).
     * Tolera tanto el esquema extendido como el esquema previo sin `tablaAfectada`.
     */
    public function registrar(
        ?int $idUsuario,
        string $accion,
        string $detalle = '',
        string $tablaAfectada = 'general',
        ?string $ip = null
    ): void {
        $ipAddr = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $uid = ($idUsuario && $idUsuario > 0) ? $idUsuario : null;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO log_auditoria (idUsuario, tablaAfectada, accion, detalle, fechaHora, ip)
                VALUES (:uid, :tabla, :accion, :detalle, NOW(), :ip)
            ");
            $stmt->execute([
                ':uid'     => $uid,
                ':tabla'   => $tablaAfectada,
                ':accion'  => $accion,
                ':detalle' => $detalle,
                ':ip'      => $ipAddr,
            ]);
        } catch (Exception $e) {
            try {
                $stmtFallback = $this->db->prepare("
                    INSERT INTO log_auditoria (idUsuario, accion, detalle, fechaHora, ip)
                    VALUES (:uid, :accion, :detalle, NOW(), :ip)
                ");
                $stmtFallback->execute([
                    ':uid'     => $uid,
                    ':accion'  => $accion,
                    ':detalle' => $detalle,
                    ':ip'      => $ipAddr,
                ]);
            } catch (Exception $inner) {
                error_log("AuditoriaRepository::registrar error: " . $inner->getMessage());
            }
        }
    }
}
