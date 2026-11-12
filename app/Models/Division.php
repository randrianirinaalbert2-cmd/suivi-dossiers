<?php

namespace App\Models;

use App\Enums\PoleDossier;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Division extends ModeleBase
{
    /**
     * Nom de la division dont les dossiers sont traités en mandatement.
     */
    public const NOM_SOLDE = 'Solde';

    /**
     * Nom de la division qui exerce le contrôle préalable (visa).
     */
    public const NOM_VISA = 'Visa';

    protected $table = 'divisions';

    protected $fillable = ['nom'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function typesDossiers(): HasMany
    {
        return $this->hasMany(TypeDossier::class);
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Chef de la division : utilisateur ayant le rôle « chef de division ».
     * Son nom est imprimé en bas du bordereau d'envoi (création).
     */
    public function chefDivision(): HasOne
    {
        return $this->hasOne(User::class)
            ->where('role', User::ROLE_CHEF_DIVISION)
            ->orderBy('id');
    }

    public function bordereauxRecus(): HasMany
    {
        return $this->hasMany(BordereauRecu::class);
    }

    public function bordereauxCreation(): HasMany
    {
        return $this->hasMany(BordereauCreation::class);
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Agents ayant au moins un dossier dans cette division.
     */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'dossiers')
            ->withPivot('type_dossier_id')
            ->distinct();
    }

    /**
     * Pôle qui traite naturellement les dossiers de cette division.
     */
    public function poleNaturel(): PoleDossier
    {
        return $this->nom === self::NOM_VISA
            ? PoleDossier::Visa
            : PoleDossier::Solde;
    }

    /**
     * Cette division tient-elle la matricule des agents ?
     *
     * Le bordereau de création porte l'IM (la matricule) des agents : il
     * relève donc de la seule division qui crée la matricule, la Solde.
     * La division Visa ne produit pas de matricule, donc pas de bordereau
     * de création.
     */
    public function chargeLesMatricules(): bool
    {
        return $this->nom === self::NOM_SOLDE;
    }

    /**
     * Division qui tient la matricule des agents.
     */
    public static function divisionMatricule(): ?self
    {
        return static::query()->where('nom', self::NOM_SOLDE)->first();
    }
}
