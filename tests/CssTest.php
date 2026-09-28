<?php

namespace ErickComp\StackedComponents\Tests;

use Illuminate\View\ViewException;

class CssTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('stacked-components.asset-function', false);
    }

    public function test_it_pushes_a_link_tag_with_href(): void
    {
        $html = $this->renderBlade('<x-css src="/app.css" stack="styles" />@stack(\'styles\')');

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/app.css">', $html);
    }

    public function test_it_renders_nothing_where_the_component_is_used(): void
    {
        $html = $this->renderBlade('before|<x-css src="/app.css" stack="styles" />|after');

        $this->assertSame('before||after', $html);
    }

    public function test_it_pushes_inline_css(): void
    {
        $html = $this->renderBlade('<x-css stack="styles">body { color: red; }</x-css>@stack(\'styles\')');

        $this->assertSame(
            '<style type="text/css">' . PHP_EOL . '    body { color: red; }    ' . PHP_EOL . '</style>',
            $html,
        );
    }

    public function test_it_strips_style_tags_wrapping_inline_css(): void
    {
        $html = $this->renderBlade('<x-css stack="styles"><style>body { color: red; }</style></x-css>@stack(\'styles\')');

        $this->assertSame(1, \substr_count($html, '<style'));
        $this->assertSame(1, \substr_count($html, '</style>'));
        $this->assertStringContainsString('body { color: red; }', $html);
    }

    public function test_it_keeps_extra_attributes(): void
    {
        $html = $this->renderBlade('<x-css src="/print.css" stack="styles" media="print" />@stack(\'styles\')');

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/print.css" media="print">', $html);
    }

    public function test_attributes_override_the_defaults(): void
    {
        $html = $this->renderBlade('<x-css src="/app.css" stack="styles" rel="preload" type="text/plain" />@stack(\'styles\')');

        $this->assertSame('<link rel="preload" type="text/plain" href="/app.css">', $html);
    }

    public function test_it_escapes_the_href(): void
    {
        $html = $this->renderBlade('<x-css :src="$src" stack="styles" />@stack(\'styles\')', ['src' => '/app.css?a=1&b="2"']);

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/app.css?a=1&amp;b=&quot;2&quot;">', $html);
    }

    public function test_it_uses_the_default_stack_from_config(): void
    {
        config(['stacked-components.default-stack-css' => 'css-stack']);

        $html = $this->renderBlade('<x-css src="/app.css" />@stack(\'css-stack\')');

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/app.css">', $html);
    }

    public function test_it_does_not_use_the_js_default_stack(): void
    {
        config([
            'stacked-components.default-stack-js' => 'js-stack',
            'stacked-components.default-stack-css' => null,
        ]);

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('stacked-components.default-stack-css');

        $this->renderBlade('<x-css src="/app.css" />');
    }

    public function test_it_requires_a_stack(): void
    {
        config(['stacked-components.default-stack-css' => null]);

        try {
            $this->renderBlade('<x-css src="/app.css" />');
            $this->fail('Expected an exception for a missing stack');
        } catch (ViewException $e) {
            $this->assertInstanceOf(\LogicException::class, $e->getPrevious());
            $this->assertStringContainsString('CSS stack', $e->getMessage());
            $this->assertStringContainsString('STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_CSS', $e->getMessage());
        }
    }

    public function test_it_pushes_the_same_stylesheet_only_once_by_default(): void
    {
        $html = $this->renderBlade(
            '<x-css src="/app.css" stack="styles" /><x-css src="/app.css" stack="styles" />@stack(\'styles\')',
        );

        $this->assertSame(1, \substr_count($html, '<link'));
    }

    public function test_once_false_pushes_the_same_stylesheet_again(): void
    {
        $html = $this->renderBlade(
            '<x-css src="/app.css" stack="styles" once="false" /><x-css src="/app.css" stack="styles" once="false" />@stack(\'styles\')',
        );

        $this->assertSame(2, \substr_count($html, '<link'));
    }

    public function test_stack_prepend_puts_the_stylesheet_first(): void
    {
        $html = $this->renderBlade(
            '<x-css src="/b.css" stack="styles" /><x-css src="/a.css" stack="styles" :stack-prepend="true" />@stack(\'styles\')',
        );

        $this->assertSame(
            '<link rel="stylesheet" type="text/css" href="/a.css">' . PHP_EOL
            . '<link rel="stylesheet" type="text/css" href="/b.css">',
            $html,
        );
    }

    public function test_js_and_css_share_a_stack(): void
    {
        $html = $this->renderBlade(
            '<x-css src="/app.css" stack="assets" /><x-js src="/app.js" stack="assets" />@stack(\'assets\')',
        );

        $this->assertSame(
            '<link rel="stylesheet" type="text/css" href="/app.css">' . PHP_EOL . '<script src="/app.js"></script>',
            $html,
        );
    }
}
