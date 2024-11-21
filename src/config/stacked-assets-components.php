<?php

return [
    'asset-function' => env('STACKED_ASSETS_COMPONENTS_DEFAULT_ASSET_FUNCTION', 'asset'),
    'default-stack-js' => env('STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_JS', null),
    'default-stack-css' => env('STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_CSS', null),
    'component-namespace' => env('STACKED_ASSETS_COMPONENTS_COMPONENT_NAMESPACE', false),
    'component-name-js' => env('STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_JS', 'js'),
    'component-name-css' => env('STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_CSS', 'css'),
    'component-name-content' => env('STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_CONTENT', 'stacked-content'),
    'component-name-div' => env('STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_DIV', 'stacked-div'),
];
