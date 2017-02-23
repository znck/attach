<?php namespace Znck\Attach\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Znck\Attach\Builder;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\FinderContract;
use Znck\Attach\Contracts\SignerContract;
use Znck\Attach\Contracts\UploaderContract;
use Znck\Attach\Contracts\UrlGeneratorContract;
use Znck\Attach\DownloaderFactory;
use Znck\Attach\Finder;
use Znck\Attach\Processors\Resize;
use Znck\Attach\Signer;
use Znck\Attach\Uploader;
use Znck\Attach\UrlGenerator;

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

        $this->app->bind(AttachmentContract::class, $this->getConfig('model'));
        $this->app->bind(UploaderContract::class, Uploader::class);
        $this->app->bind(UrlGeneratorContract::class, UrlGenerator::class);

        $this->app->singleton(FinderContract::class, function () {
            return new Finder(
                $this->app['filesystem'],
                new DownloaderFactory(),
                config('filesystems.disks'),
                config('filesystems.default')
            );
        });
        $this->app->singleton(SignerContract::class, function () {
            return new Signer($this->getConfig('signing.key'), $this->getConfig('signing.expiry'));
        });

        Builder::register('resize', Resize::class);
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
            FinderContract::class,
            SignerContract::class,
            UploaderContract::class,
            UrlGeneratorContract::class,
        ];
    }
}
