<?php
namespace ErickComp\StackedComponents;

use Illuminate\Contracts\View\View as LaravelViewInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\Component as LaravelBladeComponent;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;

abstract class Asset extends LaravelBladeComponent
{
    /** @var array<string, int> */
    private static array $phpInternalFunctions;

    /** @var array<string, bool> */
    private static array $functionsWithoutAssetParameter = [];
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
        ?string $stack = null,
        bool|string $once = true,
        bool|string $stackPrepend = false,
        /** @internal This property is internal. If you want to redeclare it, do so at your own risk. */
        protected null|string|array|object|false $assetFunction = null,
    ) {
        $this->stack = $this->validateStack($stack);
        $this->once = self::toBool($once);
        $this->stackOp = self::toBool($stackPrepend)
            ? 'prepend'
            : 'push';
    }

    /**
     * Blade passes a string for once="false" and a bool for :once="false" or a valueless attribute
     */
    private static function toBool(bool|string $value): bool
    {
        return \is_bool($value) ? $value : \filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function validateStack(?string $stack): string
    {
        $assetType = static::assetType();
        $assetTypeLower = \strtolower($assetType);
        $stack ??= config("stacked-components.default-stack-$assetTypeLower", null);

        if ($stack === null) {
            $assetTypeUpper = \strtoupper($assetType);

            throw new \LogicException(
                "You must inform a stack or configure a default $assetTypeUpper stack. " .
                "You can do it by setting the config [stacked-components.default-stack-$assetTypeLower] " .
                "or the environment variable [STACKED_COMPONENTS_DEFAULT_STACK_$assetTypeUpper]"
            );
        }

        return $stack;
    }

    /**
     * Return the asset "type", like "js" and "css"
     *
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected static function assetType(): string
    {
        return \strtolower(class_basename(static::class));
    }

    /**
     * Must return the code that will be pushed/prepended to the stack
     *
     * @internal This method is internal. If you want to extend it, do so at your own risk.
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

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function doRender(array $componentData)
    {
        $attributesForCode = $this->getAttributesToGenerateCode($componentData);
        $code = $this->getStackedCode($attributesForCode, $componentData['slot'] ?? new ComponentSlot());

        $this->insertCodeIntoStack($code, $this->once, $this->stackOp);

        // Returning an empty view implementation so it does not access the filesystem, achieving a better performance.
        // In my tests, the empty view spends 1/3 of the time of an empty string/empty view file
        return static::emptyView();
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function getRenderedSlot(ComponentSlot $slot): string
    {
        return $slot->toHtml();
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function getAttributesToGenerateCode(array $componentData): ComponentAttributeBag
    {
        $trimedSlot = \trim((string) ($componentData['slot'] ?? ''));
        $trimedSrc = \trim($this->src ?? '');

        if (!empty($trimedSlot) && !empty($trimedSrc)) {
            throw new \LogicException('Cannot use src attribute and inline code at the same time');
        }

        /** @var \Illuminate\View\ComponentAttributeBag $attributes */
        $attributes = $componentData['attributes'];

        if ($componentData['src'] !== null) {
            $attributes = $attributes->merge(['src' => $this->getAssetSrc($componentData['src'])]);
        }

        return $attributes;
    }

    /**
     * Insert (push or prepend) a block of code into a stack
     * 
     * @param "push"|"prepend" $stackOp
     * @internal This method is internal. If you want to extend it, do so at your own risk.
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
     *
     * @internal This method is internal. If you want to extend it, do so at your own risk.
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

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function getAssetSrc(string $src, array $additionalParams = []): string
    {
        $assetFunction = $this->getAssetFunction();

        if ($assetFunction === false) {
            return $src;
        }

        if (
            \is_string($assetFunction)
            && (static::isPhpInternalFunction($assetFunction) || static::isFunctionWithoutAssetParameter($assetFunction))
        ) {
            return $assetFunction($src);
        }

        $callParams = ['asset' => $src] + $additionalParams;

        return App::call($assetFunction, $callParams);
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function getAssetFunction(): null|string|array|object|false
    {
        return $this->assetFunction ?? config('stacked-components.asset-function');
    }

    /**
     * PHP's internal functions, keyed by name so the lookup is a hash hit
     *
     * @return array<string, int>
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected static function phpInternalFunctions(): array
    {
        return self::$phpInternalFunctions ??= \array_flip(\get_defined_functions(true)['internal']);
    }

    /**
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function isPhpInternalFunction(string $functionName): bool
    {
        return isset(static::phpInternalFunctions()[$functionName]);
    }

    /**
     * Whether it's a global function with no "$asset" parameter, like Laravel's asset($path), that App::call()
     * can't bind the src to, so the src must be passed positionally
     *
     * @internal This method is internal. If you want to extend it, do so at your own risk.
     */
    protected function isFunctionWithoutAssetParameter(string $functionName): bool
    {
        return self::$functionsWithoutAssetParameter[$functionName] ??= \function_exists($functionName)
            && !\in_array(
                'asset',
                \array_map(
                    static fn (\ReflectionParameter $parameter) => $parameter->getName(),
                    (new \ReflectionFunction($functionName))->getParameters(),
                ),
                true,
            );
    }
}
