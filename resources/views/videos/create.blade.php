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
            transition: width 0.2s;
        }

        #status {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Video
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1>Subir archivo</h1>
                    <x-text-input placeholder="Nombre" name="nombre" id="nombre"></x-text-input>
                   
                    <input type="file" id="fileInput" accept="video/mp4">
                    <div id="progress-container">
                        <div id="progress-bar"></div>
                    </div>
                    <div id="status"></div>
                </div>
            </div>
        </div>
    </div>
    @push('js')
        <script>
            const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB

            document.getElementById('fileInput').addEventListener('change', async (e) => {
                const file = e.target.files[0];

                const nombre = document.getElementById('nombre').value.trim();

                if (!file || !nombre) {
                    alert('⚠️ Debes completar el nombre y seleccionar un archivo.');
                    return;
                }

                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                const uploadId = Date.now();
                const progressBar = document.getElementById('progress-bar');
                const status = document.getElementById('status');

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
                            console.error('Errores de validación:', errorData.errors);

                            // Puedes mostrar el primer error, por ejemplo:
                            const status = document.getElementById('status');
                            const firstError = Object.values(errorData.errors)[0][0];
                            status.textContent = '❌ ' + firstError;
                        } else {
                            status.textContent = '❌ Error desconocido';
                        }

                        return;
                    }

                    if (!response.ok) {
                        status.textContent = '❌ Error al subir el archivo';
                        return;
                    }

                    const percent = Math.round(((i + 1) / totalChunks) * 100);
                    progressBar.style.width = percent + '%';
                    status.textContent = `Progreso: ${percent}%`;
                    if (response.ok) {
                        console.log(response)
                    }
                }


                status.textContent = '✅ Archivo subido completamente';
            });
        </script>
    @endpush

</x-app-layout>
