<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subida por partes con progreso</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        #progress-container { width: 100%; background: #eee; height: 25px; border-radius: 4px; overflow: hidden; margin-top: 20px; }
        #progress-bar { width: 0%; height: 100%; background: #28a745; transition: width 0.2s; }
        #status { margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <h1>Subir archivo grande (por partes)</h1>
    <input type="file" id="fileInput">
    <div id="progress-container">
        <div id="progress-bar"></div>
    </div>
    <div id="status"></div>

    <script>
        const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB

        document.getElementById('fileInput').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

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

                const response = await fetch('{{ route("archivo.chunk") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    status.textContent = '❌ Error al subir el archivo';
                    return;
                }

                const percent = Math.round(((i + 1) / totalChunks) * 100);
                progressBar.style.width = percent + '%';
                status.textContent = `Progreso: ${percent}%`;
            }
            if(response.ok){
                console.log(response)
            }

            status.textContent = '✅ Archivo subido completamente';
        });
    </script>

</body>
</html>
