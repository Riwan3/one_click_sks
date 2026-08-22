<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'jabatan' => $this->jabatan,
            'unit' => $this->unit,
            'lokasi' => $this->lokasi,
            'role' => $this->role->value,
            'passwordStatus' => $this->isDefaultPassword() ? 'default' : 'custom',
        ];
    }
}
