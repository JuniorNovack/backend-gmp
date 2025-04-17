<?php

namespace App\Models;

trait BaseModel
{
    public function getCacheKey()
    {
        $table = strtolower($this->getTable());
        return $table . ":" . $this->id;
    }

    public function getCacheableRelations()
    {
        return property_exists($this, 'cacheableRelations') ? $this->cacheableRelations : [];
    }
}
