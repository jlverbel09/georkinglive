<?php
// profile.php muestra el perfil de un usuario según ?user=xxx
$user = isset($_GET['user']) ? $_GET['user'] : null;
$userData = null;
if ($user) {
    $jsonFile = __DIR__ . '/registro.json';
    if (file_exists($jsonFile)) {
        $all = json_decode(file_get_contents($jsonFile), true) ?: [];
        foreach ($all as $rec) {
            if (isset($rec['username']) && $rec['username'] === $user) {
                $userData = $rec;
                break;
            }
        }
    }
}
if (!$userData) {
    // redirigir a landing si no existe usuario
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($userData['nombre']); ?> | Mi Horizonte Viajero</title>
    <link rel="stylesheet" href="./css/styles.css">

    <link rel="icon" href="https://georkingweb.com/live/logo.png" type="image/x-icon">
    <meta name="theme-color" content="#000000">
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Mi Horizonte Viajero">
    <meta name="author" content="GeorkingWeb">
    <meta name="description"
        content="Explorando rincones, probando sabores y viviendo historias. 🗺️📸">

    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta property="og:title" content="Mi Horizonte Viajero">
    <meta property="og:description" content="Explorando rincones, probando sabores y viviendo historias. 🗺️📸">
    <meta property="og:image" content="https://georkingweb.com/live/logo.png">
    <meta property="og:url" content="https://https://georkingweb.com/live/">
    <meta property="og:type" content="website">

</head>

<body>
    <!-- PANTALLA DE CARGA -->
    <div id="loadingScreen" class="loading-screen active">
        <div class="loading-container">
            <h2>Cargando perfil...</h2>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="loading-stats">
                <span id="photosLoaded">0</span> / <span id="totalPhotos">0</span> fotos
            </div>
            <div class="percentage" id="percentageDisplay">0%</div>
        </div>
    </div>

    <aside class="sidebar">
        <img draggable="false" ondragstart="return false;" oncontextmenu="return false;" src="<?php echo $userData['profilePhoto'] ? './profiles/' . $userData['profilePhoto'] : './usuario.png'; ?>" alt="Foto de Perfil" class="profile-img">
        <h1><?php echo htmlspecialchars($userData['nombre']); ?></h1>
        <!-- <span class="info-label">Usuario:</span>
        <span><?php echo htmlspecialchars($userData['username']); ?></span> -->
        <div class="tagline"><?php echo htmlspecialchars($userData['profesion']); ?></div>

        <span class="info-label">Edad:</span>
        <span id="edadDisplay"><?php
                                if (!empty($userData['fechaNacimiento'])) {
                                    $birth = new DateTime($userData['fechaNacimiento']);
                                    $now = new DateTime();
                                    echo $now->diff($birth)->y . ' años';
                                } else {
                                    echo htmlspecialchars($userData['edad']) ? htmlspecialchars($userData['edad']) . ' años' : '';
                                }
                                ?></span>

        <div id="lockedContent" style="display:none;">
            <div id="datosperfil">
                <span class="info-label">Nacimiento:</span>
                <span><?php echo !empty($userData['fechaNacimiento']) ? htmlspecialchars($userData['fechaNacimiento']) : ''; ?></span>
            </div>

            <!-- <span class="info-label">Sobre mí:</span> -->
            <p><?php echo nl2br(htmlspecialchars($userData['sobreMi'])); ?></p>

            <h2>Subir fotos</h2>
            <form id="uploadForm" action="upload-photos.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user); ?>">
                <input type="file" name="fotos[]" multiple accept="image/*">
                <button type="submit">Subir</button>
            </form>
            <div id="uploadStatus" style="margin-top:10px;color:#ffcc00"></div>

            <h2>URL YouTube</h2>
            <input type="text" class="caja" style="width: auto !important;" id="youtubeUrlInput" value="<?php echo htmlspecialchars($userData['youtubeUrl']); ?>" style="width:100%;margin-bottom:6px;">
            <button id="updateYoutubeBtn" style="margin-bottom:10px;width:100%">Actualizar URL</button>

            <button id="editProfileBtn" class="btn-open-form" style="margin-top:10px;">Editar perfil</button>

            <button id="lockProfileBtn" style="margin-top:10px;width:100%;background-color:#ff6b6b;color:white;border:none;padding:8px;border-radius:4px;cursor:pointer;">Cerrar sesión</button>
        </div>

        <div id="unlockMessage" style="text-align:center;color:#999;margin-top:20px;display:block;">
            <p style="text-align: left;"><?php echo nl2br(htmlspecialchars($userData['sobreMi'])); ?></p>

            <p style="font-size:12px;">Haz <strong>doble click</strong> en la foto de perfil para acceder</p>
        </div>

        <button id="musicToggle" title="Silenciar/activar música">🔇</button>




    </aside>

    <main class="main-content">
        <div class="masonry" id="galeria"></div>
    </main>

    <!-- MODAL CARRUSEL -->
    <div id="modalCarrusel" class="modal">
        <div class="modal-close">&times;</div>
        <div class="modal-content">
            <span class="modal-prev">&lt;</span>
            <img id="modalImg" class="modal-img" src="" alt="Foto">
            <span class="modal-next">&gt;</span>
        </div>
        <div class="modal-counter">
            <span id="fotoActual">1</span> / <span id="totalFotos">0</span>
        </div>
    </div>

    <!-- MODAL VALIDAR CONTRASEÑA -->
    <div id="validatePasswordModal" class="modal-form">
        <div class="form-content">
            <button class="form-close-btn">&times;</button>
            <h2>Acceder al Perfil</h2>
            <p style="text-align:center;font-size:14px;color:#aaa;margin-bottom:20px;">Ingresa la palabra clave para ver el perfil completo</p>
            <form id="validatePasswordForm">
                <div class="form-group">
                    <label for="passwordInput">Palabra clave</label>
                    <input type="password" id="passwordInput" name="palabraClave" placeholder="Ingresa la palabra clave" required>
                </div>
                <div id="passwordError" style="color:#ff6b6b;text-align:center;margin-bottom:10px;display:none;"></div>
                <button type="submit" class="btn-submit">Desbloquear</button>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR PERFIL -->
    <div id="editProfileModal" class="modal-form">
        <div class="form-content">
            <button class="form-close-btn">&times;</button>
            <h2>Editar Perfil</h2>
            <form id="editProfileForm">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user); ?>">
                <div class="form-group">
                    <label for="editNombre">Nombre</label>
                    <input type="text" id="editNombre" name="nombre" value="<?php echo htmlspecialchars($userData['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="editProfesion">Profesión</label>
                    <input type="text" id="editProfesion" name="profesion" value="<?php echo htmlspecialchars($userData['profesion']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="editFechaNacimiento">Fecha de nacimiento</label>
                    <input type="date" id="editFechaNacimiento" name="fechaNacimiento" value="<?php echo htmlspecialchars($userData['fechaNacimiento'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="editSobreMi">Sobre Mí</label>
                    <textarea id="editSobreMi" name="sobreMi" required><?php echo htmlspecialchars($userData['sobreMi']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="editYoutube">URL YouTube</label>
                    <input type="text" id="editYoutube" name="youtubeUrl" value="<?php echo htmlspecialchars($userData['youtubeUrl']); ?>" required>
                </div>
                <button type="submit" class="btn-submit">Guardar cambios</button>
            </form>
        </div>
    </div>

    <!-- reproductor oculto de YouTube para música de fondo -->
    <div id="bgPlayer" style="display:none;"></div>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        // pasar URL almacenada al JS global
        window.defaultYoutubeUrl = "<?php echo addslashes($userData['youtubeUrl']); ?>";
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./js/main.js"></script>

</body>

</html>