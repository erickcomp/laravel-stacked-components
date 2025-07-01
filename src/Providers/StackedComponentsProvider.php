<?php

namespace ErickComp\StackedComponents\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\ServiceProvider;

class StackedComponentsProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $config = config('stacked-components');

        $componentNamespace = $config['component-namespace'] ?? false;
        $jsComponentName = $config['component-name-js'] ?? 'js';
        $cssComponentName = $config['component-name-css'] ?? 'css';
        $contentComponentName = $config['component-name-content'] ?? 'content';
        $divComponentName = $config['component-name-div'] ?? 'div';

        if (\is_bool($componentNamespace)) {
            $componentNamespace = 'stacked';
        }

        if (\is_string($componentNamespace)) {
            Blade::componentNamespace('ErickComp\\StackedAssetComponents', $componentNamespace);
        }

        Blade::component($jsComponentName, \ErickComp\StackedComponents\Js::class);
        Blade::component($cssComponentName, \ErickComp\StackedComponents\Css::class);
        Blade::component($contentComponentName, \ErickComp\StackedComponents\Content::class);
        Blade::component($divComponentName, \ErickComp\StackedComponents\Div::class);

        $this->createStacks();
    }

    protected function createStacks()
    {
        ViewFactory::macro('hasStack', function (string $stack): bool {
            return \array_key_exists($stack, $this->pushes);
        });

        $stackCreator = new class () {
            protected array $stacksCreated = [];
            public function __invoke(string $templateStr): string {}
        };

        Blade::prepareStringsForCompilationUsing(
            function (string $templateStr): string {
                if (\str_contains($templateStr, '</head>')) {
                    if (!ViewFactory::hasStack('head_bottom')) {
                        ViewFactory::startPush('head_bottom', '');
                        return \str_replace('</head>', "@stack('head_bottom')\n</head>", $templateStr);
                    }
                }


                return $templateStr;

            }
        );
    }
}
