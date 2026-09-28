<?php

namespace ErickComp\StackedComponents\Tests\Providers;

use ErickComp\StackedComponents\Content;
use ErickComp\StackedComponents\Css;
use ErickComp\StackedComponents\Div;
use ErickComp\StackedComponents\Js;
use ErickComp\StackedComponents\Providers\StackedComponentsProvider;
use ErickComp\StackedComponents\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class StackedComponentsProviderTest extends TestCase
{
    public function test_it_merges_the_default_config(): void
    {
        $this->assertSame(
            [
                'asset-function' => 'asset',
                'default-stack-js' => null,
                'default-stack-css' => null,
                'component-namespace' => false,
                'component-name-js' => 'js',
                'component-name-css' => 'css',
                'component-name-content' => 'stacked-content',
                'component-name-div' => 'stacked-div',
                'head-bottom-stack' => true,
            ],
            config('stacked-components'),
        );
    }

    public function test_the_config_is_publishable(): void
    {
        $paths = ServiceProvider::pathsToPublish(StackedComponentsProvider::class, 'stacked-components-config');

        $this->assertCount(1, $paths);
        $this->assertSame(config_path('stacked-components.php'), \reset($paths));
        $this->assertSame(\array_keys(config('stacked-components')), \array_keys(require \key($paths)));
    }

    public function test_it_registers_the_components_under_the_default_names(): void
    {
        $aliases = Blade::getClassComponentAliases();

        $this->assertSame(Js::class, $aliases['js'] ?? null);
        $this->assertSame(Css::class, $aliases['css'] ?? null);
        $this->assertSame(Content::class, $aliases['stacked-content'] ?? null);
        $this->assertSame(Div::class, $aliases['stacked-div'] ?? null);
    }

    public function test_it_registers_no_component_namespace_by_default(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to locate a class or view for component [stacked::js]');

        $this->renderBlade('<x-stacked::js src="/app.js" stack="s" />');
    }

    public function test_it_adds_a_has_stack_macro_to_the_view_factory(): void
    {
        $this->assertFalse(View::hasStack('never-pushed'));

        View::startPush('pushed', 'content');

        $this->assertTrue(View::hasStack('pushed'));
    }

    public function test_it_adds_a_head_bottom_stack_before_closing_head(): void
    {
        config(['stacked-components.asset-function' => false]);
        // The layout may have been compiled without the stack by another test
        $this->artisan('view:clear');
        $this->app['view']->addLocation(\dirname(__DIR__) . '/Fixtures/views');

        $html = view('head-page')->render();

        $this->assertMatchesRegularExpression(
            '#<title>Page</title>\s*<script src="/head.js"></script>\s*'
            . '<link rel="stylesheet" type="text/css" href="/head.css">\s*</head>#',
            $html,
        );
    }

    public function test_the_head_bottom_stack_is_compiled_into_templates_with_head(): void
    {
        $this->assertStringContainsString(
            "yieldPushContent('head_bottom')",
            Blade::compileString('<html><head></head></html>'),
        );
    }

    public function test_the_head_bottom_stack_renders_empty_when_nothing_is_pushed(): void
    {
        $html = $this->renderBlade('<html><head><title>x</title></head><body></body></html>');

        $this->assertMatchesRegularExpression('#^<html><head><title>x</title>\s*</head><body></body></html>$#', $html);
    }

    public function test_it_leaves_templates_without_head_untouched(): void
    {
        $this->assertSame('<p>no head here</p>', $this->renderBlade('<p>no head here</p>'));
    }

    public function test_compiling_a_template_with_head_does_not_leave_output_buffers_open(): void
    {
        $level = \ob_get_level();

        $this->renderBlade('<html><head></head><body></body></html>');

        $leaked = \ob_get_level() - $level;

        while (\ob_get_level() > $level) {
            \ob_end_clean();
        }

        $this->assertSame(0, $leaked);
    }
}
