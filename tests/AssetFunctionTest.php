<?php

namespace ErickComp\StackedComponents\Tests;

use ErickComp\StackedComponents\Tests\Fixtures\AssetFunctions;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Blade;

class AssetFunctionTest extends TestCase
{
    public function test_it_calls_laravel_asset_helper_by_default(): void
    {
        $this->assertSame('<script src="http://localhost/app.js"></script>', $this->renderJs('/app.js'));
    }

    public function test_false_leaves_the_src_untouched(): void
    {
        config(['stacked-components.asset-function' => false]);

        $this->assertSame('<script src="/app.js"></script>', $this->renderJs('/app.js'));
    }

    public function test_internal_php_function_is_called_directly(): void
    {
        // Through App::call() this would fail: strtoupper() has no "$asset" parameter to bind the src to
        config(['stacked-components.asset-function' => 'strtoupper']);

        $this->assertSame('<script src="/APP.JS"></script>', $this->renderJs('/app.js'));
    }

    public function test_global_function_without_asset_parameter_gets_the_src_positionally(): void
    {
        config(['stacked-components.asset-function' => 'ErickComp\\StackedComponents\\Tests\\Fixtures\\prefixed_asset']);

        $this->assertSame('<script src="/prefixed/app.js"></script>', $this->renderJs('/app.js'));
    }

    public function test_global_function_with_asset_parameter_gets_dependencies_injected(): void
    {
        config([
            'app.asset_version' => '3',
            'stacked-components.asset-function' => 'ErickComp\\StackedComponents\\Tests\\Fixtures\\versioned_asset',
        ]);

        $this->assertSame('<script src="/app.js?v=3"></script>', $this->renderJs('/app.js'));
    }

    public function test_laravel_secure_asset_helper(): void
    {
        config(['stacked-components.asset-function' => 'secure_asset']);

        $this->assertSame('<script src="https://localhost/app.js"></script>', $this->renderJs('/app.js'));
    }

    public function test_array_callable(): void
    {
        config(['stacked-components.asset-function' => [AssetFunctions::class, 'versioned']]);

        $this->assertSame('<script src="/app.js?v=1"></script>', $this->renderJs('/app.js'));
    }

    public function test_array_callable_gets_dependencies_injected(): void
    {
        config([
            'app.asset_version' => '42',
            'stacked-components.asset-function' => [AssetFunctions::class, 'versionedFromConfig'],
        ]);

        $this->assertSame('<script src="/app.js?v=42"></script>', $this->renderJs('/app.js'));
    }

    public function test_array_callable_on_css(): void
    {
        config(['stacked-components.asset-function' => [AssetFunctions::class, 'versioned']]);

        $html = \trim(Blade::render('<x-css src="/app.css" stack="styles" />@stack(\'styles\')'));

        $this->assertSame('<link rel="stylesheet" type="text/css" href="/app.css?v=1">', $html);
    }

    public function test_closure(): void
    {
        config([
            'app.asset_version' => '7',
            'stacked-components.asset-function' => fn (string $asset, Config $config) => "$asset?v=" . $config->get('app.asset_version'),
        ]);

        $this->assertSame('<script src="/app.js?v=7"></script>', $this->renderJs('/app.js'));
    }

    public function test_invokable_object(): void
    {
        config(['stacked-components.asset-function' => new AssetFunctions()]);

        $this->assertSame('<script src="/invoked/app.js"></script>', $this->renderJs('/app.js'));
    }

    public function test_static_method_string(): void
    {
        config(['stacked-components.asset-function' => AssetFunctions::class . '::versioned']);

        $this->assertSame('<script src="/app.js?v=1"></script>', $this->renderJs('/app.js'));
    }

    public function test_at_sign_string(): void
    {
        config(['stacked-components.asset-function' => AssetFunctions::class . '@cdn']);

        $this->assertSame('<script src="https://cdn.example.com/app.js"></script>', $this->renderJs('/app.js'));
    }

    public function test_component_attribute_overrides_the_config(): void
    {
        config(['stacked-components.asset-function' => false]);

        $this->assertSame(
            '<script src="/APP.JS"></script>',
            $this->renderJs('/app.js', 'asset-function="strtoupper"'),
        );
    }

    private function renderJs(string $src, string $extraAttributes = ''): string
    {
        return \trim(Blade::render("<x-js src=\"$src\" stack=\"scripts\" $extraAttributes />@stack('scripts')"));
    }
}
