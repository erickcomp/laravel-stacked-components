# Upgrade guide

## From 0.x to 1.0

Coming from 0.9 or earlier, also read the 0.10.0 changes in the [CHANGELOG](CHANGELOG.md): they fix bugs, but some change behavior.

### Laravel 12 or 13

1.0 requires Laravel 12 or 13. For Laravel 10 and 11, stay on 0.10.

### Environment variables

The environment variables are now named ```STACKED_COMPONENTS_``` plus the config key. The old names are no longer read, so rename them in your ```.env```:

| 0.x | 1.0 |
|---|---|
| `STACKED_ASSETS_COMPONENTS_DEFAULT_ASSET_FUNCTION` | `STACKED_COMPONENTS_ASSET_FUNCTION` |
| `STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_JS` | `STACKED_COMPONENTS_DEFAULT_STACK_JS` |
| `STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_CSS` | `STACKED_COMPONENTS_DEFAULT_STACK_CSS` |
| `STACKED_ASSETS_COMPONENTS_COMPONENT_NAMESPACE` | `STACKED_COMPONENTS_COMPONENT_NAMESPACE` |
| `STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_JS` | `STACKED_COMPONENTS_COMPONENT_NAME_JS` |
| `STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_CSS` | `STACKED_COMPONENTS_COMPONENT_NAME_CSS` |
| `STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_CONTENT` | `STACKED_COMPONENTS_COMPONENT_NAME_CONTENT` |
| `STACKED_ASSETS_COMPONENTS_COMPONENT_NAME_DIV` | `STACKED_COMPONENTS_COMPONENT_NAME_DIV` |

### Config

The config key is still ```stacked-components```, so a ```config/stacked-components.php``` in your app keeps working. The config can now be published with ```php artisan vendor:publish --tag=stacked-components-config```, and has a new option, ```head-bottom-stack```.

### The src attribute of the content and div components

```<x-stacked-content>``` and ```<x-stacked-div>``` no longer have a ```src``` parameter. The content component ignores it, as before; the div component renders it like any other attribute, as before, but it's no longer part of the component's API.

### stack-prepend="false"

```stack-prepend="false"``` used to prepend, because the string "false" was taken as true. It now pushes.

### Extending the components

Protected methods and properties are internal: they may change in any release, and extending them is at your own risk.

If your subclass redeclares the ```$assetFunction``` property, for example by promoting it in its own constructor, PHP requires its type to be exactly the parent's, which is ```null|string|array|object|false``` since 0.10.0. Instead of redeclaring it, pass it on to the parent constructor, and accept strings for ```once``` and ```stackPrepend``` so ```once="false"``` keeps working:

```php
public function __construct(
    ?string $src = null,
    ?string $stack = null,
    bool|string $once = true,
    bool|string $stackPrepend = false,
    null|string|array|object|false $assetFunction = null,
) {
    parent::__construct(src: $src, stack: $stack, once: $once, stackPrepend: $stackPrepend, assetFunction: $assetFunction);
}
```
