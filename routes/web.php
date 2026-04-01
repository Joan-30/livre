<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\FavoriController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfilLecteurController;
use App\Http\Controllers\TableauDeBordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return $user->role === \App\Models\User::ROLE_ADMIN 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('tableau-de-bord');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Routes protégées (utilisateur connecté)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // ── Déconnexion ───────────────────────────────────────────────────────
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Tableau de bord (suggestions personnalisées) ──────────────────
    Route::get('/tableau-de-bord', [TableauDeBordController::class, 'index'])
        ->name('tableau-de-bord');

    // ── Gestion des favoris (AJAX et Page) ────────────────────────────
    Route::get('/mes-favoris', [FavoriController::class, 'index'])->name('favoris.index');
    
    Route::prefix('favoris')->name('favoris.')->group(function () {
        Route::get('/', [FavoriController::class, 'liste'])
            ->name('liste');
        Route::post('/{livre}', [FavoriController::class, 'basculer'])
            ->name('basculer');
    });

    // ── Avis & Notations (AJAX) ───────────────────────────────────────
    Route::prefix('avis')->name('avis.')->group(function () {
        Route::get('/{livre}',    [AvisController::class, 'show'])->name('show');
        Route::post('/{livre}',   [AvisController::class, 'store'])->name('store');
    });

    // ── Profil lecteur (préférences d'ambiance) ───────────────────────
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/',  [ProfilLecteurController::class, 'edit'])
            ->name('edit');
        Route::put('/',  [ProfilLecteurController::class, 'update'])
            ->name('update');
    });

    // ── Notifications (AJAX) ─────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::post('/lues', [NotificationController::class, 'marquerToutCommeLu'])
            ->name('toutes-lues');
        Route::patch('/{id}', [NotificationController::class, 'marquerCommeLue'])
            ->name('lue');
    });

    // ── ESPACE ADMIN ──────────────────────────────────────────────────
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        
        // Tableau de bord principal Administrateur
        Route::get('/dashboard', function () {
            return view('admin.dashboard', ['utilisateur' => auth()->user()]);
        })->name('dashboard');

        // Gestion du Catalogue (Livres)
        Route::get('/livres', [\App\Http\Controllers\Admin\LivreController::class, 'index'])->name('livres.index');
        Route::get('/livres/create', [\App\Http\Controllers\Admin\LivreController::class, 'create'])->name('livres.create');
        Route::post('/livres', [\App\Http\Controllers\Admin\LivreController::class, 'store'])->name('livres.store');
        Route::get('/livres/{livre}/edit', [\App\Http\Controllers\Admin\LivreController::class, 'edit'])->name('livres.edit');
        Route::put('/livres/{livre}', [\App\Http\Controllers\Admin\LivreController::class, 'update'])->name('livres.update');
        Route::delete('/livres/{livre}', [\App\Http\Controllers\Admin\LivreController::class, 'destroy'])->name('livres.destroy');
        
    });

});
