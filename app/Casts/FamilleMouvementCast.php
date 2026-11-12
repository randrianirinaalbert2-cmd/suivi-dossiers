<?php

namespace App\Casts;

use App\Enums\FamilleMouvement;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast tolérant de la famille de mouvement.
 *
 * Contrairement au cast enum natif, une valeur stockée inconnue (données
 * historiques, saisie externe) renvoie `null` au lieu de lever une exception
 * et de faire échouer toute la page.
 *
 * @implements CastsAttributes<FamilleMouvement|null, FamilleMouvement|string|null>
 */
class FamilleMouvementCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?FamilleMouvement
    {
        if ($value === null || $value === '') {
            return null;
        }

        return FamilleMouvement::tryFrom($value) ?? $this->depuisLibelle($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return match (true) {
            $value === null, $value === '' => null,
            $value instanceof FamilleMouvement => $value->value,
            default => (FamilleMouvement::tryFrom((string) $value) ?? $this->depuisLibelle((string) $value))?->value,
        };
    }

    private function depuisLibelle(string $valeur): ?FamilleMouvement
    {
        foreach (FamilleMouvement::cases() as $famille) {
            if ($famille->label() === $valeur) {
                return $famille;
            }
        }

        return null;
    }
}
