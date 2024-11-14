<?php

namespace ErickComp\StackedAssetComponents\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class StackedAssetComponentsProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $config = config('stacked-assets-components');

        $componentNamespace = $config['component-namespace'] ?? false;
        $jsComponentName = $config['component-name-js'];
        $cssComponentName = $config['component-name-css'];
        $contentComponentName = $config['component-name-content'];
        $divComponentName = $config['component-name-div'];

        if (\is_bool($componentNamespace)) {
            $componentNamespace = 'stacked';
        }

        if (\is_string($componentNamespace)) {
            Blade::componentNamespace('ErickComp\\StackedAssetComponents\\Components\\StackedAssetComponents', $componentNamespace);
        }

        Blade::component($jsComponentName, \ErickComp\StackedAssetComponents\StackedAssetComponents\Js::class);
        Blade::component($cssComponentName, \ErickComp\StackedAssetComponents\StackedAssetComponents\Css::class);
        Blade::component($contentComponentName, \ErickComp\StackedAssetComponents\StackedAssetComponents\Content::class);
        Blade::component($divComponentName, \ErickComp\StackedAssetComponents\StackedAssetComponents\Div::class);
    }
}
