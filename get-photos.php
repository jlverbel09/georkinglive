<?php
header('Content-Type: application/json');

$fotosDir = __DIR__ . '/fotos/';
$fotos = [];

if (is_dir($fotosDir)) {
    $files = scandir($fotosDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fotos[] = 'fotos/' . $file;
            }
        }
    }
}

echo json_encode($fotos);
?>
