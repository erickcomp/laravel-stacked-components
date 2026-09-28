<?php

namespace ErickComp\StackedComponents\Tests\Providers;

use ErickComp\StackedComponents\Tests\TestCase;

class CustomComponentNamespaceTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('stacked-components.component-namespace', 'assets');
        $app['config']->set('stacked-components.asset-function', false);
    }

    public function test_it_registers_the_configured_namespace(): void
    {
        $html = $this->renderBlade('<x-assets::css src="/app.css" stack="s" />@stack(\'s\')');

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/app.css">', $html);
    }
}
