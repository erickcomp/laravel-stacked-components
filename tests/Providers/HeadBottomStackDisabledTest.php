<?php

namespace ErickComp\StackedComponents\Tests\Providers;

use ErickComp\StackedComponents\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class HeadBottomStackDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('stacked-components.head-bottom-stack', false);
        $app['config']->set('stacked-components.asset-function', false);
    }

    public function test_templates_with_head_are_left_untouched(): void
    {
        $this->assertStringNotContainsString('head_bottom', Blade::compileString('<html><head></head></html>'));
    }

    public function test_pushes_to_head_bottom_are_not_rendered(): void
    {
        // The layout may have been compiled with the stack by another test
        $this->artisan('view:clear');
        $this->app['view']->addLocation(\dirname(__DIR__) . '/Fixtures/views');

        $html = view('head-page')->render();

        $this->assertStringNotContainsString('/head.js', $html);
        $this->assertStringContainsString('<p>content</p>', $html);
    }

    public function test_the_has_stack_macro_is_still_added(): void
    {
        $this->assertTrue(View::hasMacro('hasStack'));
    }
}
