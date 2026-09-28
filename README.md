<p align="center">
    <a href="https://packagist.org/packages/erickcomp/laravel-stacked-components"><img src="https://img.shields.io/packagist/v/erickcomp/laravel-stacked-components" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/erickcomp/laravel-stacked-components"><img src="https://img.shields.io/packagist/dt/erickcomp/laravel-stacked-components" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/erickcomp/laravel-stacked-components"><img src="https://img.shields.io/packagist/l/erickcomp/laravel-stacked-components" alt="License"></a>
</p>

# Use Blade Components syntax to insert (push/prepend) content to stacks

This package provides some blade components that you can use to insert content into stacks, most notably, scripts and styles.

## Requirements

* PHP 8.2+
* Laravel 12 or 13

For Laravel 10 and 11, use version 0.10.

## Installation

```shell
composer require erickcomp/laravel-stacked-components
```

## The vanilla-Blade way

To push some JS file/code to a stack, you have to ([from Laravel docs](https://laravel.com/docs/12.x/blade#stacks)):
```blade
@push('scripts')
    <script src="{{ asset("/example.js") }}"></script>
@endpush
```

or, for inline JS:

```blade
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            alert("Alert 2!!!");
        });
    </script>
@endpush
```

It, of course, works. But when using Blade Components, the intention is to stay closer to HTML whenever possible.

## The Stacked Components way

### JS component
```blade
<x-js src="/example.js" stack="scripts"/>
```

or, for inline JS:

```blade
<x-js stack="scripts">
    document.addEventListener("DOMContentLoaded", (event) => {
        alert("Alert 2!!!");
    });
</x-js>
```

Any other attribute, like ```type="module"``` or ```defer```, goes to the ```<script>``` tag.

### CSS component
```blade
<x-css src="/example.css" stack="styles"/>
```
pushes ```<link rel="stylesheet" type="text/css" href="...">```. For inline CSS:

```blade
<x-css stack="styles">
    body { color: red; }
</x-css>
```
pushes a ```<style>``` tag. Any other attribute, like ```media="print"```, goes to the tag, and ```rel``` and ```type``` can be overridden.

A component can't have both a ```src``` and inline code.

### The asset function

This package calls Laravel's ```asset``` function on the ```src``` of the JS and CSS components by default. You can override this behavior to call your own callable or to call nothing at all.
To do either of these, you can set the environment variable
```ini
STACKED_ASSETS_COMPONENTS_DEFAULT_ASSET_FUNCTION
```
or the config value
```ini
stacked-components.asset-function
```
or, for a single component, the ```asset-function``` attribute:
```blade
<x-js src="/example.js" stack="scripts" asset-function="secure_asset"/>
```

The asset function can be any [PHP callable](https://www.php.net/manual/en/language.types.callable.php) or use Laravel's "@" syntax (```MyNamespace\MyClass@myAssetFunction```).
The callable is invoked through Laravel's container, which passes the asset's src to its ```$asset``` parameter and injects any other dependency it asks for.
Global functions with no ```$asset``` parameter, like Laravel's ```asset($path)```, get the src as their first argument instead.
To call no function, set the config value or the environment variable to <b><u>false</u></b>.

Note that if you use objects (anonymous functions, invokable objects, first-class callables), you won't be able to use Laravel's config cache,
since it does not support cache for these types of config values.

### Content component
```blade
<x-stacked-content stack="scripts">
    <script src="/example.js"></script>
</x-stacked-content>
```
or, for inline JS:

```blade
<x-stacked-content stack="scripts">
    <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            alert("Alert 2!!!");
        });
    </script>
</x-stacked-content>
```

It pushes its content as is.

### Div component
```blade
<x-stacked-div stack="modals" id="confirm-modal" class="modal">
    Are you sure?
</x-stacked-div>
```
pushes its content wrapped in a ```<div>``` with the given attributes.

### Stack options

All the components accept:

* ```stack```: the stack to push to. For the JS and CSS components, you can set a default stack in config/env and omit it (see below).
* ```once```: whether the same content is pushed only once, like Blade's ```@pushOnce```. It defaults to ```"true"``` for the JS and CSS components and to ```"false"``` for the content and div components.
* ```stack-prepend```: prepends the content to the stack instead of pushing it, like Blade's ```@prepend```.

```blade
<x-js src="/vendor.js" stack="scripts" :stack-prepend="true"/>
<x-stacked-content stack="scripts" once="true">...</x-stacked-content>
```

#### Default stacks

For the CSS and JS components, you can set a default stack in config/env and omit it when using the component, like this:

.env file:
```ini
STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_JS="scripts"
STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_CSS="styles"
```

and in your view file:
```blade
<x-js src="/example.js" />
```

#### The head_bottom stack

Every template with a ```</head>``` tag gets a ```head_bottom``` stack right before it, so you can push to the end of ```<head>``` without declaring a stack in your layout:

```blade
<x-css src="/example.css" stack="head_bottom"/>
```

### Resolving name collisions with other components
If your app, or a library you're using, already defines components with the same names as this package's, you have 2 options:

1 - Register a namespace for the components of this package. To do so, you can set the environment variable
```ini
STACKED_ASSETS_COMPONENTS_COMPONENT_NAMESPACE
```
or the config value
```ini
stacked-components.component-namespace
```

If you set the namespace to ```true```, it will use the namespace "stacked", as in ```<x-stacked::js>```. If you set it to any string, that string will be used as the blade components namespace. For more information on components namespaces check the [Laravel docs](https://laravel.com/docs/packages#autoloading-package-components)

2 - Set the config values
```ini
stacked-components.component-name-js
stacked-components.component-name-css
stacked-components.component-name-content
stacked-components.component-name-div
```
or the environment variables
```ini
STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_JS
STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_CSS
STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_CONTENT
STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_DIV
```
To specify alternate names for the components.

Remember to clear the views caches when changing any of these values.
To clear the view cache run the view:clear artisan command:

```shell
php artisan view:clear
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
