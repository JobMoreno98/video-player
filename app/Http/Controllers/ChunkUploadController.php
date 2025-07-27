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

        if (!$disk->exists($tempDir)) {
            $disk->makeDirectory($tempDir);
        }

        $chunkPath = "{$tempDir}/chunk_{$chunkNumber}";
        Storage::put($chunkPath, file_get_contents($chunk->getRealPath()));

        if ((int)$chunkNumber === (int)$totalChunks) {
            $file = Str::slug($nombre, '_') . '_' . time();
            $path = 'uploads/complete';
            if (!$disk->exists($path)) {
                
                $disk->makeDirectory($path);
            }
            $finalPath = "uploads/complete/{$fileName}";
            $final = fopen($disk->path($finalPath), 'ab');

            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkContent = Storage::get("{$tempDir}/chunk_{$i}");
                fwrite($final, $chunkContent);
            }

            fclose($final);
            Storage::deleteDirectory($tempDir);
            // Lanza un Job (si quieres hacer algo con el archivo)
            ProcessUploadedFile::dispatch($finalPath,  $file);
            Videos::create([
                'uiid' =>  $file,
                'nombre' => $nombre,
                'status' => 'En proceso'
            ]);
        }



        return response()->json(['status' => 'chunk recibido']);
    }
}
