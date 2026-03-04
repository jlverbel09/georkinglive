<?php
header('Content-Type: application/json');

$fotos = [];

// si hay usuario en query, usar subdirectorio
$user = isset($_GET['user']) ? preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['user']) : '';
$fotosDir = __DIR__ . '/fotos/';
if ($user) {
    $fotosDir .= $user . '/';
}

if (is_dir($fotosDir)) {
    $files = scandir($fotosDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $path = 'fotos/' . ($user ? $user . '/' : '') . $file;
                $fotos[] = $path;
            }
        }
    }
}

echo json_encode($fotos);
?>
