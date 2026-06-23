<?php

namespace App\Services;

use App\Http\Resources\EspacioResource;
use App\Interfaces\EspacioRepositoryInterface;
use App\Interfaces\EspacioServiceInterface;
use App\Models\Escuela;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Lógica de negocio para Espacios.
 * Replica exacta de EspacioServicio.java
 */
class EspacioService implements EspacioServiceInterface
{
    public function __construct(
        private readonly EspacioRepositoryInterface $espacioRepository,
    ) {}

    /**
     * @Transactional(readOnly = true)
     * Listar todos los espacios con su escuela asociada.
     */
    public function listar(): array
    {
        $espacios = $this->espacioRepository->all();
        return $espacios->map(fn($e) => (new EspacioResource($e))->resolve())->toArray();
    }

    /**
     * @Transactional(readOnly = true)
     * Buscar un espacio por su ID.
     * Lanza 404 si no existe (replica ResponseStatusException NOT_FOUND).
     */
    public function buscarPorId(int $id): EspacioResource
    {
        $espacio = $this->espacioRepository->findById($id);

        if (!$espacio) {
            throw new NotFoundHttpException("No se encontro el espacio con id {$id}");
        }

        return new EspacioResource($espacio);
    }

    /**
     * Crear un nuevo espacio.
     * Valida código disponible y existencia de la escuela.
     */
    public function crear(array $data): EspacioResource
    {
        $this->validarCodigoDisponible($data['Codigo'], null);
        $this->obtenerEscuela($data['Escuela']);

        $espacio = $this->espacioRepository->create($data);
        return new EspacioResource($espacio);
    }

    /**
     * Actualizar un espacio existente.
     * Valida código disponible excluyendo el actual.
     */
    public function actualizar(int $id, array $data): EspacioResource
    {
        // Verificar que el espacio existe
        $espacioExistente = $this->espacioRepository->findById($id);
        if (!$espacioExistente) {
            throw new NotFoundHttpException("No se encontro el espacio con id {$id}");
        }

        $this->validarCodigoDisponible($data['Codigo'], $id);
        $this->obtenerEscuela($data['Escuela']);

        $espacio = $this->espacioRepository->update($id, $data);
        return new EspacioResource($espacio);
    }

    /**
     * Eliminar un espacio.
     * Lanza 404 si no existe.
     */
    public function eliminar(int $id): void
    {
        $espacio = $this->espacioRepository->findById($id);

        if (!$espacio) {
            throw new NotFoundHttpException("No se encontro el espacio con id {$id}");
        }

        $this->espacioRepository->delete($id);
    }

    /**
     * Replica: obtenerEscuela() de EspacioServicio.java
     * Verifica que la escuela existe o lanza 404.
     */
    private function obtenerEscuela(int $id): Escuela
    {
        $escuela = Escuela::find($id);

        if (!$escuela) {
            throw new NotFoundHttpException("No se encontro la escuela con id {$id}");
        }

        return $escuela;
    }

    /**
     * Replica: validarCodigoDisponible() de EspacioServicio.java
     * Verifica unicidad del código excluyendo el registro actual (para updates).
     */
    private function validarCodigoDisponible(?string $codigo, ?int $idActual): void
    {
        if ($codigo === null) {
            return;
        }

        $existente = $this->espacioRepository->findByCodigoIgnoreCase(trim($codigo));

        if ($existente && ($idActual === null || $existente->IdEspacio !== $idActual)) {
            throw new HttpException(400, "El codigo {$codigo} ya esta registrado en otro espacio.");
        }
    }
}
