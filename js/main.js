let fotosArray = [];
let fotoActualIndex = 0;

// ===== música YouTube =====
var player = null;
var apiReady = false;

// extrae el ID de vídeo de una URL completa de YouTube
function extractYouTubeId(url) {
    var regex = /(?:youtube\.com\/(?:.*v=|v\/|embed\/)|youtu\.be\/)([\w-]{11})/;
    var match = url.match(regex);
    return match ? match[1] : null;
}

function createPlayer(id) {
    player = new YT.Player('bgPlayer', {
        height: '0',
        width: '0',
        videoId: id,
        playerVars: {
            'autoplay': 1,
            'loop': 1,
            'playlist': id,
            'controls': 0,
            'disablekb': 1,
            'modestbranding': 1,
            'iv_load_policy': 3,
            'rel': 0
        },
        events: {
            'onReady': function(e) {
                e.target.setVolume(20);
                e.target.playVideo();
                $('#musicToggle').text('🔊');
            }
        }
    });
}

// debe estar en ámbito global para que la API pueda llamarla
function onYouTubeIframeAPIReady() {
    apiReady = true;
}

$(document).ready(function() {
    // bloquear interacciones básicas con imágenes
    $(document).on('contextmenu', 'img', function(){ return false; });
    $(document).on('dragstart', 'img', function(){ return false; });
    $(document).on('mousedown', 'img', function(){ return false; });

    // Cargar fotos dinámicamente
    function getUsername(){
        // first try query string
        var params = new URLSearchParams(window.location.search);
        if (params.has('user')) return params.get('user');
        // otherwise, derive from path (/username)
        var parts = window.location.pathname.replace(/\/+$/, '').split('/');
        return parts[parts.length-1] || '';
    }
    var userQuery = '';
    var username = getUsername();
    if (username) {
        userQuery = 'user=' + encodeURIComponent(username);
    }
    $.getJSON('get-photos.php' + (userQuery ? '?' + userQuery : ''), function(fotos) {
        fotosArray = fotos;
        let galeriaHtml = '';

        fotos.forEach((foto, index) => {
            galeriaHtml += `
                <div class="item" data-index="${index}">
                    <img draggable="false" ondragstart="return false;" oncontextmenu="return false;" onmousedown="return false;" src="${foto}" alt="Foto ${index + 1}">
                </div>
            `;
        });

        $('#galeria').html(galeriaHtml);
        $('#totalFotos').text(fotos.length);

        // Evento click en las fotos para abrir el modal
        $('.item').on('click', function() {
            fotoActualIndex = $(this).data('index');
            abrirModal();
        });
    }).fail(function() {
        console.error('Error cargando las fotos');
        $('#galeria').html('<p style="color: red;">Error al cargar las fotos</p>');
    });

    // Cerrar modal carrusel
    $('.modal-close').on('click', cerrarModal);

    // Click fuera del modal para cerrar
    $('#modalCarrusel').on('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });

    // Navegación del carrusel
    $('.modal-prev').on('click', function(e) {
        e.stopPropagation();
        fotoActualIndex = (fotoActualIndex - 1 + fotosArray.length) % fotosArray.length;
        mostrarFoto();
    });

    $('.modal-next').on('click', function(e) {
        e.stopPropagation();
        fotoActualIndex = (fotoActualIndex + 1) % fotosArray.length;
        mostrarFoto();
    });

    // Navegación con teclas de flecha
    $(document).on('keydown', function(e) {
        if ($('#modalCarrusel').hasClass('active')) {
            if (e.key === 'ArrowLeft') {
                fotoActualIndex = (fotoActualIndex - 1 + fotosArray.length) % fotosArray.length;
                mostrarFoto();
            } else if (e.key === 'ArrowRight') {
                fotoActualIndex = (fotoActualIndex + 1) % fotosArray.length;
                mostrarFoto();
            } else if (e.key === 'Escape') {
                cerrarModal();
            }
        }
    });

    // Calcular edad
    function calcularEdad() {
        const fechaNacimiento = new Date('1997-09-09');
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mes = hoy.getMonth() - fechaNacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }

        return edad;
    }

    if (window.location.search.indexOf('user=') === -1) {
        $('#edadDisplay').text(calcularEdad() + ' años');
    }

    // manejo de subida masiva
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'upload-photos.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#uploadStatus').text(response.message);
                    // recargar galería para mostrar nuevas fotos
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#uploadStatus').text(response.message);
                }
            },
            error: function() {
                $('#uploadStatus').text('Error al subir fotos');
            }
        });
    });

    var defaultYoutubeUrl = window.defaultYoutubeUrl || 'https://www.youtube.com/watch?v=beXEr5xMCP0&list=RDCkCHc8Un98c&index=14';

    function loadMusic(url) {
        url = url || defaultYoutubeUrl;
        var id = extractYouTubeId(url);
        if (!id) {
            console.error('URL de YouTube no válida');
            return;
        }
        if (apiReady) {
            if (player) {
                player.loadVideoById(id);
            } else {
                createPlayer(id);
            }
        } else {
            setTimeout(function() { loadMusic(url); }, 500);
        }
    }

    // Cargar música automáticamente
    loadMusic();

    function toggleMusic() {
        if (!player || !player.getPlayerState) return;
        var state = player.getPlayerState();
        if (state === YT.PlayerState.PLAYING || state === YT.PlayerState.BUFFERING) {
            player.pauseVideo();
            $('#musicToggle').text('🔇');
        } else {
            player.playVideo();
            $('#musicToggle').text('🔊');
        }
    }
    $('#musicToggle').on('click', toggleMusic);

    // actualizar URL de YouTube desde perfil
    $('#updateYoutubeBtn').on('click', function(){
        var newUrl = $('#youtubeUrlInput').val().trim();
        if(!newUrl) return;
        $.post('registro.php',{action:'update_url',username:getUsername(),youtubeUrl:newUrl},function(res){
            if(res.success){
                defaultYoutubeUrl = newUrl;
                loadMusic(newUrl);
                alert('URL actualizada');
            } else {
                alert('Error: '+res.message);
            }
        },'json');
    });

    // abrir modal edición perfil
    $('#editProfileBtn').on('click', function(){
        $('#editProfileModal').addClass('active');
    });

    // cerrar modal edición
    $('#editProfileModal .form-close-btn').on('click', function(){
        $('#editProfileModal').removeClass('active');
    });

    // envío de formulario de edición
    $('#editProfileForm').on('submit', function(e){
        e.preventDefault();
        var formData = $(this).serialize();
        $.post('registro.php', formData + '&action=update_profile', function(res){
            if(res.success){
                alert('Perfil actualizado. Recargando...');
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        },'json');
    });

    // ===== VALIDACIÓN DE CONTRASEÑA PARA PERFIL =====
    var lockKey = 'profile_unlocked_' + username;
    
    // Verificar si ya está desbloqueado en esta sesión
    if(localStorage.getItem(lockKey) === 'true') {
        unlockProfile();
    }

    // Double-click en foto de perfil para validar o, si ya está desbloqueado, para editar
    $('.profile-img').on('dblclick', function(){
        if(localStorage.getItem(lockKey) === 'true') {
            // perfil desbloqueado: abrir modal edición
            $('#editProfileModal').addClass('active');
            return;
        }
        // si está bloqueado, solicitar contraseña
        $('#validatePasswordModal').addClass('active');
        // Limpiar campo y error previos
        $('#passwordInput').val('').focus();
        $('#passwordError').hide().text('');
    });

    // Cerrar modal de validación
    $('#validatePasswordModal .form-close-btn').on('click', function(){
        $('#validatePasswordModal').removeClass('active');
    });

    // Click fuera del modal para cerrar
    $('#validatePasswordModal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });

    // Enviar validación de contraseña
    $('#validatePasswordForm').on('submit', function(e){
        e.preventDefault();
        var password = $('#passwordInput').val().trim();
        if(!password) {
            $('#passwordError').text('Por favor ingresa la palabra clave').show();
            return;
        }

        $.post('registro.php', {
            action: 'validate_password',
            username: username,
            palabraClave: password
        }, function(res){
            if(res.success){
                // Contraseña correcta
                localStorage.setItem(lockKey, 'true');
                $('#validatePasswordModal').removeClass('active');
                unlockProfile();
            } else {
                // Contraseña incorrecta
                $('#passwordError').text('Palabra clave incorrecta').show();
                $('#passwordInput').val('').focus();
            }
        }, 'json').fail(function(){
            $('#passwordError').text('Error al validar. Intenta again').show();
        });
    });

    function unlockProfile(){
        $('#lockedContent').slideDown(300);
        $('#unlockMessage').slideUp(300);
        // Cargar la música cuando se desbloquea
        loadMusic();
    }

    // Cerrar sesión de palabra clave
    $('#lockProfileBtn').on('click', function(){
        localStorage.removeItem(lockKey);
        $('#lockedContent').slideUp(300);
        $('#unlockMessage').slideDown(300);
        $('#passwordInput').val('');
        $('#passwordError').hide();
        if(player && player.pauseVideo) player.pauseVideo();
    });

    $('#registerModal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });


});

function abrirModal() {
    mostrarFoto();
    $('#modalCarrusel').addClass('active');
}

function cerrarModal() {
    $('#modalCarrusel').removeClass('active');
}

function mostrarFoto() {
    $('#modalImg').attr('oncontextmenu', 'return false;');
    $('#modalImg').attr('src', fotosArray[fotoActualIndex]);
    $('#fotoActual').text(fotoActualIndex + 1);
}
