<?php

namespace ErickComp\StackedComponents;

use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;

class Js extends Asset
{
    protected static function assetType(): string
    {
        return 'js';
    }

    protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string
    {
        $renderedSlot = $this->getRenderedSlot($slot);
        $renderedAttributes = \trim($attributes);

        if (!empty($renderedAttributes)) {
            $renderedAttributes = " $renderedAttributes";
        }

        $EOL = PHP_EOL;

        if (empty($trimmedSlot = $renderedSlot)) {
            return "<script$renderedAttributes></script>$EOL";
        }

        $trimmedSlot = Str::replaceStart('<script>', '', $trimmedSlot);
        $trimmedSlot = Str::replaceEnd('</script>', '', $trimmedSlot);

        return "<script$renderedAttributes>$EOL    $trimmedSlot    $EOL</script>$EOL";
    }
}
