<?php

namespace KluseG\LaravelWallets;

use Illuminate\Support\ServiceProvider;

use KluseG\LaravelWallets\Models\WalletTransaction;
use KluseG\LaravelWallets\Observers\WalletTransactionObserver;

class LaravelWalletsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'wallets');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        WalletTransaction::observe(WalletTransactionObserver::class);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravelwallets.php', 'wallets');

        // Register the service the package provides.
        $this->app->singleton('laravelwallets', function ($app) {
            return new LaravelWallets;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravelwallets'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravelwallets.php' => config_path('laravelwallets.php'),
        ], 'laravelwallets.config');

        // Publishing the translation files.
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/wallets'),
        ], 'laravelwallets.views');
    }
}
