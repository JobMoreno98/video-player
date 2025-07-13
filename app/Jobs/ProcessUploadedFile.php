<?php

namespace App\Jobs;

use App\Models\Videos;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\File;

class ProcessUploadedFile implements ShouldQueue
{
    use Queueable;

    protected $path, $outputName;

    public function __construct($path, $outputName)
    {
        $this->path = $path;
        $this->outputName = $outputName;
    }

    protected function generateToken(string $resource, int $duration = 600): string
    {
        $expires = now()->addSeconds($duration)->timestamp;
        return base64_encode(hash_hmac('sha256', "$resource|$expires", config('app.key'), true)) . "|$expires";
    }

    public function handle()
    {

        $video = Videos::where('uiid', $this->outputName)->first();
        $outputName = $this->outputName;
        //Log::info("Nombre de salida" . $outputName);

        $key = HLSExporter::generateEncryptionKey();

        // Guarda la clave en el sistema de archivos
        Storage::disk('local')->put("keys/{$outputName}.key", $key);

        // Construye la URL pública que se insertará en el .m3u8
        $keyUrl = route('video.key', [
            'key' => "{$outputName}.key",
            'token' => $this->generateToken("{$outputName}.key"),
        ]);

        //Log::info("Clave generada: " . bin2hex($key));
        $format = new X264('aac');

        $relativePath = 'private/encrypted';
        $fullPath = storage_path('app/' . $relativePath);

        $format->setKiloBitrate(1000);

        if (!Storage::disk('local')->exists($relativePath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        try {
            FFMpeg::fromDisk('local')
                ->open($this->path)
                ->exportForHLS()

                ->onProgress(function ($percentage) {
                    Log::info("⚙️ Progreso: {$percentage}%");
                })
                ->setSegmentLength(10)
                ->inFormat($format)
                ->withEncryptionKey($key, "{$outputName}.key", $keyUrl) // Laravel-FFMpeg debería generar el keyinfo con la URL pública configurada, si no, tendrás que hacerlo manualmente
                ->addFormat((new \FFMpeg\Format\Video\X264)->setKiloBitrate(500))
                ->addFormat((new \FFMpeg\Format\Video\X264)->setKiloBitrate(1000))
                ->addFormat((new \FFMpeg\Format\Video\X264)->setKiloBitrate(2000))
                ->toDisk('local')
                ->save("encrypted/" . $outputName . ".m3u8");

            if (Storage::disk('local')->exists("encrypted/" . $outputName . ".m3u8")) {
                Log::info("✅ Playlist creado correctamente: encrypted/" . $outputName . ".m3u8");
                $video->status = "Finalizado";
            } else {
                Log::error("❌ Playlist no encontrado: encrypted/{$outputName}.m3u8");
                $video->status = "Error";
            }

            Storage::disk('local')->delete($this->path);
        } catch (\Exception $e) {
            Log::error("❌ Error al procesar el archivo: " . $e->getMessage());
            $video->status = "Error";
        }
        $video->update();
    }
}
