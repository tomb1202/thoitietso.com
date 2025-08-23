<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Genre;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {

                $menuAdmins = $this->readFiles();
                View::share('menuAdmins', $menuAdmins);

                $settings = Setting::all();

                $arrSettings = array();
                foreach ($settings as $item) {
                    $arrSettings[$item->key] = $item->value;
                }

                $version   = $arrSettings['version'] ?? 1.0;
                $agent  = new Agent();
                $isDesktop = $agent->isDesktop() ? true : false;

                $genres = Genre::where(['hidden' => 0])
                    ->where('slug', '!=', '')
                    ->get();

                View::share('genres', $genres);

                View::share('settings', $arrSettings);
                View::share('version', $version);
                View::share('isDesktop', $isDesktop);
            }
        } catch (Exception $e) {
            Log::error('Errr', ['err' => $e]);
        }
    }

    public function readFiles()
    {
        $file = base_path('resources/views/admin/menus.json');
        $jsonString = file_get_contents($file);
        $data = json_decode($jsonString, true);
        return $data;
    }
}
