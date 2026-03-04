<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Mi Mundo Viajero</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <div class="fondo"></div>
    <div class="capanegra"></div>
    <main class="main-content" style="max-width:500px; margin:0px auto;">
        <div style="width: 100%; text-align:center">
              <img class="logo" src="./logo.png" width="70%" alt="">
        </div>
        <h2>Crear cuenta</h2>
        <form id="registerForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" placeholder="nombre de usuario" required>
            </div>
            <div class="form-group">
                <label for="profilePhoto">Foto de Perfil</label>
                <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
            </div>
            <div class="form-group">
                <label for="profesion">Profesión</label>
                <input type="text" id="profesion" name="profesion" placeholder="Tu profesión" required>
            </div>
            <div class="form-group">
                <label for="fechaNacimiento">Fecha de nacimiento</label>
                <input type="date" id="fechaNacimiento" name="fechaNacimiento" required>
            </div>
            <div class="form-group">
                <label for="sobreMi">Sobre Mí</label>
                <textarea id="sobreMi" name="sobreMi" placeholder="Cuéntame sobre ti..." required></textarea>
            </div>
            <div class="form-group">
                <label for="fotosModal">Subir Fotos</label>
                <input type="file" id="fotosModal" name="fotos[]" accept="image/*" multiple required>
            </div>
            <div class="form-group">
                <label for="youtubeUrlModal">URL YouTube</label>
                <input type="text" id="youtubeUrlModal" name="youtubeUrl" placeholder="https://youtu.be/..." required>
            </div>
            <div class="form-group">
                <label for="palabraClave">Palabra clave (para acceder al perfil)</label>
                <input type="password" id="palabraClave" name="palabraClave" placeholder="Ingresa una palabra clave" required>
            </div>
            <button type="submit" class="btn-submit">Registrar</button>
        </form>
        <div id="registerStatus" style="margin-top:10px;color:#00a2ff"></div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(function(){
        $('#registerForm').on('submit', function(e){
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url:'registro.php',
                method:'POST',
                data:formData,
                processData:false,
                contentType:false,
                dataType:'json',
                success:function(res){
                    if(res.success){
                        $('#registerStatus').text('Registro exitoso, redirigiendo...');
                        setTimeout(function(){
                            window.location.href = '/mihorizonteviajero/' + encodeURIComponent($('#username').val());
                        },1000);
                    } else {
                        $('#registerStatus').text(res.message);
                    }
                },
                error:function(){
                    $('#registerStatus').text('Error al registrar');
                }
            });
        });
    });
    </script>
</body>
</html>