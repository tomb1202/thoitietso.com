<?php

use App\Http\Controllers\Admin\ActorController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdvController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DirectorController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MovieController as SysMovieController;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/sitemap.xml', function () {
    return response()->file(storage_path('app/public/sitemaps/sitemap.xml'), [
        'Content-Type' => 'application/xml'
    ]);
});

Route::middleware(['admin'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // account
        Route::get('/accounts', [AdminController::class, 'accounts'])->name('account.index');
        Route::post('/account/store', [AdminController::class, 'store'])->name('account.store');
        Route::get('/upgrading', [AdminController::class, 'upgrading'])->name('upgrading');

        // movie
        Route::prefix('movies')->name('movies.')->group(function () {
            Route::get('/', [SysMovieController::class, 'index'])->name('index');
            Route::get('/create', [SysMovieController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [SysMovieController::class, 'edit'])->name('edit');

            Route::put('/update/{id}', [SysMovieController::class, 'update'])->name('update');
            Route::post('/store', [SysMovieController::class, 'store'])->name('store');

            Route::delete('/delete', [SysMovieController::class, 'delete'])->name('delete');
            Route::delete('/delete-multiple', [SysMovieController::class, 'deleteMultiple'])->name('delete-multiple');
            Route::post('/active', [SysMovieController::class, 'active'])->name('active');
        });
        // genres
        Route::prefix('genres')->name('genres.')->group(function () {
            Route::get('/', [GenreController::class, 'index'])->name('index');
            Route::get('/create', [GenreController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [GenreController::class, 'edit'])->name('edit');

            Route::put('/update/{id}', [GenreController::class, 'update'])->name('update');
            Route::post('/store', [GenreController::class, 'store'])->name('store');
        });

        // countries
        Route::prefix('countries')->name('countries.')->group(function () {
            Route::get('/', [CountryController::class, 'index'])->name('index');
            Route::get('/create', [CountryController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [CountryController::class, 'edit'])->name('edit');

            Route::put('/update/{id}', [CountryController::class, 'update'])->name('update');
            Route::post('/store', [CountryController::class, 'store'])->name('store');
        });

        // actors
        Route::prefix('actors')->name('actors.')->group(function () {
            Route::get('/', [ActorController::class, 'index'])->name('index');
            Route::get('/create', [ActorController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [ActorController::class, 'edit'])->name('edit');

            Route::put('/update/{id}', [ActorController::class, 'update'])->name('update');
            Route::post('/store', [ActorController::class, 'store'])->name('store');
        });

        // directors
        Route::prefix('directors')->name('directors.')->group(function () {
            Route::get('/', [DirectorController::class, 'index'])->name('index');
            Route::get('/create', [DirectorController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [DirectorController::class, 'edit'])->name('edit');

            Route::put('/update/{id}', [DirectorController::class, 'update'])->name('update');
            Route::post('/store', [DirectorController::class, 'store'])->name('store');
        });

        // users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        //adv
        Route::get('/advs', [AdvController::class, 'index'])->name('adv.index');

        Route::prefix('adv')->name('adv.')->group(function () {
            Route::post('/store', [AdvController::class, 'store'])->name('store');
            Route::get('/banner', [AdvController::class, 'banner'])->name('banner');
            Route::get('/banner-script', [AdvController::class, 'bannerScript'])->name('banner-script');
            Route::get('/catfish', [AdvController::class, 'catfish'])->name('catfish');
            Route::get('/preload', [AdvController::class, 'preload'])->name('preload');
            Route::get('/pushjs', [AdvController::class, 'pushjs'])->name('pushjs');
            Route::get('/popupjs', [AdvController::class, 'popupjs'])->name('popupjs');
            Route::get('/text-link', [AdvController::class, 'textLink'])->name('text-link');
            Route::get('/header', [AdvController::class, 'header'])->name('header');
            Route::get('/bottom', [AdvController::class, 'bottom'])->name('bottom');
            Route::post('/refresh', [AdvController::class, 'refresh'])->name('refresh');
            Route::post('/active', [AdvController::class, 'active'])->name('active');
            Route::post('/delete/{id}', [AdvController::class, 'delete'])->name('delete');
        });

        // setting
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/store', [SettingController::class, 'store'])->name('settings.store');

        Route::get('/artisan-runner', function () {
            return view('admin.setting.artisan-runner');
        });

        Route::post('/artisan-runner', function (\Illuminate\Http\Request $request) {
            $phpPath = '/usr/bin/php';
            $command = $phpPath . ' artisan ' . escapeshellcmd($request->input('command'));

            $process = Symfony\Component\Process\Process::fromShellCommandline($command, base_path());
            $process->run();

            return response()->json([
                'success' => $process->isSuccessful(),
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);
        });
    });

Route::post('movies/change-poster', [SysMovieController::class, 'changePoster'])
    ->name('admin.movies.change-poster');


Route::get('/storage/uploads/advs/{path?}', function ($path) {
    $cacheKey = 'adv_' . $path;

    if (Cache::store('file')->has($cacheKey)) {
        $imageString = Cache::store('file')->get($cacheKey);
    } else {
        $imagePath = storage_path('app/public/uploads/advs/' . $path);

        if (!file_exists($imagePath)) {
            $imagePath = public_path('system/img/no-image.png');
        }

        $imageString = file_get_contents($imagePath);

        Cache::store('file')->put($cacheKey, $imageString, now()->addMinutes(60));
    }

    $response = response($imageString)->header('Content-Type', 'image/gif');
    $response->header('Cache-Control', 'public, max-age=31536000');
    return $response;
})->name('web.adv.banner');

Route::get('/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/login',  [AdminController::class, 'postLogin'])->name('admin.post.login');
Route::post('/logout',  [AdminController::class, 'logout'])->name('admin.logout');

Route::get('/genre/{slug}', [PageController::class, 'genre'])->name('site.genre');

Route::get('/watch/{slug}', [MovieController::class, 'watch'])->name('movie.watch');
Route::get('/', [HomeController::class, 'home'])->name('site.home');

Route::get('/search/{keyword}', [PageController::class, 'search'])->name('site.search');

// Route::get('/sitemap.xml', [SitemapController::class, 'index']);
