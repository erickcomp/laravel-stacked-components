<?php

namespace ErickComp\StackedComponents\Tests\Providers;

use ErickComp\StackedComponents\Tests\TestCase;

class ComponentNamespaceTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('stacked-components.component-namespace', true);
        $app['config']->set('stacked-components.asset-function', false);
    }

    public function test_true_registers_the_stacked_namespace(): void
    {
        $html = $this->renderBlade('<x-stacked::js src="/app.js" stack="s" />@stack(\'s\')');

        $this->assertSame('<script src="/app.js"></script>', $html);
    }

    public function test_every_component_is_in_the_namespace(): void
    {
        $html = $this->renderBlade(
            '<x-stacked::css src="/app.css" stack="s" />'
            . '<x-stacked::content stack="s">content</x-stacked::content>'
            . '<x-stacked::div stack="s">div</x-stacked::div>'
            . '@stack(\'s\')',
        );

        $this->assertStringContainsString('<link rel="stylesheet" type="text/css" href="/app.css">', $html);
        $this->assertStringContainsString('content', $html);
        $this->assertStringContainsString('<div>' . PHP_EOL . 'div' . PHP_EOL . '</div>', $html);
    }

    public function test_the_plain_names_keep_working(): void
    {
        $html = $this->renderBlade('<x-js src="/app.js" stack="s" />@stack(\'s\')');

        $this->assertSame('<script src="/app.js"></script>', $html);
    }
}
