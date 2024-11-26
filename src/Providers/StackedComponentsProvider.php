<?php

namespace ErickComp\StackedComponents\Providers;

use Illuminate\Support\Facades\Blade;
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
        $jsComponentName = $config['component-name-js'];
        $cssComponentName = $config['component-name-css'];
        $contentComponentName = $config['component-name-content'];
        $divComponentName = $config['component-name-div'];

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
    }
}
