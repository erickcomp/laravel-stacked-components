<?php

namespace ErickComp\StackedComponents\Tests\Providers;

use ErickComp\StackedComponents\Tests\TestCase;

class CustomComponentNamesTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('stacked-components.component-name-js', 'script');
        $app['config']->set('stacked-components.component-name-css', 'stylesheet');
        $app['config']->set('stacked-components.component-name-content', 'push');
        $app['config']->set('stacked-components.component-name-div', 'push-div');
        $app['config']->set('stacked-components.asset-function', false);
    }

    public function test_it_keeps_the_other_defaults(): void
    {
        $this->assertFalse(config('stacked-components.asset-function'));
        $this->assertFalse(config('stacked-components.component-namespace'));
        $this->assertNull(config('stacked-components.default-stack-js'));
    }

    public function test_js_uses_the_configured_name(): void
    {
        $html = $this->renderBlade('<x-script src="/app.js" stack="s" />@stack(\'s\')');

        $this->assertSame('<script src="/app.js"></script>', $html);
    }

    public function test_css_uses_the_configured_name(): void
    {
        $html = $this->renderBlade('<x-stylesheet src="/app.css" stack="s" />@stack(\'s\')');

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/app.css">', $html);
    }

    public function test_content_uses_the_configured_name(): void
    {
        $html = $this->renderBlade('<x-push stack="s">foo</x-push>[@stack(\'s\')]');

        $this->assertSame('[foo]', $html);
    }

    public function test_div_uses_the_configured_name(): void
    {
        $html = $this->renderBlade('<x-push-div stack="s">foo</x-push-div>@stack(\'s\')');

        $this->assertStringStartsWith('<div>', $html);
    }

    public function test_the_default_names_are_not_registered(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to locate a class or view for component [js]');

        $this->renderBlade('<x-js src="/app.js" stack="s" />');
    }
}
