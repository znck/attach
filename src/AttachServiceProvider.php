<?php namespace Znck\Attach;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Znck\Attach\Contracts\Attachment as AttachmentContract;
use Znck\Attach\Contracts\Downloader as DownloaderContract;
use Znck\Attach\Contracts\Finder as FinderContract;
use Znck\Attach\Contracts\Storage as StorageContract;
use Znck\Attach\Contracts\Uploader as UploaderContract;
use Znck\Attach\Util\Finder;

class AttachServiceProvider extends ServiceProvider
{
    protected $configPath = __DIR__.'/../config/attach.php';

    public function boot()
    {
        $this->publishes([$this->configPath => config_path('attach.php')], 'config');
        $this->publishes([__DIR__.'/../migrations/' => database_path('migrations')], 'migrations');
        $this->loadMigrationsFrom(__DIR__.'/../migrations/');
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
        $this->app->bind(AttachmentContract::class, $this->getConfig('model'));
        $this->app->bind(DownloaderContract::class, Downloader::class);
        $this->app->bind(FinderContract::class, Finder::class);
        $this->app->bind(StorageContract::class, $this->app['filesystem.disk']);
        $this->app->bind(UploaderContract::class, Uploader::class);
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
