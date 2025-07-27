@push('css')
    <style>
        #progress-container {
            width: 100%;
            background: #eee;
            height: 25px;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 20px;
        }

        #progress-bar {
            width: 0%;
            height: 100%;
            background: #28a745;
            transition: width 0.3s ease-in-out;
        }


        #status {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Crear Video
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h1 class="text-xl font-semibold mb-6 text-gray-700">Subir archivo</h1>

                <div class="flex gap-6 items-center mb-6 max-w-6xl mx-auto">
                    <!-- Columna izquierda: nombre -->
                    <div class="flex-1">
                        <label for="nombre" class="block mb-1 font-semibold text-gray-700">Nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Columna derecha: input file -->
                    <div class="flex-1">
                        <label for="fileInput"
                            class="cursor-pointer flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 2v4M8 2v4M4 10h16" />
                            </svg>
                            Seleccionar archivo
                        </label>
                        <input type="file" id="fileInput" accept="video/mp4" class="hidden"
                            onchange="document.getElementById('fileName').textContent = this.files.length ? this.files[0].name : 'Ningún archivo seleccionado';" />
                        <p id="fileName" class="mt-2 text-gray-600 text-sm">Ningún archivo seleccionado</p>
                    </div>
                </div>


                <x-button onclick="subirArchivo()"
                    class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 focus:ring-offset-blue-200 transition duration-200 text-white font-semibold rounded px-6 py-2 mb-6 inline-block">
                    Guardar
                </x-button>

                <div id="progress-container" class="w-full bg-gray-200 rounded h-6 overflow-hidden mb-3">
                    <div id="progress-bar" class="bg-green-500 h-full w-0"></div>
                </div>

                <div id="status" class="font-semibold text-gray-700 min-h-[1.5rem]"></div>
            </div>
        </div>
    </div>
    @push('js')
        <script>
            const CHUNK_SIZE = (2 * 1024 * 1024); // Por ejemplo: 2MB

            async function subirArchivo() {
                const file = document.getElementById('fileInput').files[0];
                const nombre = document.getElementById('nombre').value.trim();
                const progressBar = document.getElementById('progress-bar');
                const status = document.getElementById('status');

                if (!file || !nombre) {
                    alert('⚠️ Debes completar el nombre y seleccionar un archivo.');
                    return;
                }

                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                const uploadId = Date.now();

                for (let i = 0; i < totalChunks; i++) {
                    const start = i * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, file.size);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('chunk', chunk);
                    formData.append('chunk_number', i + 1);
                    formData.append('total_chunks', totalChunks);
                    formData.append('upload_id', uploadId);
                    formData.append('file_name', file.name);
                    formData.append('nombre', nombre);

                    const response = await fetch('{{ route('archivo.chunk') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (!response.ok) {
                        if (response.status === 422) {
                            const errorData = await response.json();
                            const firstError = Object.values(errorData.errors)[0][0];
                            status.textContent = '❌ ' + firstError;
                        } else {
                            status.textContent = '❌ Error desconocido';
                        }
                        return;
                    }

                    const percent = Math.round(((i + 1) / totalChunks) * 100);
                    progressBar.style.width = percent + '%';
                    status.textContent = `Progreso: ${percent}%`;
                }

                status.textContent = '✅ Archivo subido completamente';
            }
        </script>
    @endpush

</x-app-layout>
