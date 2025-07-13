<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessUploadedFile;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ChunkUploadController extends Controller
{
    public function upload(Request $request)
    {
        $chunk = $request->file('chunk');
        $uploadId = $request->input('upload_id');
        $chunkNumber = $request->input('chunk_number');
        $totalChunks = $request->input('total_chunks');
        $fileName = $request->input('file_name');

        if (!$chunk || !$uploadId || !$chunkNumber || !$totalChunks || !$fileName) {
            return response()->json(['error' => 'Faltan datos'], 400);
        }

        $tempDir = "uploads/tmp/{$uploadId}";
        Storage::makeDirectory($tempDir);

        $chunkPath = "{$tempDir}/chunk_{$chunkNumber}";
        Storage::put($chunkPath, file_get_contents($chunk->getRealPath()));

        if ((int)$chunkNumber === (int)$totalChunks) {
            Storage::makeDirectory('uploads/complete');
            $finalPath = "uploads/complete/{$fileName}";
            $final = fopen(Storage::path($finalPath), 'ab');

            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkContent = Storage::get("{$tempDir}/chunk_{$i}");
                fwrite($final, $chunkContent);
            }

            fclose($final);
            Storage::deleteDirectory($tempDir);


            // Lanza un Job (si quieres hacer algo con el archivo)
            ProcessUploadedFile::dispatch($finalPath, 'babymetal_01');
        }

        return response()->json(['status' => 'chunk recibido']);
    }
}
