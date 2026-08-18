<?php
class InstitucionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerUniversidades() {
        // Filter by estado='activa' — column added in Phase 2.3
        $sql = "SELECT * FROM universidades WHERE estado = 'activo' ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCarreras() {
        $sql = "SELECT * FROM carreras ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedes() {
        $sql = "SELECT * FROM sedes ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Carreras dictadas en una sede especifica (via oferta_academica)
    public function obtenerCarrerasPorSede(int $idSede): array {
        $sql = "SELECT c.idCarrera, c.nombre, oa.anioImplementacion
                FROM oferta_academica oa
                INNER JOIN carreras c ON oa.idCarrera = c.idCarrera
                WHERE oa.idSede = :idSede
                ORDER BY c.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idSede' => $idSede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sedes donde se dicta una carrera (via oferta_academica)
    public function obtenerSedesPorCarrera(int $idCarrera): array {
        $sql = "SELECT s.idSede, s.nombre, s.telefono, u.nombre as nombreUniversidad,
                       oa.anioImplementacion
                FROM oferta_academica oa
                INNER JOIN sedes s ON oa.idSede = s.idSede
                INNER JOIN universidades u ON s.idUniversidad = u.idUniversidad
                WHERE oa.idCarrera = :idCarrera
                ORDER BY s.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idCarrera' => $idCarrera]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sedes filtradas por universidad
    public function obtenerSedesPorUniversidad(int $idUniversidad): array {
        $sql = "SELECT * FROM sedes WHERE idUniversidad = :id ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUniversidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OfertaAcademica CRUD
    public function crearOfertaAcademica(int $idCarrera, int $idSede, ?int $anio): bool {
        $sql  = "INSERT INTO oferta_academica (idCarrera, idSede, anioImplementacion)
                 VALUES (:car, :sede, :anio)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':car' => $idCarrera, ':sede' => $idSede, ':anio' => $anio]);
    }

    public function eliminarOfertaAcademica(int $idOferta): bool {
        $stmt = $this->db->prepare("DELETE FROM oferta_academica WHERE idOfertaAcademica = :id");
        $stmt->execute([':id' => $idOferta]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPaises() {
        $sql = "SELECT * FROM paises ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProvincias() {
        $sql = "SELECT * FROM provincias ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCiudades() {
        $sql = "SELECT * FROM ciudades ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
