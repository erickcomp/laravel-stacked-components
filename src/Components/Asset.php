<?php

namespace ErickComp\StackedAssetComponents\Components;

use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\Component as LaravelBladeComponent;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;
use Illuminate\Contracts\View\View as LaravelViewInterface;

abstract class Asset extends LaravelBladeComponent
{
    private static LaravelViewInterface $emptyView;
    public string $stack;

    /** @var "push"|"prepend" $stackOp */
    public string $stackOp;
    public bool $once;

    /**
     * @inheritDoc
     * 
     * @throws \LogicException
     */
    public function __construct(
        public ?string $src = null,
        string $once = "true",
        ?string $stack = null,
        bool $stackPrepend = false,
    ) {

        $this->stack = $this->validateStack($stack);
        $this->once = \filter_var(\strtolower($once), FILTER_VALIDATE_BOOLEAN);
        $this->stackOp = $stackPrepend
            ? 'prepend'
            : 'push';
    }

    protected function validateStack(?string $stack): string
    {
        $assetType = static::assetType();
        $assetTypeLower = \strtolower($assetType);
        $stack ??= config("stacked-assets-components.default-stack-$assetTypeLower", null);

        if ($stack === null) {
            $assetTypeUpper = \strtoupper($assetType);

            throw new \LogicException(
                "You must inform a stack or configure a default $assetTypeUpper stack. " .
                "You can do it by setting the config [stacked-assets-components.default-stack-$assetTypeLower] " .
                "or the environment variable [STACKED_ASSETS_COMPONENTS_DEFAULT_STACK_$assetTypeUpper]"
            );
        }

        return $stack;
    }

    /**
     * Return the asset "type", like "js" and "css"
     */
    protected static function assetType(): string
    {
        return \strtolower(class_basename(static::class));
    }

    /**
     * Must return the code that will be pushed/prepended to the stack
     */
    abstract protected function getStackedCode(ComponentAttributeBag $attributes, ComponentSlot $slot): string;


    /**
     * @inheritDoc
     * 
     * @return \Closure
     */
    public function render()
    {
        return $this->doRender(...);
    }

    protected function doRender(array $componentData)
    {
        $attributesForCode = $this->getAttributesToGenerateCode($componentData);
        $code = $this->getStackedCode($attributesForCode, $componentData['slot']);

        $this->insertCodeIntoStack($code, $this->once, $this->stackOp);

        // Returning an empty view implementation so it does not access the filesystem, achieving a better performance.
        // In my tests, the empty view spends 1/3 of the time of an empty string/empty view file
        return static::emptyView();
    }

    protected function getAttributesToGenerateCode(array $componentData): ComponentAttributeBag
    {
        $trimedSlot = \trim((string) $componentData['attributes']['slot']);
        $trimedSrc = \trim($this->src ?? '');

        if (!empty($trimedSlot) && !empty($trimedSrc)) {
            throw new \LogicException('Cannot use src attribute and inline code at the same time');
        }

        /** @var \Illuminate\View\ComponentAttributeBag $attributes */
        $attributes = $componentData['attributes'];
        return $attributes->merge(['src' => $componentData['src']]);
    }

    /**
     * Insert (push or prepend) a block of code into a stack
     * 
     * @param "push"|"prepend" $stackOp
     */
    protected function insertCodeIntoStack(string $code, bool $once, string $stackOp)
    {
        if (!$once || !ViewFactory::hasRenderedOnce($code)) {
            if ($stackOp === 'push') {
                ViewFactory::startPush($this->stack, $code);
            } else {
                ViewFactory::startPrepend($this->stack, $code);
            }

            if ($once) {
                ViewFactory::markAsRenderedOnce($code);
            }
        }
    }

    /**
     * Returns an instance of \Illuminate\Contracts\View\View interface.
     * Such instance is hollow and does not have the overhead of caching or access the filesystem at all
     */
    protected final static function emptyView(): LaravelViewInterface
    {
        if (!isset(self::$emptyView)) {
            self::$emptyView = new class () implements LaravelViewInterface {
                public function name()
                {
                    return '';
                }

                public function with($key, $value = null)
                {
                    return $this;
                }

                public function getData()
                {
                    return [];
                }

                public function render()
                {
                    return '';
                }
            };
        }

        return self::$emptyView;
    }
}
