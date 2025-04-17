<?php

namespace App\Services;

use App\Exceptions\Handler;
use App\Exceptions\ModelException;
use App\Utils\CustomErrorMessages;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\CachedItemNotFoundException;

abstract class BaseService
{
    const CACHE_DURATION = 86400;
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @return Model
     */
    abstract protected function getModelObject(): Model;

    /**
     * @return Model
     * @throws ModelException
     */
    public function getModel(): Model
    {
        if (!isset($this->model)) {
            throw new ModelException("Model not loaded");
        }

        return $this->model;
    }

    /**
     * @param Model $model
     * @return void
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * Model is unset by setting it to null
     */
    public function unsetModel(): void
    {
        unset($this->model);
    }


    /**
     * @param Model $model
     * @throws ModelException
     */
    public function insert(Model $model)
    {
        if (!$model->save()) {
            throw new ModelException('Failed to insert model');
        }

        return $this->findModelById($model->id);
    }

    /**
     * @param Model $model
     * @throws ModelException
     */
    public function update(Model $model)
    {
        if (!$model->update()) {
            throw new ModelException('Failed to update model');
        }

        $this->redisCacheDataBase()::forget($model->getCacheKey());

        return $this->findModelById($model->id);
    }

    /**
     * @param Model $model
     * @throws ModelException
     */
    public function delete(Model $model)
    {
        if (!$model->delete()) {
            throw new ModelException('Failed to delete model');
        }
        $this->redisCacheDataBase()::forget($model->getCacheKey());
        $this->unsetModel();
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return Model
     * @throws ModelException
     */
    public function findModelBy(string $key, $value)
    {
        $table = strtolower($this->getModelObject()->getTable());
        $secondary_cache_key = $table . ":" . $key . ":" . $value;

        try {
            $main_cache_key = $this->getFromCache($secondary_cache_key);
            return $this->getFromCache($main_cache_key);
        } catch (CachedItemNotFoundException $e) {
            app()->get(Handler::class)->report($e);
            $existe = $this->getModelObject()::where([$key => $value])->exists();
            if ($existe) {
                $result = $this->getModelObject()::where([$key => $value])->first();
                $relations = $this->getCacheableRelations($result);

                $model = !blank($relations) ? $result->load($relations) : $result;
                return $this->saveReferenceToCache($secondary_cache_key, $model, now()->addDay());
            }else{
                return $this->getModelObject();
            }
        }
    }


    /**
     * get and return a models relationship
     *
     * @param Model $model
     * @return array
     */
    protected function getCacheableRelations(Model $model)
    {
        if (method_exists($model, 'getCacheableRelations')) {
            return $model->getCacheableRelations();
        }
        return [];
    }


    /**
     * @param int $id
     * @return Model
     * @throws ModelException
     */
    public function findModelById($id)
    {
        $table = strtolower($this->getModelObject()->getTable());
        $cache_key = $table . ":" . $id;

        $model = $this->redisCacheDataBase()::remember($cache_key, self::CACHE_DURATION, function () use ($id) {
            $modelObject = $this->getModelObject();
            $relations = $this->getCacheableRelations($modelObject);
            return $this->getModelObject()::with(array_filter($relations))->find($id);
        });

        if (!$model) {
            $error_message = CustomErrorMessages::interpolate(CustomErrorMessages::MODEL_NOT_FOUND, [
                'model' => get_class($this->getModelObject()),
                'key' => 'id',
                'value' => $id
            ]);
            throw new ModelException($error_message);
        }

        return $model;
    }

    /**
     * @param string $uuid
     * @return Model
     * @throws ModelException
     */
    public function findModelByUuid($uuid)
    {
        $table = strtolower($this->getModelObject()->getTable());
        $cache_key = $table . ":" . $uuid;

        $model = $this->redisCacheDataBase()::remember($cache_key, self::CACHE_DURATION, function () use ($uuid) {
            $modelObject = $this->getModelObject();
            $relations = $this->getCacheableRelations($modelObject);
            return $this->getModelObject()::with(array_filter($relations))->where('uuid', $uuid)->first();
        });

        if (!$model) {
            $error_message = CustomErrorMessages::interpolate(CustomErrorMessages::MODEL_NOT_FOUND, [
                'model' => get_class($this->getModelObject()),
                'key' => 'uuid',
                'value' => $uuid
            ]);
            throw new ModelException($error_message);
        }

        return $model;
    }

    /**
     * @param string $secondaryKey
     * @param Model|null $model
     * @param \DateTimeInterface $expirationDate
     * @return Model
     * @throws ModelException
     */
    protected function saveReferenceToCache(
        string $secondaryKey,
        ?Model $model,
        \DateTimeInterface $expirationDate
    ) {
        [, $key, $value] = explode(":", $secondaryKey);

        if (!$model) {
            $error_message = CustomErrorMessages::interpolate(CustomErrorMessages::MODEL_NOT_FOUND, [
                'model' => get_class($this->getModelObject()),
                'key' => $key,
                'value' => $value
            ]);
            throw new ModelException($error_message);
        }

        $mainKey = $model->getCacheKey();

        $this->redisCacheDataBase()::put($mainKey, $model);
        $this->redisCacheDataBase()::put($secondaryKey, $mainKey, $expirationDate);

        return $model;
    }

    /**
     * @param string $key
     * @throws CachedItemNotFoundException
     * @return mixed
     */
    protected function getFromCache(string $key)
    {
        $result = $this->redisCacheDataBase()::get($key);
        if (!$result) {
            throw new CachedItemNotFoundException("There is no item at $key");
        }

        return $result;
    }

    /**
     * @return Cache
     */
    protected function redisCacheDataBase(): Cache
    {
        return new Cache();
    }
}
