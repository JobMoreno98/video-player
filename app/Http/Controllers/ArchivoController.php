<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcesarArchivo;

class ArchivoController extends Controller
{
    public function formulario()
    {
        return view('subir-archivo');
    }

    public function subir(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:204800', // 200MB
        ]);

        // Guardar archivo en storage/app/uploads
        $path = $request->file('archivo')->store('uploads');

        // Despachar el Job
        ProcesarArchivo::dispatch($path);

        return back()->with('success', 'Archivo subido y en proceso.');
    }
}
