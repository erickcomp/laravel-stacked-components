<?php

namespace ErickComp\StackedAssetComponents;

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
        return new ComponentAttributeBag();
    }

    protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string
    {
        return $this->getRenderedSlot($slot);
    }
}
