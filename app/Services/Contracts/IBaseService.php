<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ModelNotFoundException;

interface IBaseService
{
    /**
     * @return Model
     * @throws ModelNotFoundException
     */
    public function getModel(): Model;

    /**
     * @param string $key
     * @param mixed $value
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findModelBy(string $key, $value);

    /**
     * @param int $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findModelById(int $id);

    /**
     * @param string $uuid
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findModelByUuid($uuid);
}
