<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\BordereauCreationController;
use App\Http\Controllers\BordereauRecuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DocumentNumeriseController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\LieuController;
use App\Http\Controllers\PieceRequiseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceOrigineController;
use App\Http\Controllers\SoitTransmisVisaController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\TypeDossierController;
use App\Http\Controllers\TypePieceController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

// La racine renvoie vers l'authentification (et non la page d'accueil
// Laravel par défaut) lorsqu'aucune session n'est ouverte.
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('accueil');

// Pas de « verified » : application interne, les comptes sont créés par
// l'administration et aucune boîte de courriel n'est configurée.
Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('statistiques/dossiers', [StatistiqueController::class, 'dossiers'])->name('statistiques.dossiers');

    // Bordereaux reçus (entrées) et dossiers
    Route::resource('bordereaux', BordereauRecuController::class)
        ->parameters(['bordereaux' => 'bordereau']);
    Route::get('bordereaux/{bordereau}/imprimer', [BordereauRecuController::class, 'imprimer'])
        ->name('bordereaux.imprimer');
    Route::get('bordereaux/{bordereau}/soit-transmis-visa', SoitTransmisVisaController::class)
        ->name('bordereaux.soit-transmis-visa');

    Route::resource('dossiers', DossierController::class)
        ->except(['destroy'])
        ->parameters(['dossiers' => 'dossier']);

    Route::post('dossiers/{dossier}/statut', [DossierController::class, 'changerStatut'])
        ->name('dossiers.statut');

    Route::post('dossiers/{dossier}/pieces', [DossierController::class, 'basculerPiece'])
        ->name('dossiers.pieces');

    Route::post('dossiers/{dossier}/transmettre-solde', [DossierController::class, 'transmettreAuSolde'])
        ->name('dossiers.transmettre-solde');

    Route::post('dossiers/{dossier}/prendre', [DossierController::class, 'prendreEnCharge'])
        ->name('dossiers.prendre');

    Route::post('dossiers/{dossier}/valider-pieces', [DossierController::class, 'validerPieces'])
        ->name('dossiers.valider-pieces');

    // Saisie en masse : tous les agents d'un même bordereau papier d'un coup
    Route::get('saisie-multiple/{bordereau}', [DossierController::class, 'createPlusieurs'])
        ->name('dossiers.create-plusieurs');
    Route::post('saisie-multiple/{bordereau}', [DossierController::class, 'enregistrerEnMasse'])
        ->name('dossiers.en-masse');

    // Bordereaux de création (sorties / visa)
    // Le bordereau de création porte l'IM (matricule) des agents : il est
    // réservé à la division qui tient la matricule (Solde) et à
    // l'administrateur. La division Visa n'en produit pas.
    Route::middleware(['matricule'])->group(function () {
        Route::get('bordereaux/{bordereau}/generer-be', [BordereauCreationController::class, 'createDepuisBordereau'])
            ->name('bordereaux.generer-be');
        Route::post('bordereaux/{bordereau}/generer-be', [BordereauCreationController::class, 'preparerBeDepuisBordereau'])
            ->name('bordereaux.preparer-be');
        Route::get('bordereaux/{bordereau}/soit-transmis', [BordereauCreationController::class, 'soitTransmisDepuisBordereau'])
            ->name('bordereaux.soit-transmis');
        Route::resource('bordereaux-creation', BordereauCreationController::class)
            ->parameters(['bordereaux-creation' => 'bordereau']);
        Route::get('bordereaux-creation/{bordereau}/imprimer', [BordereauCreationController::class, 'imprimer'])
            ->name('bordereaux-creation.imprimer');
        Route::get('bordereaux-creation/{bordereau}/soit-transmis', [BordereauCreationController::class, 'soitTransmis'])
            ->name('bordereaux-creation.soit-transmis');
        Route::post('bordereaux-creation/{bordereau}/archiver', [BordereauCreationController::class, 'archiver'])
            ->name('bordereaux-creation.archiver');
        Route::post('bordereaux-creation/{bordereau}/desarchiver', [BordereauCreationController::class, 'desarchiver'])
            ->name('bordereaux-creation.desarchiver');
    });

    // Documents numérisés
    Route::post('numerisation/bordereaux/{bordereau}', [DocumentNumeriseController::class, 'stockerBordereau'])
        ->name('documents.bordereau.store');
    Route::post('numerisation/dossiers/{dossier}', [DocumentNumeriseController::class, 'stockerDossier'])
        ->name('documents.dossier.store');
    Route::get('documents/{document}/telecharger', [DocumentNumeriseController::class, 'telecharger'])
        ->name('documents.telecharger');
    Route::delete('documents/{document}', [DocumentNumeriseController::class, 'supprimer'])
        ->name('documents.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Paramétrage : réservé à l'administration
Route::middleware(['auth', 'role:administrateur'])->prefix('parametrage')->group(function () {
    Route::resource('divisions', DivisionController::class)
        ->parameters(['divisions' => 'division'])->except(['show']);
    Route::resource('lieux', LieuController::class)
        ->parameters(['lieux' => 'lieu'])->except(['show']);
    Route::resource('destinations', DestinationController::class)
        ->parameters(['destinations' => 'destination'])->except(['show']);
    Route::resource('services-origine', ServiceOrigineController::class)
        ->parameters(['services-origine' => 'service'])
        ->names('services-origine')->except(['show']);
    Route::resource('types-dossiers', TypeDossierController::class)
        ->parameters(['types-dossiers' => 'typeDossier'])
        ->names('types-dossiers')->except(['show']);
    Route::resource('types-pieces', TypePieceController::class)
        ->parameters(['types-pieces' => 'typePiece'])
        ->names('types-pieces')->except(['show']);
    Route::resource('pieces-requises', PieceRequiseController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['pieces-requises' => 'pieceRequise'])
        ->names('pieces-requises');
    Route::resource('agents', AgentController::class)
        ->parameters(['agents' => 'agent'])->except(['show']);
    Route::resource('utilisateurs', UtilisateurController::class)
        ->parameters(['utilisateurs' => 'user'])
        ->names('utilisateurs')->except(['show']);
});

require __DIR__.'/auth.php';
