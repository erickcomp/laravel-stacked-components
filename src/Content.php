<?php

namespace ErickComp\StackedComponents;

use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;

class Content extends Asset
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

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function validateStack(?string $stack): string
    {
        return $stack ?? '';
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function getAttributesToGenerateCode(array $componentData): ComponentAttributeBag
    {
        return new ComponentAttributeBag();
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string
    {
        return $this->getRenderedSlot($slot);
    }
}
