<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . '/fotos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$response = ['success' => false, 'message' => ''];

if (isset($_FILES['fotos'])) {
    $errors = [];
    $uploaded = [];
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    foreach ($_FILES['fotos']['name'] as $i => $name) {
        $tmpName = $_FILES['fotos']['tmp_name'][$i];
        $error = $_FILES['fotos']['error'][$i];

        if ($error === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $errors[] = "$name: formato no permitido";
                continue;
            }

            // generar nombre único si ya existe
            $base = pathinfo($name, PATHINFO_FILENAME);
            $target = $uploadDir . $name;
            $counter = 1;
            while (file_exists($target)) {
                $target = $uploadDir . $base . '_' . $counter . '.' . $ext;
                $counter++;
            }

            if (move_uploaded_file($tmpName, $target)) {
                $uploaded[] = basename($target);
            } else {
                $errors[] = "$name: fallo al mover archivo";
            }
        } else {
            $errors[] = "$name: error c\u00f3digo $error";
        }
    }

    if ($uploaded) {
        $response['success'] = true;
        $response['message'] = 'Subidas: ' . implode(', ', $uploaded);
    }
    if ($errors) {
        $response['message'] .= ($response['message'] ? ' | ' : '') . 'Errores: ' . implode('; ', $errors);
    }
} else {
    $response['message'] = 'No se recibieron archivos';
}

echo json_encode($response);
