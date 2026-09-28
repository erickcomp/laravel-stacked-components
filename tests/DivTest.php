<?php

namespace ErickComp\StackedComponents\Tests;

class DivTest extends TestCase
{
    public function test_it_pushes_a_div_with_its_slot(): void
    {
        $html = $this->renderBlade('<x-stacked-div stack="modals">Hello</x-stacked-div>[@stack(\'modals\')]');

        $this->assertSame('[<div>' . PHP_EOL . 'Hello' . PHP_EOL . '</div>' . PHP_EOL . ']', $html);
    }

    public function test_it_keeps_attributes(): void
    {
        $html = $this->renderBlade('<x-stacked-div stack="modals" id="modal" class="modal">Hello</x-stacked-div>@stack(\'modals\')');

        $this->assertMatchesRegularExpression('#^<div [^>]*\bid="modal"#', $html);
        $this->assertMatchesRegularExpression('#^<div [^>]*\bclass="modal"#', $html);
    }

    public function test_src_is_an_ordinary_attribute(): void
    {
        config(['stacked-components.asset-function' => 'strtoupper']);

        $html = $this->renderBlade('<x-stacked-div stack="modals" src="/image.png">Hello</x-stacked-div>@stack(\'modals\')');

        $this->assertStringStartsWith('<div src="/image.png">', $html);
    }

    public function test_it_renders_nothing_where_the_component_is_used(): void
    {
        $html = $this->renderBlade('before|<x-stacked-div stack="modals">Hello</x-stacked-div>|after');

        $this->assertSame('before||after', $html);
    }

    public function test_it_pushes_the_same_div_again_by_default(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-div stack="modals">Hello</x-stacked-div><x-stacked-div stack="modals">Hello</x-stacked-div>@stack(\'modals\')',
        );

        $this->assertSame(2, \substr_count($html, '<div>'));
    }

    public function test_once_true_pushes_the_same_div_only_once(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-div stack="modals" once="true">Hello</x-stacked-div><x-stacked-div stack="modals" once="true">Hello</x-stacked-div>@stack(\'modals\')',
        );

        $this->assertSame(1, \substr_count($html, '<div>'));
    }

    public function test_stack_prepend_puts_the_div_first(): void
    {
        $html = $this->renderBlade(
            '<x-stacked-div stack="modals" id="b">b</x-stacked-div><x-stacked-div stack="modals" id="a" :stack-prepend="true">a</x-stacked-div>@stack(\'modals\')',
        );

        $this->assertLessThan(\strpos($html, 'id="b"'), \strpos($html, 'id="a"'));
    }
}
