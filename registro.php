<?php
header('Content-Type: application/json');

// update actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'validate_password') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['palabraClave'] ?? '';
        $jsonFile = __DIR__ . '/registro.json';
        if (file_exists($jsonFile)) {
            $all = json_decode(file_get_contents($jsonFile), true) ?: [];
            foreach ($all as $rec) {
                if (isset($rec['username']) && $rec['username'] === $username) {
                    if (!empty($rec['palabraClave']) && $rec['palabraClave'] === $password) {
                        echo json_encode(['success' => true, 'message' => 'Acceso permitido']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Palabra clave incorrecta']);
                    }
                    exit;
                }
            }
        }
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    if ($action === 'update_url') {
        $username = $_POST['username'] ?? '';
        $newUrl = $_POST['youtubeUrl'] ?? '';
        $jsonFile = __DIR__ . '/registro.json';
        if (file_exists($jsonFile)) {
            $all = json_decode(file_get_contents($jsonFile), true) ?: [];
            foreach ($all as &$rec) {
                if (isset($rec['username']) && $rec['username'] === $username) {
                    $rec['youtubeUrl'] = $newUrl;
                    break;
                }
            }
            file_put_contents($jsonFile, json_encode($all, JSON_PRETTY_PRINT));
        }
        echo json_encode(['success' => true, 'message' => 'URL actualizada']);
        exit;
    }
    if ($action === 'update_profile') {
        $username = $_POST['username'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $profesion = $_POST['profesion'] ?? '';
        $fechaNacimiento = $_POST['fechaNacimiento'] ?? '';
        $sobreMi = $_POST['sobreMi'] ?? '';
        $youtubeUrl = $_POST['youtubeUrl'] ?? '';
        $jsonFile = __DIR__ . '/registro.json';
        if (file_exists($jsonFile)) {
            $all = json_decode(file_get_contents($jsonFile), true) ?: [];
            foreach ($all as &$rec) {
                if (isset($rec['username']) && $rec['username'] === $username) {
                    if ($nombre) $rec['nombre'] = $nombre;
                    if ($profesion) $rec['profesion'] = $profesion;
                    if ($fechaNacimiento) $rec['fechaNacimiento'] = $fechaNacimiento;
                    if ($sobreMi) $rec['sobreMi'] = $sobreMi;
                    if ($youtubeUrl) $rec['youtubeUrl'] = $youtubeUrl;
                    break;
                }
            }
            file_put_contents($jsonFile, json_encode($all, JSON_PRETTY_PRINT));
        }
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
        exit;
    }
}

// directorios
$uploadDir = __DIR__ . '/fotos/';
$profileDir = __DIR__ . '/profiles/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!is_dir($profileDir)) mkdir($profileDir, 0755, true);

$response = ['success' => false, 'message' => ''];

// recoge datos de texto
$username = $_POST['username'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$profesion = $_POST['profesion'] ?? '';
$fechaNacimiento = $_POST['fechaNacimiento'] ?? '';
$sobreMi = $_POST['sobreMi'] ?? '';
$youtubeUrl = $_POST['youtubeUrl'] ?? '';
$palabraClave = $_POST['palabraClave'] ?? '';

// calcular edad si se proporciona fechaNacimiento
$edad = '';
if ($fechaNacimiento) {
    $birth = new DateTime($fechaNacimiento);
    $now = new DateTime();
    $edad = $now->diff($birth)->y;
}

// función de guardado de fotos genérica
function handleFiles($fieldName, $targetDir) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $saved = [];
    if (!isset($_FILES[$fieldName])) return $saved;
    foreach ($_FILES[$fieldName]['name'] as $i => $name) {
        $tmp = $_FILES[$fieldName]['tmp_name'][$i];
        $error = $_FILES[$fieldName]['error'][$i];
        if ($error === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext,$allowed)) continue;
            $base = pathinfo($name, PATHINFO_FILENAME);
            $target = $targetDir . $name;
            $cnt = 1;
            while (file_exists($target)) {
                $target = $targetDir . $base . '_' . $cnt . '.' . $ext;
                $cnt++;
            }
            if (move_uploaded_file($tmp,$target)) {
                $saved[] = basename($target);
            }
        }
    }
    return $saved;
}

// procesar foto de perfil (un solo archivo)
$profilePhotoName = '';
if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['profilePhoto']['tmp_name'];
    $name = $_FILES['profilePhoto']['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext, $allowed)) {
        $target = $profileDir . $name;
        $cnt=1;
        $base = pathinfo($name, PATHINFO_FILENAME);
        while (file_exists($target)) {
            $target = $profileDir . $base . '_' . $cnt . '.' . $ext;
            $cnt++;
        }
        if (move_uploaded_file($tmp,$target)) {
            $profilePhotoName = basename($target);
        }
    }
}

// fotos adicionales
if ($username) {
    // carpeta de usuario dentro de fotos
    $uploadDir = rtrim($uploadDir, '/') . '/' . preg_replace('/[^a-zA-Z0-9_-]/','',$username) . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
}
$gallery = handleFiles('fotos', $uploadDir);
if ($username && $gallery) {
    foreach ($gallery as &$g) {
        $g = $username . '/' . $g;
    }
}

// compilar registro (antes comprobar duplicados de usuario)
$jsonFile = __DIR__ . '/registro.json';
$all = [];
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    $all = json_decode($content, true) ?: [];
}

// si el username ya existe, devolver error
foreach ($all as $existing) {
    if (isset($existing['username']) && $existing['username'] === $username) {
        $response['message'] = 'El nombre de usuario ya está en uso';
        echo json_encode($response);
        exit;
    }
}

$registro = [
    'username' => $username,
    'nombre' => $nombre,
    'profesion' => $profesion,
    'fechaNacimiento' => $fechaNacimiento,
    'edad' => $edad,
    'sobreMi' => $sobreMi,
    'youtubeUrl' => $youtubeUrl,
    'palabraClave' => $palabraClave,
    'profilePhoto' => $profilePhotoName,
    'gallery' => $gallery,
    'timestamp' => date('c')
];

// guardar en JSON (append a un arreglo de registros)
$all[] = $registro;
file_put_contents($jsonFile, json_encode($all, JSON_PRETTY_PRINT));

$response['success'] = true;
$response['message'] = 'Usuario registrado correctamente';
$response['data'] = $registro;

echo json_encode($response);
