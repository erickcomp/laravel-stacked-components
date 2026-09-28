<?php

namespace ErickComp\StackedComponents;

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
        bool|string $once = false,
        bool|string $stackPrepend = false,
    ) {
        parent::__construct(null, $stack, $once, $stackPrepend);
    }

    protected function validateStack(?string $stack): string
    {
        return $stack ?? '';
    }

    protected function getAttributesToGenerateCode(array $componentData): ComponentAttributeBag
    {
        return $componentData['attributes'];
    }

    protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string
    {
        $renderedSlot = $this->getRenderedSlot($slot);
        $renderedAttributes = \trim($attributes);

        if (!empty($renderedAttributes)) {
            $renderedAttributes = " $renderedAttributes";
        }

        $EOL = PHP_EOL;

        return "<div$renderedAttributes>$EOL$renderedSlot$EOL</div>$EOL";
    }
}
