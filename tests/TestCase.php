<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $this->bloquearRefreshDatabase();
        $this->validarBaseSegura($app);

        return $app;
    }

    private function bloquearRefreshDatabase(): void
    {
        if (isset($this->traitsUsedByTest[RefreshDatabase::class])) {
            throw new RuntimeException('RefreshDatabase esta bloqueado en este repo. Usa DatabaseTransactions.');
        }
    }

    private function validarBaseSegura(Application $app): void
    {
        $database = (string) $app['config']->get('database.connections.'.config('database.default').'.database', '');

        if (! preg_match('/(test|testing|pruebas)/i', $database)) {
            throw new RuntimeException("Tests bloqueados: la base [{$database}] no parece ser de pruebas.");
        }
    }
}
