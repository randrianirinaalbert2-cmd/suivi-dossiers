<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class ModeleBase extends Model
{
    public const CREATED_AT = 'cree_le';

    public const UPDATED_AT = 'modifie_le';
}
