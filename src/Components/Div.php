<?php

namespace ErickComp\StackedAssetComponents\StackedAssetComponents;

use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;

class Div extends Asset
{
    /**
     * @inheritDoc
     * 
     * @throws \LogicException
     */
    public function __construct(
        string $stack,
        public ?string $src = null,
        string $once = "false",
        bool $stackPrepend = false,
    ) {
        parent::__construct($src, $once, $stack, $stackPrepend);
    }

    protected function validateStack(?string $stack): string
    {
        return $stack ?? '';
    }

    protected function getAttributesToGenerateCode(array $componentData): ComponentAttributeBag
    {
        /** @var \Illuminate\View\ComponentAttributeBag $attributes */
        $attributes = $componentData['attributes'];
        return $attributes->merge(['src' => $componentData['src']]);
    }

    protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string
    {
        $renderedSlot = $slot->toHtml();
        $renderedAttributes = \trim($attributes);

        if (!empty($renderedAttributes)) {
            $renderedAttributes = " $renderedAttributes";
        }

        $EOL = PHP_EOL;

        return "<div$renderedAttributes>$EOL$renderedSlot$EOL</div>$EOL";
    }
}
