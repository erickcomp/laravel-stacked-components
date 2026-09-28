<?php

namespace ErickComp\StackedComponents\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\ServiceProvider;

class StackedComponentsProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../../config/stacked-components.php';

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'stacked-components');
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->publishes([self::CONFIG_PATH => config_path('stacked-components.php')], 'stacked-components-config');

        $config = config('stacked-components');

        $componentNamespace = $config['component-namespace'] ?? false;
        $jsComponentName = $config['component-name-js'] ?? 'js';
        $cssComponentName = $config['component-name-css'] ?? 'css';
        $contentComponentName = $config['component-name-content'] ?? 'stacked-content';
        $divComponentName = $config['component-name-div'] ?? 'stacked-div';

        if ($componentNamespace === true) {
            $componentNamespace = 'stacked';
        }

        if (\is_string($componentNamespace) && $componentNamespace !== '') {
            Blade::componentNamespace('ErickComp\\StackedComponents', $componentNamespace);
        }

        Blade::component($jsComponentName, \ErickComp\StackedComponents\Js::class);
        Blade::component($cssComponentName, \ErickComp\StackedComponents\Css::class);
        Blade::component($contentComponentName, \ErickComp\StackedComponents\Content::class);
        Blade::component($divComponentName, \ErickComp\StackedComponents\Div::class);

        $this->createStacks();
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function createStacks()
    {
        ViewFactory::macro('hasStack', function (string $stack): bool {
            return \array_key_exists($stack, $this->pushes);
        });

        if (!config('stacked-components.head-bottom-stack', true)) {
            return;
        }

        Blade::prepareStringsForCompilationUsing(
            function (string $templateStr): string {
                if (\str_contains($templateStr, '</head>')) {
                    if (!ViewFactory::hasStack('head_bottom')) {
                        // startPush() with no content opens an output buffer, so it must be closed right away
                        ViewFactory::startPush('head_bottom');
                        ViewFactory::stopPush();
                    }

                    return \str_replace('</head>', "@stack('head_bottom')\n</head>", $templateStr);
                }

                return $templateStr;

            }
        );
    }
}
