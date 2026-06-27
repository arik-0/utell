<?php
class Comentario {
    private int $idPublicacion;
    private int $idUsuario;
    private string $texto;

    public function __construct(int $idPub, int $idUser, string $txt) {
        $this->idPublicacion = $idPub;
        $this->idUsuario = $idUser;
        $this->texto = $txt;
    }
}