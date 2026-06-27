<?php
class InstitucionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerUniversidades() {
        $sql = "SELECT * FROM universidades";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCarreras() {
        $sql = "SELECT * FROM carreras";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedes() {
        $sql = "SELECT * FROM sedes";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPaises() {
        $sql = "SELECT * FROM paises";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProvincias() {
        $sql = "SELECT * FROM provincias";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCiudades() {
        $sql = "SELECT * FROM ciudades";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
