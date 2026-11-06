<?php
require_once __DIR__ . '/../Models/Auditoria.php';

class AuditoriaRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    /**
     * Registra un evento de trazabilidad en `log_auditoria` (Punto T06 / RNF06).
     * Incluye soporte para estado original (`valorOriginal`) y transformaciones (`valorNuevo`).
     */
    public function registrar(
        ?int $idUsuario,
        string $accion,
        string $detalle = '',
        string $tablaAfectada = 'general',
        ?string $ip = null,
        ?string $valorOriginal = null,
        ?string $valorNuevo = null
    ): void {
        $ipAddr = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $uid = ($idUsuario && $idUsuario > 0) ? $idUsuario : null;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO log_auditoria (idUsuario, tablaAfectada, accion, valorOriginal, valorNuevo, detalle, fechaHora, ip)
                VALUES (:uid, :tabla, :accion, :valOrig, :valNuevo, :detalle, NOW(), :ip)
            ");
            $stmt->execute([
                ':uid'      => $uid,
                ':tabla'    => $tablaAfectada,
                ':accion'   => $accion,
                ':valOrig'  => $valorOriginal,
                ':valNuevo' => $valorNuevo,
                ':detalle'  => $detalle,
                ':ip'       => $ipAddr,
            ]);
        } catch (Exception $e) {
            // Fallback si la tabla en producción aún no tiene valorOriginal/valorNuevo
            $detalleEnriquecido = $detalle;
            if ($valorOriginal !== null || $valorNuevo !== null) {
                $detalleEnriquecido .= " | Antes: " . ($valorOriginal ?? 'N/A') . " -> Después: " . ($valorNuevo ?? 'N/A');
            }
            try {
                $stmtFallback = $this->db->prepare("
                    INSERT INTO log_auditoria (idUsuario, tablaAfectada, accion, detalle, fechaHora, ip)
                    VALUES (:uid, :tabla, :accion, :detalle, NOW(), :ip)
                ");
                $stmtFallback->execute([
                    ':uid'     => $uid,
                    ':tabla'   => $tablaAfectada,
                    ':accion'  => $accion,
                    ':detalle' => $detalleEnriquecido,
                    ':ip'      => $ipAddr,
                ]);
            } catch (Exception $inner) {
                try {
                    $stmtMin = $this->db->prepare("
                        INSERT INTO log_auditoria (idUsuario, accion, detalle, fechaHora, ip)
                        VALUES (:uid, :accion, :detalle, NOW(), :ip)
                    ");
                    $stmtMin->execute([
                        ':uid'     => $uid,
                        ':accion'  => $accion,
                        ':detalle' => $detalleEnriquecido,
                        ':ip'      => $ipAddr,
                    ]);
                } catch (Exception $final) {
                    error_log("AuditoriaRepository::registrar error: " . $final->getMessage());
                }
            }
        }
    }
}
