<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComentarioRequest;
use App\Http\Requests\PostRequest;
use App\Models\OlimpiadaComentario;
use App\Models\OlimpiadaPost;
use App\Services\PostService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private PostService $postService)
    {
    }

    public function index(Request $request)
    {
        $edicionId = (int) $request->query('edicionId');

        $posts = $this->postService->listarPorEdicion($edicionId)
            ->map(fn (OlimpiadaPost $post) => $this->mapearPost($post))
            ->all();

        return response()->json($posts);
    }

    public function store(PostRequest $request)
    {
        $post = $this->postService->crear($request->validated());
        return response()->json($this->mapearPost($post), 201);
    }

    public function destroy($id)
    {
        try {
            $this->postService->eliminar((int) $id);
            return response()->json(['message' => 'Post eliminado.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function comentarios($id)
    {
        try {
            $comentarios = $this->postService->listarComentarios((int) $id)
                ->map(fn (OlimpiadaComentario $c) => $this->mapearComentario($c))
                ->all();
            return response()->json($comentarios);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function storeComentario(ComentarioRequest $request, $id)
    {
        try {
            $comentario = $this->postService->comentar((int) $id, $request->validated());
            $comentario->load('usuario');
            return response()->json($this->mapearComentario($comentario), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function mapearPost(OlimpiadaPost $post): array
    {
        return [
            'id' => $post->IdPost,
            'edicionId' => $post->Edicion,
            'titulo' => $post->Titulo,
            'contenido' => $post->Contenido,
            'imagenUrl' => $post->ImagenUrl,
            'autor' => $post->Autor,
            'fechaPublicacion' => $post->FechaPublicacion,
            'totalComentarios' => $post->comentarios_count ?? 0,
        ];
    }

    private function mapearComentario(OlimpiadaComentario $comentario): array
    {
        return [
            'id' => $comentario->IdComentario,
            'postId' => $comentario->Post,
            'usuarioId' => $comentario->Usuario,
            'usuarioNombre' => $comentario->usuario?->nombre_completo,
            'contenido' => $comentario->Contenido,
            'fechaComentario' => $comentario->FechaComentario,
        ];
    }
}
