<?php

namespace ErickComp\StackedComponents\Tests;

use ErickComp\StackedComponents\Providers\StackedComponentsProvider;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [StackedComponentsProvider::class];
    }

    protected function renderBlade(string $template, array $data = []): string
    {
        return \trim(Blade::render($template, $data, deleteCachedView: true));
    }
}
