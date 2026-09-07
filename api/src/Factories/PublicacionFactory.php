<?php
// api/src/Factories/PublicacionFactory.php

class PublicacionFactory {
    /**
     * Valida y normaliza los datos de una publicación según su tipo (Experiencia o Consulta).
     * @param array $datos Datos provenientes del request
     * @return array Datos validados y listos para persistencia
     * @throws InvalidArgumentException Si los datos requeridos no cumplen con las reglas de negocio
     */
    public static function crear(array $datos): array {
        $tipo = trim($datos['tipoPublicacion'] ?? 'Consulta');
        $titulo = trim($datos['titulo'] ?? '');
        $texto = trim($datos['texto'] ?? '');
        $idUsuario = (int)($datos['idUsuario'] ?? 0);

        if ($idUsuario <= 0) {
            throw new InvalidArgumentException("El ID de usuario es obligatorio.");
        }

        if (empty($texto) && empty($titulo)) {
            throw new InvalidArgumentException("La publicación debe contener un título o texto explicativo.");
        }

        $normalizado = [
            'idUsuario' => $idUsuario,
            'tipoPublicacion' => $tipo,
            'titulo' => $titulo,
            'texto' => $texto,
            'fecha' => $datos['fecha'] ?? date('Y-m-d'),
            'hora' => $datos['hora'] ?? date('H:i:s'),
            'idUniversidad' => !empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null,
            'idSede' => !empty($datos['idSede']) ? (int)$datos['idSede'] : null,
            'idCarrera' => !empty($datos['idCarrera']) ? (int)$datos['idCarrera'] : null,
            'likes' => 0
        ];

        if ($tipo === 'Experiencia') {
            $normalizado['puntuacionClima'] = min(5.0, max(1.0, floatval($datos['puntuacionClima'] ?? 5.0)));
            $normalizado['puntuacionInfraestructura'] = min(5.0, max(1.0, floatval($datos['puntuacionInfraestructura'] ?? 5.0)));
            $normalizado['puntuacionAcademica'] = min(5.0, max(1.0, floatval($datos['puntuacionAcademica'] ?? 5.0)));
        } else {
            $normalizado['tipoPublicacion'] = 'Consulta';
            $normalizado['resuelta'] = false;
        }

        return $normalizado;
    }
}