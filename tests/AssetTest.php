<?php

namespace ErickComp\StackedComponents\Tests;

use ErickComp\StackedComponents\Js;
use PHPUnit\Framework\Attributes\DataProvider;

class AssetTest extends TestCase
{
    public function test_it_recognizes_php_internal_functions(): void
    {
        $asset = $this->makeAsset();

        $this->assertTrue($asset->exposedIsPhpInternalFunction('strtoupper'));
        $this->assertTrue($asset->exposedIsPhpInternalFunction('md5'));
    }

    public function test_it_does_not_take_user_functions_or_methods_for_internal_ones(): void
    {
        $asset = $this->makeAsset();

        $this->assertTrue(\function_exists('asset'));
        $this->assertFalse($asset->exposedIsPhpInternalFunction('asset'));
        $this->assertFalse($asset->exposedIsPhpInternalFunction('not_a_function'));
        $this->assertFalse($asset->exposedIsPhpInternalFunction(''));
        $this->assertFalse($asset->exposedIsPhpInternalFunction('DateTime::createFromFormat'));
    }

    public function test_internal_functions_are_keyed_by_name(): void
    {
        $functions = $this->makeAsset()::exposedPhpInternalFunctions();

        $this->assertArrayHasKey('strtoupper', $functions);
        $this->assertCount(\count(\get_defined_functions(true)['internal']), $functions);
    }

    public function test_it_recognizes_global_functions_without_an_asset_parameter(): void
    {
        $asset = $this->makeAsset();

        $this->assertTrue($asset->exposedIsFunctionWithoutAssetParameter('asset'));
        $this->assertTrue($asset->exposedIsFunctionWithoutAssetParameter('ErickComp\\StackedComponents\\Tests\\Fixtures\\prefixed_asset'));
        $this->assertTrue($asset->exposedIsFunctionWithoutAssetParameter('strtoupper'));
    }

    public function test_it_leaves_functions_with_an_asset_parameter_and_methods_to_the_container(): void
    {
        $asset = $this->makeAsset();

        $this->assertFalse($asset->exposedIsFunctionWithoutAssetParameter('ErickComp\\StackedComponents\\Tests\\Fixtures\\versioned_asset'));
        $this->assertFalse($asset->exposedIsFunctionWithoutAssetParameter('not_a_function'));
        $this->assertFalse($asset->exposedIsFunctionWithoutAssetParameter(Fixtures\AssetFunctions::class . '::versioned'));
        $this->assertFalse($asset->exposedIsFunctionWithoutAssetParameter(Fixtures\AssetFunctions::class . '@cdn'));
    }

    public function test_global_function_without_asset_parameter_ignores_additional_params(): void
    {
        $asset = $this->makeAsset(assetFunction: 'ErickComp\\StackedComponents\\Tests\\Fixtures\\prefixed_asset');

        $this->assertSame('/prefixed/app.js', $asset->exposedGetAssetSrc('/app.js', ['suffix' => '?v=1']));
    }

    public function test_internal_function_ignores_additional_params(): void
    {
        $asset = $this->makeAsset(assetFunction: 'strtoupper');

        $this->assertSame('/APP.JS', $asset->exposedGetAssetSrc('/app.js', ['suffix' => '?v=1']));
    }

    public function test_additional_params_are_passed_to_the_asset_function(): void
    {
        $asset = $this->makeAsset(assetFunction: fn (string $asset, string $suffix) => $asset . $suffix);

        $this->assertSame('/app.js?v=1', $asset->exposedGetAssetSrc('/app.js', ['suffix' => '?v=1']));
    }

    public function test_the_src_is_bound_to_the_asset_parameter_whatever_its_position(): void
    {
        $asset = $this->makeAsset(assetFunction: fn (string $prefix, string $asset) => $prefix . $asset);

        $this->assertSame('/static/app.js', $asset->exposedGetAssetSrc('/app.js', ['prefix' => '/static']));
    }

    public function test_the_asset_function_argument_takes_precedence_over_config(): void
    {
        config(['stacked-components.asset-function' => 'strtolower']);

        $this->assertSame('/APP.JS', $this->makeAsset(assetFunction: 'strtoupper')->exposedGetAssetSrc('/app.js'));
    }

    public function test_false_asset_function_argument_disables_the_configured_one(): void
    {
        config(['stacked-components.asset-function' => 'strtoupper']);

        $this->assertSame('/app.js', $this->makeAsset(assetFunction: false)->exposedGetAssetSrc('/app.js'));
    }

    public function test_null_asset_function_argument_falls_back_to_config(): void
    {
        config(['stacked-components.asset-function' => 'strtoupper']);

        $this->assertSame('/APP.JS', $this->makeAsset(assetFunction: null)->exposedGetAssetSrc('/app.js'));
    }

    #[DataProvider('onceValues')]
    public function test_it_parses_once(string $once, bool $expected): void
    {
        $this->assertSame($expected, $this->makeAsset(once: $once)->once);
    }

    public static function onceValues(): array
    {
        return [
            'true' => ['true', true],
            'TRUE' => ['TRUE', true],
            '1' => ['1', true],
            'yes' => ['yes', true],
            'on' => ['on', true],
            'false' => ['false', false],
            'FALSE' => ['FALSE', false],
            '0' => ['0', false],
            'no' => ['no', false],
            'empty' => ['', false],
            'garbage' => ['garbage', false],
        ];
    }

    public function test_it_pushes_by_default(): void
    {
        $this->assertSame('push', $this->makeAsset()->stackOp);
    }

    public function test_it_prepends_when_asked(): void
    {
        $this->assertSame('prepend', $this->makeAsset(stackPrepend: true)->stackOp);
    }

    public function test_it_keeps_the_given_stack(): void
    {
        config(['stacked-components.default-stack-js' => 'default']);

        $this->assertSame('mine', $this->makeAsset(stack: 'mine')->stack);
        $this->assertSame('default', $this->makeAsset(stack: null)->stack);
    }

    public function test_it_throws_without_a_stack(): void
    {
        config(['stacked-components.default-stack-js' => null]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You must inform a stack or configure a default JS stack');

        $this->makeAsset(stack: null);
    }

    private function makeAsset(
        ?string $stack = 'scripts',
        string $once = 'true',
        bool $stackPrepend = false,
        null|string|array|object|false $assetFunction = null,
    ): Js {
        return new class('/app.js', $stack, $once, $stackPrepend, $assetFunction) extends Js {
            public function exposedIsPhpInternalFunction(string $functionName): bool
            {
                return $this->isPhpInternalFunction($functionName);
            }

            public function exposedIsFunctionWithoutAssetParameter(string $functionName): bool
            {
                return $this->isFunctionWithoutAssetParameter($functionName);
            }

            public function exposedGetAssetSrc(string $src, array $additionalParams = []): string
            {
                return $this->getAssetSrc($src, $additionalParams);
            }

            public static function exposedPhpInternalFunctions(): array
            {
                return static::phpInternalFunctions();
            }
        };
    }
}
