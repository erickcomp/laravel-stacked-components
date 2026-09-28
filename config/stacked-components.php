<?php

return [
    /*
     * Called on the src of the JS and CSS components. Any callable or "Class@method" string; the src is passed to
     * its $asset parameter, or as the first argument of a global function without one. false calls nothing
     */
    'asset-function' => env('STACKED_COMPONENTS_ASSET_FUNCTION', 'asset'),

    /*
     * Stacks the JS and CSS components push to when they have no stack attribute
     */
    'default-stack-js' => env('STACKED_COMPONENTS_DEFAULT_STACK_JS', null),
    'default-stack-css' => env('STACKED_COMPONENTS_DEFAULT_STACK_CSS', null),

    /*
     * Also registers the components under a namespace, like <x-stacked::js>. true uses "stacked"
     */
    'component-namespace' => env('STACKED_COMPONENTS_COMPONENT_NAMESPACE', false),

    /*
     * Component names
     */
    'component-name-js' => env('STACKED_COMPONENTS_COMPONENT_NAME_JS', 'js'),
    'component-name-css' => env('STACKED_COMPONENTS_COMPONENT_NAME_CSS', 'css'),
    'component-name-content' => env('STACKED_COMPONENTS_COMPONENT_NAME_CONTENT', 'stacked-content'),
    'component-name-div' => env('STACKED_COMPONENTS_COMPONENT_NAME_DIV', 'stacked-div'),

    /*
     * Adds a head_bottom stack right before </head> in every template. Templates get it when compiled, so clear the
     * view cache (php artisan view:clear) after changing this
     */
    'head-bottom-stack' => env('STACKED_COMPONENTS_HEAD_BOTTOM_STACK', true),
];
