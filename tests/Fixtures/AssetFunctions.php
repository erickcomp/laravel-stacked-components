<?php

namespace ErickComp\StackedComponents\Tests\Fixtures;

use Illuminate\Contracts\Config\Repository as Config;

class AssetFunctions
{
    public static function versioned(string $asset): string
    {
        return "$asset?v=1";
    }

    public static function versionedFromConfig(string $asset, Config $config): string
    {
        return "$asset?v=" . $config->get('app.asset_version');
    }

    public function cdn(string $asset): string
    {
        return "https://cdn.example.com$asset";
    }

    public function __invoke(string $asset): string
    {
        return "/invoked$asset";
    }
}
