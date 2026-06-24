<?php

namespace App\Services;

use App\Models\OlimpiadaComentario;
use App\Models\OlimpiadaPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class PostService
{
    public function listarPorEdicion(int $edicionId): Collection
    {
        return OlimpiadaPost::where('Edicion', $edicionId)
            ->withCount('comentarios')
            ->orderByDesc('FechaPublicacion')
            ->get();
    }

    public function crear(array $datos): OlimpiadaPost
    {
        return OlimpiadaPost::create([
            'Edicion' => $datos['edicionId'],
            'Titulo' => trim($datos['titulo']),
            'Contenido' => trim($datos['contenido']),
            'ImagenUrl' => $datos['imagenUrl'] ?? null,
            'Autor' => $datos['autor'] ?? 'Comité Olímpico UPT',
            'FechaPublicacion' => Carbon::now(),
        ]);
    }

    public function eliminar(int $id): void
    {
        $post = OlimpiadaPost::find($id);
        if (!$post) {
            throw new ModelNotFoundException('El post indicado no existe.');
        }
        $post->delete();
    }

    public function listarComentarios(int $postId): Collection
    {
        if (!OlimpiadaPost::where('IdPost', $postId)->exists()) {
            throw new ModelNotFoundException('El post indicado no existe.');
        }

        return OlimpiadaComentario::with('usuario')
            ->where('Post', $postId)
            ->orderBy('FechaComentario')
            ->get();
    }

    public function comentar(int $postId, array $datos): OlimpiadaComentario
    {
        if (!OlimpiadaPost::where('IdPost', $postId)->exists()) {
            throw new ModelNotFoundException('El post indicado no existe.');
        }

        return OlimpiadaComentario::create([
            'Post' => $postId,
            'Usuario' => $datos['usuarioId'],
            'Contenido' => trim($datos['contenido']),
            'FechaComentario' => Carbon::now(),
        ]);
    }
}
