@push('css')
    <style>
        video {
            width: 640px !important;
            height: 360px !important;
            margin: auto;
        }
    </style>
@endpush
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reproductor de video
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 mx-auto">
                    <h3 class="text-center border  border-bottom">{{ $video->nombre }}</h3>
                    <div>
                        <video id="video" autoplay muted controls></video>
                    </div>


                    @push('js')
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
                    @endpush

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
