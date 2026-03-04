<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . '/fotos/';

// si se proporciona usuario, crear subcarpeta
$username = isset($_POST['username']) ? preg_replace('/[^a-zA-Z0-9_-]/','',$_POST['username']) : '';
if ($username) {
    $uploadDir .= $username . '/';
}

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

        // si es para un usuario, actualizar su registro
        if ($username) {
            $jsonFile = __DIR__ . '/registro.json';
            if (file_exists($jsonFile)) {
                $content = file_get_contents($jsonFile);
                $all = json_decode($content, true) ?: [];
                foreach ($all as &$rec) {
                    if (isset($rec['username']) && $rec['username'] === $username) {
                        if (!isset($rec['gallery']) || !is_array($rec['gallery'])) {
                            $rec['gallery'] = [];
                        }
                        foreach ($uploaded as $f) {
                            $rec['gallery'][] = $username ? ($username . '/' . $f) : $f;
                        }
                        break;
                    }
                }
                file_put_contents($jsonFile, json_encode($all, JSON_PRETTY_PRINT));
            }
        }
    }
    if ($errors) {
        $response['message'] .= ($response['message'] ? ' | ' : '') . 'Errores: ' . implode('; ', $errors);
    }
} else {
    $response['message'] = 'No se recibieron archivos';
}

echo json_encode($response);
