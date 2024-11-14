<?php

namespace ErickComp\StackedAssetComponents\StackedAssetComponents;

use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;

class Css extends Asset
{
    protected static function assetType(): string
    {
        return 'css';
    }

    protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string
    {

        $defaultAttributeValues = ['rel' => 'stylesheet', 'type' => 'text/css'];
        $src = $attributes->get('src');

        if (empty($src)) {
            unset($defaultAttributeValues['rel']);
        } else {
            $defaultAttributeValues['href'] = $src;
        }

        $attributes = new ComponentAttributeBag([
            ...$defaultAttributeValues,
            ...$attributes->except('src')->all(),
        ]);

        $renderedAttributes = \trim($attributes);

        if (!empty($renderedAttributes)) {
            $renderedAttributes = " $renderedAttributes";
        }

        $EOL = PHP_EOL;

        return empty(\trim($slot))
            ? "<link$renderedAttributes>$EOL"
            : "<style$renderedAttributes>$EOL    $slot    $EOL</style>$EOL";
    }
}
