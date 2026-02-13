<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function paginate(?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= (int) config('services.pagination.per_page', 10);

        return $this->model->paginate($perPage);
    }

    public function findBy(string $field, mixed $value): ?Model
    {
        return $this->model->where($field, $value)->first();
    }

    public function where(string $field, mixed $value): Collection
    {
        return $this->model->where($field, $value)->get();
    }

    /**
     * Get the model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the model instance
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }
}
