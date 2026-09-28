<?php

namespace ErickComp\StackedComponents\Tests\Providers;

use ErickComp\StackedComponents\Tests\TestCase;

class EnvironmentVariablesTest extends TestCase
{
    private const ENV = [
        'STACKED_COMPONENTS_ASSET_FUNCTION' => 'strtoupper',
        'STACKED_COMPONENTS_DEFAULT_STACK_JS' => 'env-js',
        'STACKED_COMPONENTS_DEFAULT_STACK_CSS' => 'env-css',
        'STACKED_COMPONENTS_COMPONENT_NAME_JS' => 'env-script',
    ];

    protected function setUp(): void
    {
        foreach (self::ENV as $name => $value) {
            $_ENV[$name] = $_SERVER[$name] = $value;
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (\array_keys(self::ENV) as $name) {
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }

    public function test_config_is_read_from_the_environment(): void
    {
        $this->assertSame('strtoupper', config('stacked-components.asset-function'));
        $this->assertSame('env-js', config('stacked-components.default-stack-js'));
        $this->assertSame('env-css', config('stacked-components.default-stack-css'));
        $this->assertSame('env-script', config('stacked-components.component-name-js'));
    }

    public function test_components_use_the_environment_config(): void
    {
        $html = $this->renderBlade('<x-env-script src="/app.js" /><x-css src="/app.css" />[@stack(\'env-js\')][@stack(\'env-css\')]');

        $this->assertSame(
            '[<script src="/APP.JS"></script>' . PHP_EOL . '][<link rel="stylesheet" type="text/css" href="/APP.CSS">' . PHP_EOL . ']',
            $html,
        );
    }
}
