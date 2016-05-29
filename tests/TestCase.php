<?php namespace Test\Znck\Attach;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
}
