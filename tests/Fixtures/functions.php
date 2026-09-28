<?php

namespace ErickComp\StackedComponents\Tests\Fixtures;

use Illuminate\Contracts\Config\Repository as Config;

function prefixed_asset(string $path): string
{
    return "/prefixed$path";
}

function versioned_asset(string $asset, Config $config): string
{
    return "$asset?v=" . $config->get('app.asset_version');
}
