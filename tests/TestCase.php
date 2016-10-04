<?php namespace Test\Znck\Attach;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Znck\Attach\Providers\AttachServiceProvider;

class TestCase extends AbstractPackageTestCase
{
    use DatabaseMigrations;

    public function runDatabaseMigrations()
    {
        $this->artisan('migrate', ['--realpath' => realpath(__DIR__.'/../migrations/')]);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

    public function getServiceProviderClass($app)
    {
        return AttachServiceProvider::class;
    }

    public function setUp()
    {
        parent::setUp();

        self::afterApplicationCreated(function () {
            foreach ($this->app['filesystem']->files('', true) as $file) {
                $this->app['filesystem']->delete($file);
            }
        });
    }
}
