<?php
class JWTService {
    private static $secret = null;

    private static function getSecret(): string {
        if (self::$secret === null) {
            $secret = getenv('JWT_SECRET');
            if ($secret === false || $secret === '') {
                // WARNING: In production, JWT_SECRET MUST be set as an environment variable
                // This fallback generates a persistent secret for development only
                $secretFile = sys_get_temp_dir() . '/utell_jwt_secret.key';
                if (file_exists($secretFile)) {
                    $secret = file_get_contents($secretFile);
                } else {
                    $secret = bin2hex(random_bytes(32));
                    @file_put_contents($secretFile, $secret);
                }
            }
            self::$secret = $secret;
        }
        return self::$secret;
    }

    /**
     * Genera un token JWT firmado para un usuario.
     * @param array $payload Datos a codificar (idUsuario, email, tipoPerfil, rol)
     * @param int $exp Duración en segundos (por defecto 2 horas)
     * @return string
     */
    public static function generarToken(array $payload, int $exp = 7200): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['iat'] = time();
        $payload['exp'] = time() + $exp;
        $payloadJson = json_encode($payload);

        $base64UrlHeader  = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Valida y decodifica un token JWT.
     * @param string $token
     * @return array|null Retorna el payload decodificado o null si es inválido o expiró
     */
    public static function validarToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
