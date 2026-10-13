<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
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

// Les mêmes pages existent en français (sans préfixe) et en anglais (/en),
// avec des adresses traduites. Les liens se génèrent avec lroute().
$publicRoutes = function (array $paths) {
    Route::get('/', HomeController::class)->name('home');
    Route::get($paths['projects'], [ProjectController::class, 'index'])->name('projects.index');
    Route::get($paths['projects'].'/{slug}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
    Route::get($paths['review'], [ReviewController::class, 'create'])->name('reviews.create');
};

Route::middleware('locale:fr')->group(fn () => $publicRoutes(['projects' => '/projets', 'review' => '/donner-mon-avis']));
Route::prefix('en')->name('en.')->middleware('locale:en')->group(fn () => $publicRoutes(['projects' => '/projects', 'review' => '/leave-a-review']));

Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::post('/avis', [ReviewController::class, 'store'])->middleware('throttle:reviews')->name('reviews.store');

// Mesure d'audience anonyme (page vue, puis durée passée sur la page).
Route::post('/t', [AnalyticsController::class, 'store'])->middleware('throttle:analytics')->name('analytics.store');
Route::post('/t/{uuid}', [AnalyticsController::class, 'duration'])->whereUuid('uuid')->middleware('throttle:analytics')->name('analytics.duration');
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

        Route::get('/services', Admin\Services\ServiceIndex::class)->name('services.index');
        Route::get('/services/nouveau', Admin\Services\ServiceForm::class)->name('services.create');
        Route::get('/services/{service}', Admin\Services\ServiceForm::class)->name('services.edit');

        Route::get('/statistiques', Admin\Analytics::class)->name('analytics');

        Route::get('/blog', Admin\Posts\PostIndex::class)->name('posts.index');
        Route::get('/blog/nouveau', Admin\Posts\PostForm::class)->name('posts.create');
        Route::get('/blog/{post}', Admin\Posts\PostForm::class)->name('posts.edit');

        Route::get('/parcours', Admin\CareerIndex::class)->name('career.index');
        Route::get('/recommandations', Admin\TestimonialIndex::class)->name('testimonials.index');

        Route::get('/messages', Admin\Messages\MessageIndex::class)->name('messages.index');
        Route::get('/messages/{message}', Admin\Messages\MessageShow::class)->name('messages.show');

        Route::get('/journal', Admin\ActivityLogIndex::class)->name('activity');
    });
