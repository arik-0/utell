<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// POST /upload — subir imagen (soporta multipart/form-data y base64)
$app->post('/upload', function(Request $request, Response $response) {
    $uploadDir = __DIR__ . '/../../public/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $filename = null;
    $uploadedFiles = $request->getUploadedFiles();

    // 1. Multipart file upload
    $fileKey = null;
    foreach (['file', 'image', 'imagen', 'foto'] as $k) {
        if (!empty($uploadedFiles[$k])) {
            $fileKey = $k;
            break;
        }
    }

    if ($fileKey && $uploadedFiles[$fileKey]->getError() === UPLOAD_ERR_OK) {
        $file = $uploadedFiles[$fileKey];
        $clientFilename = $file->getClientFilename();
        $ext = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowedExts, true)) {
            $ext = 'jpg';
        }

        $filename = 'utell_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        $file->moveTo($targetPath);
    }

    // 2. Base64 payload in JSON or body
    if (!$filename) {
        $base64Data = $request->getParam('image') ?: $request->getParam('file') ?: $request->getParam('foto');
        if (!empty($base64Data) && is_string($base64Data)) {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $allowedExts = ['jpg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowedExts, true)) {
                    $ext = 'jpg';
                }
            } else {
                $ext = 'jpg';
            }

            $decoded = base64_decode($base64Data);
            if ($decoded !== false) {
                $filename = 'utell_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                file_put_contents($targetPath, $decoded);
            }
        }
    }

    if (!$filename) {
        return jsonResponse($response, ['error' => ['text' => 'No se recibió ninguna imagen válida.']], 400);
    }

    // Detect server host and url
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $relativeUrl = '/uploads/' . $filename;
    $fullUrl = $scheme . '://' . $host . $relativeUrl;

    // Optional: Auto-update user avatar if idUsuario is provided
    $idUsuario = (int)($request->getParam('idUsuario') ?? 0);
    $tipo = $request->getParam('tipo') ?? '';
    if ($idUsuario > 0 && ($tipo === 'avatar' || $tipo === 'perfil')) {
        try {
            $db = db::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET fotoPerfil = :foto WHERE idUsuario = :id");
            $stmt->execute([
                ':foto' => $relativeUrl,
                ':id'   => $idUsuario
            ]);
        } catch (\Exception $e) {
            // Silently continue if update fails
        }
    }

    return jsonResponse($response, [
        'success'  => true,
        'url'      => $relativeUrl,
        'fullUrl'  => $fullUrl,
        'filename' => $filename
    ], 201);
});
