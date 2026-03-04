<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detección Automática de Ritmo</title>
    <style>
        body { background: #111; color: white; text-align: center; font-family: sans-serif; }
        .window { width: 600px; height: 350px; overflow: hidden; margin: 20px auto; border: 4px solid #444; }
        .gallery { display: flex; transition: transform 0.2s ease-out; }
        .gallery img { width: 600px; height: 350px; object-fit: cover; }
        #visualizer { width: 600px; height: 50px; background: #222; margin-bottom: 10px; }
        button { padding: 10px 20px; cursor: pointer; background: #f00; color: #fff; border: none; border-radius: 5px; }
    </style>
</head>
<body>

    <h2>Galería con Detección Automática</h2>
    <button id="startBtn">1. Activar Micrófono/Audio y Video</button>
    
    <div class="window">
        <div class="gallery" id="gallery">
            <img src="https://picsum.photos/id/101/600/350">
            <img src="https://picsum.photos/id/102/600/350">
            <img src="https://picsum.photos/id/103/600/350">
            <img src="https://picsum.photos/id/104/600/350">
        </div>
    </div>

    <canvas id="visualizer"></canvas>
    <div id="player"></div>

    <script>
        let audioContext, analyser, dataArray;
        let lastBeatTime = 0;
        let fotoActual = 0;

        // 1. Iniciar Audio y YouTube
        document.getElementById('startBtn').addEventListener('click', () => {
            initAudio();
            document.getElementById('startBtn').style.display = 'none';
        });

        async function initAudio() {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            // Captura el audio del sistema/micrófono para "escuchar" la canción
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const source = audioContext.createMediaStreamSource(stream);
            
            analyser = audioContext.createAnalyser();
            analyser.fftSize = 256;
            source.connect(analyser);

            dataArray = new Uint8Array(analyser.frequencyBinCount);
            detectBeat();
        }

        function detectBeat() {
            analyser.getByteFrequencyData(dataArray);

            // Analizamos las bajas frecuencias (el bajo/kick suele estar al inicio del array)
            let lowerFreqSum = 0;
            for(let i = 0; i < 10; i++) lowerFreqSum += dataArray[i];
            let average = lowerFreqSum / 10;

            // UMBRAL: Si la energía del bajo supera cierto nivel (ej. 200) y pasó suficiente tiempo
            let threshold = 210; 
            let now = Date.now();

            if (average > threshold && now - lastBeatTime > 300) { 
                moverGaleria();
                lastBeatTime = now;
            }

            dibujarVisualizador(average);
            requestAnimationFrame(detectBeat);
        }

        function moverGaleria() {
            fotoActual = (fotoActual + 1) % 4;
            document.getElementById('gallery').style.transform = `translateX(-${fotoActual * 600}px)`;
        }

        // --- YouTube API ---
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        document.head.appendChild(tag);

        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                height: '315', width: '560',
                videoId: 'kp3lM6t9Wls' // Cambia por el ID de tu canción
            });
        }

        function dibujarVisualizador(vol) {
            const canvas = document.getElementById('visualizer');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = vol > 200 ? 'red' : 'lime';
            ctx.fillRect(0, 0, vol * 2.5, 50);
        }
    </script>
</body>
</html>