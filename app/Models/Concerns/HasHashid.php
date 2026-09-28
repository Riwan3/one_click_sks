<?php

namespace App\Models\Concerns;

use App\Support\Hashids;

trait HasHashid
{
    /**
     * Get the value of the model's route key (encoded as Hashid).
     */
    public function getRouteKey(): mixed
    {
        return Hashids::encode($this->getKey()) ?? $this->getKey();
    }

    /**
     * Retrieve the model for a bound value (decodes Hashid or falls back to raw numeric ID).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $id = Hashids::decode($value) ?? $value;

        return $this->where($field ?? $this->getKeyName(), $id)->first();
    }

    /**
     * Accessor for hashid attribute ($model->hashid).
     */
    public function getHashidAttribute(): ?string
    {
        return Hashids::encode($this->getKey());
    }
}
