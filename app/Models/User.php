<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['nom', 'photo', 'courriel', 'mot_de_passe', 'role', 'division_id'])]
#[Hidden(['mot_de_passe', 'jeton_souvenir'])]
class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, Notifiable;

    public const ROLE_ADMINISTRATEUR = 'administrateur';

    public const ROLE_CHEF_DIVISION = 'chef_division';

    public const ROLE_RESPONSABLE = 'responsable';

    /**
     * Nom de la table associée au modèle.
     */
    protected $table = 'utilisateurs';

    public const CREATED_AT = 'cree_le';

    public const UPDATED_AT = 'modifie_le';

    /**
     * Nom de la colonne du mot de passe.
     */
    public function getAuthPasswordName(): string
    {
        return 'mot_de_passe';
    }

    /**
     * Nom de la colonne du jeton de souvenir de moi.
     */
    public function getRememberTokenName(): string
    {
        return 'jeton_souvenir';
    }

    /**
     * Adresse utilisée pour la réinitialisation du mot de passe.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->courriel;
    }

    /**
     * Adresse utilisée pour l'envoi des notifications par courriel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->courriel;
    }

    /**
     * Le courriel de l'utilisateur est-il vérifié ?
     */
    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->courriel_verifie_le);
    }

    /**
     * Marquer le courriel de l'utilisateur comme vérifié.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'courriel_verifie_le' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Adresse utilisée pour la vérification du courriel.
     */
    public function getEmailForVerification(): string
    {
        return $this->courriel;
    }

    /**
     * Envoyer la notification de vérification du courriel.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Conversions automatiques des attributs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'courriel_verifie_le' => 'datetime',
            'mot_de_passe' => 'hashed',
            'role' => RoleUtilisateur::class,
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function dossiersResponsables(): HasMany
    {
        return $this->hasMany(Dossier::class, 'responsable_id');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(Historique::class, 'utilisateur_id');
    }

    public function estAdministrateur(): bool
    {
        return $this->role === RoleUtilisateur::Administrateur;
    }

    public function estChefDivision(): bool
    {
        return $this->role === RoleUtilisateur::ChefDivision;
    }

    public function estChefService(): bool
    {
        return $this->role === RoleUtilisateur::ChefHierarchique;
    }

    public function voitToutesLesDivisions(): bool
    {
        return $this->estAdministrateur() || $this->estChefService();
    }

    /**
     * L'utilisateur peut-il faire passer ce dossier au statut demandé ?
     *
     * - « Transmis » (départ du dossier) : réservé au chef de division ;
     * - « Archivé » : réservé au responsable du dossier (chacun archive ses propres dossiers) ;
     * - les autres statuts : toute personne de la division du dossier.
     * L'administrateur peut tout faire.
     */
    public function peutChangerStatut(Dossier $dossier, StatutDossier $nouveauStatut): bool
    {
        if ($this->estAdministrateur()) {
            return true;
        }

        if (! $this->voitDivision($dossier->division_id)) {
            return false;
        }

        return match ($nouveauStatut) {
            StatutDossier::Transmis => $this->estChefDivision(),
            StatutDossier::Archive => (int) $dossier->responsable_id === (int) $this->id,
            default => true,
        };
    }

    /**
     * L'utilisateur a-t-il accès aux données de la division indiquée ?
     */
    public function voitDivision(?int $divisionId): bool
    {
        if ($this->voitToutesLesDivisions()) {
            return true;
        }

        return $divisionId !== null && (int) $this->division_id === (int) $divisionId;
    }

    /**
     * Identifiants de division accessibles (null = toutes).
     *
     * @return array<int, int>|null
     */
    public function divisionsAccessibles(): ?array
    {
        if ($this->voitToutesLesDivisions()) {
            return null;
        }

        return $this->division_id ? [(int) $this->division_id] : [];
    }

    /**
     * Pôles naturels des divisions accessibles (null = tous).
     *
     * L'administrateur et le chef de service (hiérarchique) voient toutes les
     * divisions, donc tous les pôles. Un utilisateur rattaché à une division
     * ne voit que le pôle naturel de celle-ci : un compte Solde ne doit
     * jamais recevoir d'indicateur ou d'alerte du pôle Visa.
     *
     * @return array<int, string>|null
     */
    public function polesAccessibles(): ?array
    {
        $divisions = $this->divisionsAccessibles();

        if ($divisions === null) {
            return null;
        }

        if ($divisions === []) {
            return [];
        }

        return Division::query()
            ->whereIn('id', $divisions)
            ->get()
            ->map(fn (Division $division) => $division->poleNaturel()->value)
            ->unique()
            ->values()
            ->all();
    }

    public function nomRole(): string
    {
        return $this->role->label();
    }

    /**
     * Initiales affichées lorsque l'utilisateur n'a pas de photo.
     */
    public function initiales(): string
    {
        $mots = preg_split('/\s+/', trim((string) $this->nom), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $lettres = array_map(
            fn (string $mot) => mb_strtoupper(mb_substr($mot, 0, 1)),
            array_slice($mots, 0, 2)
        );

        return $lettres === [] ? '?' : implode('', $lettres);
    }

    /**
     * Adresse publique de la photo de profil, ou null.
     */
    public function urlPhoto(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    /**
     * L'utilisateur peut-il établir des bordereaux de création ?
     *
     * Ces bordereaux portent la matricule (IM) des agents : seule la division
     * qui tient la matricule les établit. L'administrateur les supervise
     * pour toutes les divisions.
     */
    public function peutGererBordereauxDeCreation(): bool
    {
        if ($this->estAdministrateur()) {
            return true;
        }

        return $this->division !== null && $this->division->chargeLesMatricules();
    }
}
