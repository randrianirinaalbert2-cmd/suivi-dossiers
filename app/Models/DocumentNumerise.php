<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentNumerise extends ModeleBase
{
    protected $table = 'documents_numerises';

    public const TYPE_BORDEREAU = 'bordereau';

    public const TYPE_DOSSIER = 'dossier';

    protected $fillable = [
        'bordereau_recu_id',
        'dossier_id',
        'utilisateur_id',
        'type_document',
        'nom_fichier',
        'chemin_fichier',
        'empreinte_fichier',
        'taille_fichier',
        'date_numerisation',
    ];

    protected function casts(): array
    {
        return [
            'taille_fichier' => 'integer',
            'date_numerisation' => 'datetime',
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function bordereauRecu(): BelongsTo
    {
        return $this->belongsTo(BordereauRecu::class);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->chemin_fichier);
    }

    public function tailleLisible(): string
    {
        $octets = $this->taille_fichier;
        $unites = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;

        while ($octets >= 1024 && $i < count($unites) - 1) {
            $octets /= 1024;
            $i++;
        }

        return number_format($octets, $i > 0 ? 1 : 0, ',', ' ').' '.$unites[$i];
    }
}
