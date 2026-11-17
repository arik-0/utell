<?php
// api/src/Factories/PublicacionFactory.php

require_once __DIR__ . '/../Models/Experiencia.php';
require_once __DIR__ . '/../Models/Consulta.php';

class PublicacionFactory {
    /**
     * Patrón Factory Method: instancia la entidad de dominio polimórfica
     * correspondiente (Experiencia o Consulta) validando sus reglas de negocio.
     * @throws InvalidArgumentException Si los datos requeridos no cumplen con las reglas de dominio
     */
    public static function crearEntidad(
        array $datos,
        ?Usuario $autor = null,
        ?Universidad $universidad = null,
        ?Sede $sede = null,
        ?Carrera $carrera = null
    ): Publicacion {
        $tipo      = trim($datos['tipoPublicacion'] ?? 'Consulta');
        $titulo    = trim($datos['titulo'] ?? '');
        $texto     = trim($datos['texto'] ?? '');
        $idUsuario = (int)($datos['idUsuario'] ?? 0);

        if ($idUsuario <= 0) {
            throw new InvalidArgumentException("El ID de usuario es obligatorio.");
        }

        if (empty($texto) && empty($titulo)) {
            throw new InvalidArgumentException("La publicación debe contener un título o texto explicativo.");
        }

        $fecha = $datos['fecha'] ?? date('Y-m-d');
        $hora  = $datos['hora'] ?? date('H:i:s');

        if ($tipo === 'Experiencia') {
            return new Experiencia(
                $autor,
                $texto,
                $universidad,
                floatval($datos['puntuacionClima'] ?? 5.0),
                floatval($datos['puntuacionInfraestructura'] ?? 5.0),
                floatval($datos['puntuacionAcademica'] ?? 5.0),
                $sede,
                $carrera,
                $titulo,
                null,
                $fecha,
                $hora
            );
        }

        return new Consulta(
            $autor,
            $texto,
            $universidad,
            (bool)($datos['resuelta'] ?? false),
            $sede,
            $carrera,
            $titulo,
            null,
            $fecha,
            $hora
        );
    }

    /**
     * Valida y normaliza los datos de una publicación delegando la instanciación
     * polimórfica en crearEntidad() y retornando el payload listo para persistencia.
     * @param array $datos Datos provenientes del request
     * @return array Datos validados y listos para persistencia
     * @throws InvalidArgumentException Si los datos requeridos no cumplen con las reglas de negocio
     */
    public static function crear(array $datos): array {
        $entidad   = self::crearEntidad($datos);
        $idUsuario = (int)$datos['idUsuario'];

        $normalizado = [
            'idUsuario'       => $idUsuario,
            'tipoPublicacion' => $entidad->getTipoPublicacion(),
            'titulo'          => $entidad->getTitulo(),
            'texto'           => $entidad->getTexto(),
            'fecha'           => $entidad->getFechaCreacion(),
            'hora'            => $entidad->getHoraCreacion(),
            'idUniversidad'   => !empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null,
            'idSede'          => !empty($datos['idSede']) ? (int)$datos['idSede'] : null,
            'idCarrera'       => !empty($datos['idCarrera']) ? (int)$datos['idCarrera'] : null,
            'likes'           => $entidad->getCantidadLikes(),
        ];

        if ($entidad instanceof Experiencia) {
            $normalizado['puntuacionClima']           = $entidad->getPuntuacionClima();
            $normalizado['puntuacionInfraestructura'] = $entidad->getPuntuacionInfraestructura();
            $normalizado['puntuacionAcademica']       = $entidad->getPuntuacionAcademica();
        } elseif ($entidad instanceof Consulta) {
            $normalizado['resuelta'] = $entidad->getEstadoResuelta();
        }

        return $normalizado;
    }
}