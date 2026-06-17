<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Solución permanente para evitar el borrado de la base de datos local.
        // Si existe la caché de configuración, Laravel ignora el entorno 'testing' de phpunit.xml
        // y ejecuta RefreshDatabase contra tu MySQL local. Esto elimina la caché antes de inicializar.
        $configCache = __DIR__.'/../bootstrap/cache/config.php';
        if (file_exists($configCache)) {
            unlink($configCache);
        }

        parent::setUp();

        $this->withoutVite();
    }
}
