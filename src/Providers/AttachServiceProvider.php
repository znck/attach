<?php namespace Znck\Attach\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Znck\Attach\Contracts\Manager as ManagerInterface;
use Znck\Attach\Contracts\UriGenerator as UriGeneratorInterface;
use Znck\Attach\Contracts\UriSigner as TokenGeneratorInterface;
use Znck\Attach\Http\Controllers\AttachController;
use Znck\Attach\UriGenerator;

class AttachServiceProvider extends ServiceProvider
{
    protected $configPath = __DIR__.'/../../config/attach.php';

    public function boot()
    {
        $this->publishes([$this->configPath => config_path('attach.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath, 'attach');
        $this->registerManager();
        $this->registerUriGenerator();
        $this->registerTokenGenerator();
        $this->registerRoutes($this->app['router']);
    }

    public function getConfig($name, $default = null, $prefix = 'attach.')
    {
        return $this->app['config']->get($prefix.$name, $default);
    }

    /**
     * @param Router $router
     */
    private function registerRoutes(Router $router)
    {
        if (! $this->app->routesAreCached()) {
            if (! $router->has($route = $this->getConfig('url.name'))) {
                $router->get(
                    '/media/{filename}/{manipulation?}',
                    ['as' => $route, 'uses' => AttachController::class.'@get']
                );
            }
        }
    }

    private function registerManager()
    {
        $this->app->bind(
            ManagerInterface::class,
            function () {
                return $this->app->make(
                    $this->getConfig('manager')
                );
            }
        );
    }

    private function registerUriGenerator()
    {
        $this->app->bind(UriGeneratorInterface::class, $this->getConfig('uri.generator'));
        $this->app->singleton(
            UriGenerator::class,
            function () {
                return new UriGenerator(
                    $this->app['url'],
                    $this->getConfig('uri.name'),
                    $this->getConfig('uri.parameters')
                );
            }
        );
    }

    private function registerTokenGenerator()
    {
        $this->app->singleton(
            TokenGeneratorInterface::class,
            function () {
                return $this->app->make(
                    $this->getConfig('token.generator')
                );
            }
        );
    }

    public function provides()
    {
        return [TokenGeneratorInterface::class, UriGeneratorInterface::class, ManagerInterface::class];
    }
}
