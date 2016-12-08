<?php namespace Znck\Attach;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

/**
 * @property \Illuminate\Foundation\Application $app
 */
class AttachServiceProvider extends ServiceProvider
{
    public static $runMigrations = true;

    protected $configPath = __DIR__.'/../config/attach.php';

    public function boot()
    {
        $this->publishes([$this->configPath => config_path('attach.php')], 'attach-config');
        if ($this->app->runningInConsole()) {
            if (self::$runMigrations) {
                $this->loadMigrationsFrom(__DIR__.'/../migrations/');

                return;
            }

            $this->publishes([__DIR__.'/../migrations/' => database_path('migrations')], 'attach-migrations');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath, 'attach');
        $this->registerRoutes($this->app['router']);

        $this->app->bind(Contracts\Attachment::class, $this->getConfig('model'));
        $this->app->bind(Contracts\Downloader::class, Downloader::class);
        $this->app->bind(Contracts\Uploader::class, Uploader::class);
        $this->app->bind(Contracts\UrlGenerator::class, Util\Url::class);


        $this->app->singleton(Contracts\Finder::class, function () {
            $finder = new Util\Finder;
            $finder->setStorage($this->app['filesystem']->disk());

            return $finder;
        });
        $this->app->singleton(Contracts\Signer::class, function () {
            return new Util\Signer($this->getConfig('signing.key'));
        });

        Builder::register('resize', Processors\Resize::class);
    }

    /**
     * @param Router $router
     */
    public function registerRoutes(Router $router)
    {
        if (! $this->app->routesAreCached()) {
            $route = $this->getConfig('route');

            if (is_array($route)) {
                $router->get($route['_path'], array_except($route, '_path'));
            }
        }
    }

    public function getConfig($name, $default = null, $prefix = 'attach.')
    {
        return $this->app['config']->get($prefix.$name, $default);
    }

    public function provides()
    {
        return [
            AttachmentContract::class,
            DownloaderContract::class,
            FinderContract::class,
            StorageContract::class,
            UploaderContract::class,
        ];
    }
}
