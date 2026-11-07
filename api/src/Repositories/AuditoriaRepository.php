<?php
require_once __DIR__ . '/../Models/Auditoria.php';

class AuditoriaRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    private function asegurarEsquemaAuditoria(): void {
        $alters = [
            "ALTER TABLE log_auditoria MODIFY COLUMN idUsuario INT(11) DEFAULT NULL",
            "ALTER TABLE log_auditoria MODIFY COLUMN tablaAfectada VARCHAR(60) DEFAULT 'general'",
            "ALTER TABLE log_auditoria MODIFY COLUMN accion VARCHAR(80) NOT NULL",
            "ALTER TABLE log_auditoria ADD COLUMN valorNuevo TEXT DEFAULT NULL",
            "ALTER TABLE log_auditoria ADD COLUMN detalle TEXT DEFAULT NULL",
            "ALTER TABLE log_auditoria ADD COLUMN ip VARCHAR(45) DEFAULT NULL",
        ];
        foreach ($alters as $sql) {
            try {
                $this->db->exec($sql);
            } catch (Exception $e) {}
        }
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

        $sqlMain = "INSERT INTO log_auditoria (idUsuario, tablaAfectada, accion, valorOriginal, valorNuevo, detalle, fechaHora, ip)
                    VALUES (:uid, :tabla, :accion, :valOrig, :valNuevo, :detalle, NOW(), :ip)";
        $paramsMain = [
            ':uid'      => $uid,
            ':tabla'    => $tablaAfectada,
            ':accion'   => $accion,
            ':valOrig'  => $valorOriginal,
            ':valNuevo' => $valorNuevo,
            ':detalle'  => $detalle,
            ':ip'       => $ipAddr,
        ];

        try {
            $stmt = $this->db->prepare($sqlMain);
            $stmt->execute($paramsMain);
        } catch (Exception $e) {
            $this->asegurarEsquemaAuditoria();
            try {
                $stmt = $this->db->prepare($sqlMain);
                $stmt->execute($paramsMain);
            } catch (Exception $inner) {
                $valConsolidated = $valorOriginal ?? $detalle;
                if ($valorNuevo !== null) {
                    $valConsolidated = "Antes: " . ($valorOriginal ?? 'N/A') . " | Después: " . $valorNuevo . " | " . $detalle;
                }
                try {
                    $stmtLegacy = $this->db->prepare("
                        INSERT INTO log_auditoria (idUsuario, tablaAfectada, accion, valorOriginal, fechaHora)
                        VALUES (:uid, :tabla, :accion, :valOrig, NOW())
                    ");
                    $stmtLegacy->execute([
                        ':uid'     => $uid ?? 99,
                        ':tabla'   => substr($tablaAfectada, 0, 50),
                        ':accion'  => substr($accion, 0, 20),
                        ':valOrig' => $valConsolidated,
                    ]);
                } catch (Exception $final) {
                    error_log("AuditoriaRepository::registrar error: " . $final->getMessage());
                }
            }
        }
    }
}
