<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Facades\URL;


class VideoStreamController extends Controller
{
    public function stream(Request $request, $filename)
    {
        $referer = $request->headers->get('referer');
        /*
        if (!str_starts_with($referer, config('app.url'))) {
            abort(403, 'Acceso no autorizado');
        }*/

        $filePath = "uploads/complete/{$filename}";

        if (!Storage::exists($filePath)) {
            abort(404);
        }

        $fullPath = Storage::path($filePath);
        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath);

        $start = 0;
        $end = $fileSize - 1;

        if ($request->hasHeader('Range')) {
            // Soporte para Range: bytes=start-end
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches);
            $start = intval($matches[1]);
            $end = isset($matches[2]) && is_numeric($matches[2]) ? intval($matches[2]) : $end;
        }

        // Validaciones seguras
        $start = max(0, $start);
        $end = min($end, $fileSize - 1);
        $length = $end - $start + 1;

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Content-Range' => "bytes $start-$end/$fileSize",
            'Accept-Ranges' => 'bytes',
        ];

        // Asegúrate de limpiar cualquier salida previa
        if (ob_get_length()) {
            ob_end_clean();
        }

        return new StreamedResponse(function () use ($fullPath, $start, $length) {
            $handle = fopen($fullPath, 'rb');
            fseek($handle, $start);

            $bufferSize = 65536; // 8KB
            $bytesSent = 0;

            while (!feof($handle) && $bytesSent < $length) {
                $readLength = min($bufferSize, $length - $bytesSent);
                echo fread($handle, $readLength);
                flush();
                $bytesSent += $readLength;
            }

            fclose($handle);
        }, 206, $headers);
    }

    protected function generateToken(string $resource, int $duration = 600): string
    {
        $expires = now()->addSeconds($duration)->timestamp;
        return base64_encode(hash_hmac('sha256', "$resource|$expires", config('app.key'), true)) . "|$expires";
    }

    public function playlist(Request $req, $playlist)
    {

        if (!$req->hasValidSignature()) {
            abort(403);
        }

        $playlistName = preg_replace('/\.m3u8$/', '', $playlist);
        $path = "encrypted/{$playlistName}.m3u8";

        if (!Storage::disk('local')->exists($path)) {
            throw new \Exception("❌ El archivo {$path} no existe en el disco local");
        }

        $contents = Storage::disk('local')->get($path);
        if (empty($contents)) {
            throw new \Exception("❌ El archivo {$path} está vacío o corrupto");
        }

        /*
        return FFMpeg::dynamicHLSPlaylist()
            ->fromDisk('local')
            ->open( $path)
            ->setKeyUrlResolver(fn($key) => route('video.key', [
                'key' => $key,
                'token' => $this->generateToken($key)
            ]))
            ->setMediaUrlResolver(fn($seg) => route('video.segment', [
                'segment' => $seg,
                'token' => $this->generateToken($seg)
            ]))
            ->setPlaylistUrlResolver(fn($pl) => route('video.playlist', [
                'playlist' => $pl,
                'expires' => now()->addSeconds(600)->timestamp,
                'signature' => URL::temporarySignedRoute('video.playlist', now()->addMinutes(10), ['playlist' => $pl])
            ]));*/

        $content = FFMpeg::dynamicHLSPlaylist()
            ->fromDisk('local')
            ->open($path)
            ->setKeyUrlResolver(fn($key) => route('video.key', [
                'key' => $key,
                'token' => $this->generateToken($key)
            ]))
            ->setMediaUrlResolver(fn($seg) => route('video.segment', [
                'segment' => $seg,
                'token' => $this->generateToken($seg)
            ]))
            ->setPlaylistUrlResolver(
                fn($pl) =>
                URL::temporarySignedRoute('video.playlist', now()->addMinutes(10), ['playlist' => $pl])
            )
            ->get(); // <- Esto devuelve el contenido como texto

        return response($content, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

public function key(Request $req, $keyFilename)
{
    [$token, $expires] = explode('|', $req->query('token') ?? '');

    if (
        !$token || now()->timestamp > (int)$expires ||
        hash_hmac('sha256', "$keyFilename|$expires", config('app.key'), true) !== base64_decode($token)
    ) {
        abort(403, 'Token inválido o expirado');
    }

    $path = storage_path("app/private/keys/{$keyFilename}");

    if (!file_exists($path)) {
        abort(404, "Clave no encontrada: {$keyFilename}");
    }

    return response()->file($path, [
        'Content-Type' => 'application/octet-stream',
    ]);
}

    public function segment(Request $req, $segment)
    {
        [$token, $expires] = explode('|', $req->query('token') ?? '');
        if (
            !$token || now()->timestamp > (int)$expires ||
            hash_hmac('sha256', "$segment|$expires", config('app.key'), true) !== base64_decode($token)
        ) {
            abort(403);
        }

        $path = storage_path("app/private/encrypted/{$segment}");

        if (!file_exists($path)) {
            abort(404, "Segmento no encontrado: {$segment}");
        }

        return response()->file($path, [
            'Content-Type' => 'video/MP2T',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
