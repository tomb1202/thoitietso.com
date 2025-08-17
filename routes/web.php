<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
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


// 
Route::get('/', [TestController::class, 'index'])->name('index');
Route::get('/temp', [TestController::class, 'temp'])->name('temp');