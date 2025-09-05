@push('css')
    <style>
        .video-container {
            width: 80%;
            /* ancho fijo */
            aspect-ratio: 16 / 9;
            margin: 1rem auto;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: black;
            /* fondo mientras carga video */
        }

        .video-container video {
            width: 100%;
            height: 100%;
            display: block;
            /* para mantener proporción dentro del contenedor */
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-center text-2xl font-semibold mb-4 border-b pb-2 text-gray-700">{{ $video->nombre }}</h3>
                <video width="720" controls muted autoplay playsinline>
                    <source src="{{ route('video.stream', ['filename' => $video->uiid]) }}" type="video/mp4">
                    Tu navegador no soporta el elemento video.
                </video>
                {{--  
                <div class="video-container">
                    <video id="video" controls muted autoplay playsinline></video>
                </div>
                --}}
            </div>
        </div>
    </div>
    {{--  
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const video = document.getElementById('video');
                const videoSrc = @json($signedUrl);

                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(videoSrc);
                    hls.attachMedia(video);

                    hls.on(Hls.Events.MANIFEST_PARSED, () => {
                        video.play().catch(e => console.error('Play error:', e));
                    });

                    hls.on(Hls.Events.ERROR, (event, data) => {
                        console.error('HLS.js error:', data);
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = videoSrc;
                    video.addEventListener('loadedmetadata', () => {
                        video.play().catch(e => console.error('Play error:', e));
                    });
                } else {
                    alert("Tu navegador no soporta video HLS.");
                }
            });
        </script>
    @endpush
    --}}
</x-app-layout>
