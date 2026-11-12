<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends ModeleBase
{
    protected $table = 'destinations';

    protected $fillable = ['nom'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function bordereauxCreation(): HasMany
    {
        return $this->hasMany(BordereauCreation::class);
    }
}
