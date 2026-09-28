<?php

namespace ErickComp\StackedComponents\Tests;

class ContentTest extends TestCase
{
    public function test_it_pushes_its_slot_to_the_stack(): void
    {
        $html = $this->renderBlade('<x-stacked-content stack="scripts"><script src="/app.js"></script></x-stacked-content>[@stack(\'scripts\')]');

        $this->assertSame('[<script src="/app.js"></script>]', $html);
    }

    public function test_it_renders_nothing_where_the_component_is_used(): void
    {
        $html = $this->renderBlade('before|<x-stacked-content stack="scripts">foo</x-stacked-content>|after');

        $this->assertSame('before||after', $html);
    }

    public function test_it_pushes_the_same_content_again_by_default(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-content stack="s">foo</x-stacked-content><x-stacked-content stack="s">foo</x-stacked-content>[@stack(\'s\')]',
        );

        $this->assertSame('[foofoo]', $html);
    }

    public function test_once_true_pushes_the_same_content_only_once(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-content stack="s" once="true">foo</x-stacked-content><x-stacked-content stack="s" once="true">foo</x-stacked-content>[@stack(\'s\')]',
        );

        $this->assertSame('[foo]', $html);
    }

    public function test_valueless_once_pushes_the_same_content_only_once(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-content stack="s" once>foo</x-stacked-content><x-stacked-content stack="s" once>foo</x-stacked-content>[@stack(\'s\')]',
        );

        $this->assertSame('[foo]', $html);
    }

    public function test_stack_prepend_puts_the_content_first(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-content stack="s">b</x-stacked-content><x-stacked-content stack="s" :stack-prepend="true">a</x-stacked-content>[@stack(\'s\')]',
        );

        $this->assertSame('[ab]', $html);
    }

    public function test_it_does_not_render_attributes(): void
    {
        $html = $this->renderBlade('<x-stacked-content stack="s" class="foo">bar</x-stacked-content>[@stack(\'s\')]');

        $this->assertSame('[bar]', $html);
    }

    public function test_src_is_ignored_like_any_other_attribute(): void
    {
        $html = $this->renderBlade('<x-stacked-content stack="s" src="/app.js">foo</x-stacked-content>[@stack(\'s\')]');

        $this->assertSame('[foo]', $html);
    }

    public function test_it_renders_blade_inside_the_slot(): void
    {
        $html = $this->renderBlade('<x-stacked-content stack="s">{{ $name }}</x-stacked-content>[@stack(\'s\')]', ['name' => '<b>']);

        $this->assertSame('[&lt;b&gt;]', $html);
    }
}
