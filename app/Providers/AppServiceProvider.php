<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $publicStorage = public_path('storage');

        if (!\Illuminate\Support\Facades\File::exists($publicStorage)) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                logger()->info('Symlink public/storage criado com sucesso.');
            } catch (\Exception $e) {
                logger()->error('Erro ao criar o symlink: ' . $e->getMessage());
            }
        }
    }
}
