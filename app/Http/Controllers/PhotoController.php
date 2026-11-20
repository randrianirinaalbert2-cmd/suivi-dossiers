<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    /**
     * Servir une photo de profil sans exposer le bucket privé.
     *
     * La route est protégée par le middleware « auth » : une personne non
     * connectée ne peut pas lire les photos, même avec l'adresse directe.
     */
    public function afficher(User $user): StreamedResponse
    {
        abort_unless($user->photo !== null, 404);
        abort_unless(Storage::disk('public')->exists($user->photo), 404);

        return Storage::disk('public')->response($user->photo);
    }
}
