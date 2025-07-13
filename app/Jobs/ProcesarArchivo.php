<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcesarArchivo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rutaArchivo;
    public function __construct($rutaArchivo)
    {
        $this->rutaArchivo = $rutaArchivo;
    }

    public function handle()
    {
        // Simulación: contar las líneas del archivo
        $contenido = Storage::get($this->rutaArchivo);
        $lineas = substr_count($contenido, "\n");

        // Aquí podrías guardar en BD, enviar un mail, etc.
        logger("Archivo procesado: {$this->rutaArchivo}, líneas: $lineas");
    }
}
