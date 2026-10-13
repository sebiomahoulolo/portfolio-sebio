<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Livewire\Admin;
use App\Livewire\Auth;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site public
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/projets', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projets/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
// « /cv » est déjà un dossier de public/ (ancien CV) : la route utilise une autre adresse.
Route::get('/telecharger-cv', CvController::class)->name('cv');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/*
|--------------------------------------------------------------------------
| Authentification (pas d'inscription publique)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/admin/connexion', Auth\Login::class)->name('login');
    Route::get('/admin/mot-de-passe-oublie', Auth\ForgotPassword::class)->name('password.request');
    Route::get('/admin/reinitialiser/{token}', Auth\ResetPassword::class)->name('password.reset');
});

Route::post('/admin/deconnexion', LogoutController::class)->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', AuthenticateSession::class, EnsureUserIsActive::class, 'can:access-admin'])
    ->group(function () {
        Route::get('/', Admin\Dashboard::class)->name('dashboard');
        Route::get('/compte', Admin\Account::class)->name('account');
        Route::get('/profil', Admin\ProfileEditor::class)->name('profile.edit');

        Route::get('/projets', Admin\Projects\ProjectIndex::class)->name('projects.index');
        Route::get('/projets/nouveau', Admin\Projects\ProjectForm::class)->name('projects.create');
        Route::get('/projets/{project}', Admin\Projects\ProjectForm::class)->name('projects.edit');

        Route::get('/technologies', Admin\Technologies\TechnologyIndex::class)->name('technologies.index');

        Route::get('/messages', Admin\Messages\MessageIndex::class)->name('messages.index');
        Route::get('/messages/{message}', Admin\Messages\MessageShow::class)->name('messages.show');

        Route::get('/journal', Admin\ActivityLogIndex::class)->name('activity');
    });
