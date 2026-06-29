<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    private const MAX_SIZE_KB = 51200; // 50 MB

    /**
     * Extensión => categoría de adjunto (image|video|audio|file).
     * Lista cerrada a propósito: evita subir extensiones ejecutables (.php, .exe, .js, etc.)
     * a una carpeta servida públicamente por el servidor web.
     */
    private const EXTENSIONES = [
        'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'gif' => 'image', 'webp' => 'image', 'bmp' => 'image',
        'mp4' => 'video', 'webm' => 'video', 'mov' => 'video', 'avi' => 'video', 'mkv' => 'video',
        'mp3' => 'audio', 'wav' => 'audio', 'ogg' => 'audio', 'm4a' => 'audio', 'aac' => 'audio', 'flac' => 'audio',
        'pdf' => 'file', 'doc' => 'file', 'docx' => 'file', 'xls' => 'file', 'xlsx' => 'file',
        'ppt' => 'file', 'pptx' => 'file', 'txt' => 'file', 'csv' => 'file',
        'zip' => 'file', 'rar' => 'file', '7z' => 'file',
    ];

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:' . self::MAX_SIZE_KB,
        ]);

        $file = $request->file('file');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (! array_key_exists($extension, self::EXTENSIONES)) {
            throw ValidationException::withMessages([
                'file' => ['Tipo de archivo no permitido (.' . $extension . ').'],
            ]);
        }

        $tipo = self::EXTENSIONES[$extension];
        $nombreOriginal = $file->getClientOriginalName();
        $nombre = Str::uuid() . '.' . $extension;

        $file->storeAs('canal-media', $nombre, 'public');

        return response()->json([
            'url' => Storage::disk('public')->url('canal-media/' . $nombre),
            'tipo' => $tipo,
            'nombre' => $nombreOriginal,
            'mime' => $file->getMimeType(),
        ]);
    }
}
