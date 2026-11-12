<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case Administrateur = 'administrateur';
    case ChefDivision = 'chef_division';
    case Responsable = 'responsable';
    case ChefBureau = 'chef_bureau';
    case Verificateur = 'verificateur';
    case Liquidateur = 'liquidateur';
    case ChefHierarchique = 'chef_hierarchique';
    case ResponsableControle = 'responsable_controle';

    public function label(): string
    {
        return match ($this) {
            self::Administrateur => 'Administrateur',
            self::ChefDivision => 'Chef de division',
            self::Responsable => 'Comptable',
            self::ChefBureau => 'Chef de bureau',
            self::Verificateur => 'Vérificateur',
            self::Liquidateur => 'Liquidateur',
            self::ChefHierarchique => 'Chef de service (hiérarchique)',
            self::ResponsableControle => 'Responsable de contrôle',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Administrateur => 'Accès complet à l\'application et au paramétrage',
            self::ChefDivision => 'Suit les dossiers de sa division et valide les traitements',
            self::Responsable => 'Comptable : traite les dossiers reçus par sa division',
            self::ChefBureau => 'Chef de bureau',
            self::Verificateur => 'Vérificateur',
            self::Liquidateur => 'Liquidateur',
            self::ChefHierarchique => 'Chef de service : dernière validation / vérification',
            self::ResponsableControle => 'Responsable de contrôle',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function valeurs(): array
    {
        return array_column(self::cases(), 'value');
    }
}
