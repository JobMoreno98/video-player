<!DOCTYPE html>
<html>

<head>
    <title>Reproductor HLS</title>
</head>

<body>

    <video id="video" autoplay muted controls width="640" height="360"></video>

    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const videoSrc = @json($signedUrl); // Pon aquí la URL correcta
            console.log(videoSrc)


            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(videoSrc);
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    console.log('Playlist parsed');
                    video.muted = true;
                    video.play().catch(e => console.error('Play error:', e));
                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = videoSrc;
                video.addEventListener('loadedmetadata', () => {
                    video.play().catch(err => console.error('Play error:', err));
                });
            } else {
                alert("Tu navegador no soporta video HLS.");
            }
            const hls = new Hls();

            hls.on(Hls.Events.ERROR, function(event, data) {
                console.error('HLS.js error:', data);
            });

            hls.loadSource(videoSrc);
            hls.attachMedia(video);
        });
    </script>

</body>

</html>
