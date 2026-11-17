<?php
require_once __DIR__ . '/Publicacion.php';

class Consulta extends Publicacion {
    private bool $estadoResuelta;
    /** @var Respuesta[] */
    private array $respuestas = [];

    public function __construct(
        ?Usuario $redactor,
        string $texto,
        ?Universidad $universidad = null,
        bool $estadoResuelta = false,
        ?Sede $sede = null,
        ?Carrera $carrera = null,
        string $titulo = '',
        ?int $idPublicacion = null,
        ?string $fechaCreacion = null,
        ?string $horaCreacion = null,
        int $cantidadLikes = 0,
        string $estado = 'activo'
    ) {
        parent::__construct(
            $redactor,
            $texto,
            $universidad,
            $sede,
            $carrera,
            $titulo,
            $idPublicacion,
            $fechaCreacion,
            $horaCreacion,
            $cantidadLikes,
            $estado
        );
        $this->estadoResuelta = $estadoResuelta;
    }

    public function getTipoPublicacion(): string {
        return 'Consulta';
    }

    public function marcarComoResuelta(): void {
        $this->estadoResuelta = true;
    }

    public function getEstadoResuelta(): bool {
        return $this->estadoResuelta;
    }

    /** @return Respuesta[] */
    public function getRespuestas(): array { return $this->respuestas; }
    public function agregarRespuesta(Respuesta $respuesta): void {
        $this->respuestas[] = $respuesta;
        $this->agregarComentario($respuesta);
    }
}