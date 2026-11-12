<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrigine extends ModeleBase
{
    protected $table = 'services_origine';

    protected $fillable = ['nom'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function bordereauxRecus(): HasMany
    {
        return $this->hasMany(BordereauRecu::class);
    }
}
