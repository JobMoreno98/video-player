<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadChunkRequest;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessUploadedFile;
use App\Models\Videos;
use Illuminate\Support\Str;

class ChunkUploadController extends Controller
{

    public function upload(UploadChunkRequest $request)
    {
        $chunk = $request->file('chunk');
        $uploadId = $request->input('upload_id');
        $chunkNumber = $request->input('chunk_number');
        $totalChunks = $request->input('total_chunks');
        $fileName = $request->input('file_name');
        $nombre = $request->input('nombre');

        $disk = Storage::disk('local');
        $tempDir = "uploads/tmp/{$uploadId}";

        // Crear directorio temporal si no existe
        if (!$disk->exists($tempDir)) {
            $disk->makeDirectory($tempDir);
        }

        // Guardar chunk si no existe aún
        $chunkPath = "{$tempDir}/chunk_{$chunkNumber}";
        if (!$disk->exists($chunkPath)) {
            $disk->put($chunkPath, file_get_contents($chunk->getRealPath()));
        }

        // Verificar si ya llegaron todos los chunks
        $chunksRecibidos = count($disk->files($tempDir));
        if ($chunksRecibidos == $totalChunks) {
            if ($disk->exists("{$tempDir}/.lock")) {
                return response()->json(['status' => 'ensamblaje en curso']);
            }

            $disk->put("{$tempDir}/.lock", 'locked');

            // Evitar duplicados en base de datos
            if (Videos::where('nombre', $nombre)->exists()) {
                return response()->json(['error' => 'Ya existe un video con ese nombre'], 422);
            }

            $file = Str::slug($nombre, '_') . '_' . time();
            $path = 'uploads/complete';
            if (!$disk->exists($path)) {
                $disk->makeDirectory($path);
            }

            $finalPath = "{$path}/{$file}";
            $final = fopen($disk->path($finalPath), 'ab');

            // Ensamblar usando streams para archivos grandes
            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkStream = fopen($disk->path("{$tempDir}/chunk_{$i}"), 'rb');
                stream_copy_to_stream($chunkStream, $final);
                fclose($chunkStream);
            }


            fclose($final);
            $disk->deleteDirectory($tempDir);

            // Registrar en base de datos
            Videos::create([
                'uiid' => $file,
                'nombre' => $nombre,
                'status' => 'Finalizado'
            ]);
            $disk->delete("{$tempDir}/.lock");


            // Opcional: lanzar Job para procesar el archivo
            // ProcessUploadedFile::dispatch($finalPath, $file);
        }

        return response()->json(['status' => 'chunk recibido']);
    }
}
