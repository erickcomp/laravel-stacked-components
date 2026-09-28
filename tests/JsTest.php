<?php

namespace ErickComp\StackedComponents\Tests;

use Illuminate\View\ViewException;

class JsTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('stacked-components.asset-function', false);
    }

    public function test_it_pushes_a_script_tag_with_src(): void
    {
        $html = $this->renderBlade('<x-js src="/app.js" stack="scripts" />@stack(\'scripts\')');

        $this->assertSame('<script src="/app.js"></script>', $html);
    }

    public function test_it_renders_nothing_where_the_component_is_used(): void
    {
        $html = $this->renderBlade('before|<x-js src="/app.js" stack="scripts" />|after');

        $this->assertSame('before||after', $html);
    }

    public function test_it_pushes_inline_code(): void
    {
        $html = $this->renderBlade('<x-js stack="scripts">alert(1);</x-js>@stack(\'scripts\')');

        $this->assertSame("<script>" . PHP_EOL . "    alert(1);    " . PHP_EOL . "</script>", $html);
    }

    public function test_it_strips_script_tags_wrapping_inline_code(): void
    {
        $html = $this->renderBlade('<x-js stack="scripts"><script>alert(1);</script></x-js>@stack(\'scripts\')');

        $this->assertSame(1, \substr_count($html, '<script'));
        $this->assertSame(1, \substr_count($html, '</script>'));
        $this->assertStringContainsString('alert(1);', $html);
    }

    public function test_it_keeps_extra_attributes(): void
    {
        $html = $this->renderBlade('<x-js src="/app.js" stack="scripts" type="module" data-foo="bar" />@stack(\'scripts\')');

        $this->assertSame('<script src="/app.js" type="module" data-foo="bar"></script>', $html);
    }

    public function test_it_keeps_extra_attributes_on_inline_code(): void
    {
        $html = $this->renderBlade('<x-js stack="scripts" type="module">alert(1);</x-js>@stack(\'scripts\')');

        $this->assertStringStartsWith('<script type="module">', $html);
    }

    public function test_it_escapes_the_src(): void
    {
        $html = $this->renderBlade('<x-js :src="$src" stack="scripts" />@stack(\'scripts\')', ['src' => '/app.js?a=1&b="2"']);

        $this->assertSame('<script src="/app.js?a=1&amp;b=&quot;2&quot;"></script>', $html);
    }

    public function test_it_accepts_a_bound_src(): void
    {
        $html = $this->renderBlade('<x-js :src="$src" stack="scripts" />@stack(\'scripts\')', ['src' => '/bound.js']);

        $this->assertSame('<script src="/bound.js"></script>', $html);
    }

    public function test_it_uses_the_default_stack_from_config(): void
    {
        config(['stacked-components.default-stack-js' => 'js-stack']);

        $html = $this->renderBlade('<x-js src="/app.js" />@stack(\'js-stack\')');

        $this->assertSame('<script src="/app.js"></script>', $html);
    }

    public function test_the_stack_attribute_overrides_the_default_stack(): void
    {
        config(['stacked-components.default-stack-js' => 'js-stack']);

        $html = $this->renderBlade('<x-js src="/app.js" stack="other" />[@stack(\'js-stack\')][@stack(\'other\')]');

        $this->assertSame('[][<script src="/app.js"></script>' . PHP_EOL . ']', $html);
    }

    public function test_it_requires_a_stack(): void
    {
        config(['stacked-components.default-stack-js' => null]);

        try {
            $this->renderBlade('<x-js src="/app.js" />');
            $this->fail('Expected an exception for a missing stack');
        } catch (ViewException $e) {
            $this->assertInstanceOf(\LogicException::class, $e->getPrevious());
            $this->assertStringContainsString('stacked-components.default-stack-js', $e->getMessage());
            $this->assertStringContainsString('STACKED_COMPONENTS_DEFAULT_STACK_JS', $e->getMessage());
        }
    }

    public function test_it_pushes_the_same_script_only_once_by_default(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/app.js" stack="scripts" /><x-js src="/app.js" stack="scripts" />@stack(\'scripts\')',
        );

        $this->assertSame(1, \substr_count($html, '<script'));
    }

    public function test_it_pushes_different_scripts_in_order(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/a.js" stack="scripts" /><x-js src="/b.js" stack="scripts" />@stack(\'scripts\')',
        );

        $this->assertSame('<script src="/a.js"></script>' . PHP_EOL . '<script src="/b.js"></script>', $html);
    }

    public function test_once_false_pushes_the_same_script_again(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/app.js" stack="scripts" once="false" /><x-js src="/app.js" stack="scripts" once="false" />@stack(\'scripts\')',
        );

        $this->assertSame(2, \substr_count($html, '<script'));
    }

    public function test_bound_once_false_pushes_the_same_script_again(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/app.js" stack="scripts" :once="false" /><x-js src="/app.js" stack="scripts" :once="false" />@stack(\'scripts\')',
        );

        $this->assertSame(2, \substr_count($html, '<script'));
    }

    public function test_stack_prepend_false_pushes(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/b.js" stack="scripts" /><x-js src="/a.js" stack="scripts" stack-prepend="false" />@stack(\'scripts\')',
        );

        $this->assertSame('<script src="/b.js"></script>' . PHP_EOL . '<script src="/a.js"></script>', $html);
    }

    public function test_valueless_stack_prepend_prepends(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/b.js" stack="scripts" /><x-js src="/a.js" stack="scripts" stack-prepend />@stack(\'scripts\')',
        );

        $this->assertSame('<script src="/a.js"></script>' . PHP_EOL . '<script src="/b.js"></script>', $html);
    }

    public function test_stack_prepend_puts_the_script_first(): void
    {
        $html = $this->renderBlade(
            '<x-js src="/b.js" stack="scripts" /><x-js src="/a.js" stack="scripts" :stack-prepend="true" />@stack(\'scripts\')',
        );

        $this->assertSame('<script src="/a.js"></script>' . PHP_EOL . '<script src="/b.js"></script>', $html);
    }

    public function test_the_script_is_pushed_to_a_stack_rendered_earlier_in_a_layout(): void
    {
        $this->app['view']->addLocation(__DIR__ . '/Fixtures/views');

        $html = view('page')->render();

        $this->assertMatchesRegularExpression('#<header>\s*<script src="/page.js"></script>\s*</header>#', $html);
    }

    public function test_it_rejects_src_and_inline_code_together(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Cannot use src attribute and inline code at the same time');

        $this->renderBlade('<x-js src="/app.js" stack="scripts">alert(1);</x-js>');
    }
}
