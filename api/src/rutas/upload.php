<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/UsuarioService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// POST /upload — subir imagen autenticada (soporta multipart/form-data y base64, máx 5 MB)
$app->post('/upload', function(Request $request, Response $response) {
    $maxBytes = 5 * 1024 * 1024; // 5 MB
    $mimeMap  = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $uploadDir = __DIR__ . '/../../public/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $rawBytes = null;
    $uploadedFiles = $request->getUploadedFiles();

    // 1. Multipart file upload
    $fileKey = null;
    foreach (['file', 'image', 'imagen', 'foto'] as $k) {
        if (!empty($uploadedFiles[$k])) {
            $fileKey = $k;
            break;
        }
    }

    if ($fileKey) {
        $file = $uploadedFiles[$fileKey];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return errorResponse($response, 'Error al recibir el archivo adjunto.', 400);
        }
        if ($file->getSize() > $maxBytes) {
            return errorResponse($response, 'La imagen supera el tamaño máximo permitido (5 MB).', 413);
        }
        $stream = $file->getStream();
        $stream->rewind();
        $rawBytes = $stream->getContents();
    }

    // 2. Base64 payload in JSON or body
    if ($rawBytes === null) {
        $base64Data = $request->getParam('image') ?: $request->getParam('file') ?: $request->getParam('foto');
        if (!empty($base64Data) && is_string($base64Data)) {
            if (strpos($base64Data, ',') !== false) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }
            $decoded = base64_decode($base64Data, true);
            if ($decoded === false) {
                return errorResponse($response, 'Codificación de imagen Base64 inválida.', 400);
            }
            if (strlen($decoded) > $maxBytes) {
                return errorResponse($response, 'La imagen supera el tamaño máximo permitido (5 MB).', 413);
            }
            $rawBytes = $decoded;
        }
    }

    if ($rawBytes === null || $rawBytes === '') {
        return errorResponse($response, 'No se recibió ninguna imagen válida.', 400);
    }

    // Validar MIME real de los bytes recibidos (evita subida de scripts renombrados)
    $detectedMime = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detectedMime = finfo_buffer($finfo, $rawBytes) ?: null;
            finfo_close($finfo);
        }
    }
    if (!$detectedMime && function_exists('getimagesizefromstring')) {
        $imgInfo = @getimagesizefromstring($rawBytes);
        $detectedMime = $imgInfo['mime'] ?? null;
    }

    if (!$detectedMime || !isset($mimeMap[$detectedMime])) {
        return errorResponse($response, 'Formato de archivo no permitido. Solo se aceptan JPG, PNG, WEBP o GIF.', 415);
    }

    $ext        = $mimeMap[$detectedMime];
    $filename   = 'utell_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (@file_put_contents($targetPath, $rawBytes) === false) {
        return errorResponse($response, 'No se pudo almacenar la imagen en el servidor.', 500);
    }

    $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $relativeUrl = '/uploads/' . $filename;
    $fullUrl     = $scheme . '://' . $host . $relativeUrl;

    // Actualizar fotoPerfil solo del usuario autenticado (o administrador autorizado)
    $authId  = getAuthUserId($request);
    $idParam = (int)($request->getParam('idUsuario') ?? 0);
    $tipo    = (string)($request->getParam('tipo') ?? '');

    if ($authId > 0 && ($tipo === 'avatar' || $tipo === 'perfil')) {
        $targetUserId = ($idParam > 0 && isAuthAdminOrMod($request)) ? $idParam : $authId;
        try {
            UsuarioService::getInstance()->actualizarFotoPerfil($targetUserId, $relativeUrl);
        } catch (Exception $e) {
            error_log("Upload avatar sync error: " . $e->getMessage());
        }
    }

    return jsonResponse($response, [
        'success'  => true,
        'url'      => $relativeUrl,
        'fullUrl'  => $fullUrl,
        'filename' => $filename
    ], 201);
})->add(new AuthMiddleware());
