<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\Factory;
use Leantime\Core\Application;
use Leantime\Core\Bootloader;
use Leantime\Core\Language;
use Leantime\Core\Support\Build;
use Leantime\Core\Support\Format;
use Leantime\Core\Support\Cast;

if (! function_exists('app')) {
    /**
     * Returns the application instance.
     *
     * @param string $abstract
     * @param array  $parameters
     * @return mixed|Application
     * @throws BindingResolutionException
     */
    function app(string $abstract = '', array $parameters = []): mixed
    {
        $app = Application::getInstance();
        return !empty($abstract) ? $app->make($abstract, $parameters) : $app;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed $args
     * @return never
     */
    function dd(...$args): never
    {
        echo sprintf('<pre>%s</pre>', var_export($args, true));
        exit;
    }
}

if (! function_exists('bootstrap_minimal_app')) {
    /**
     * Bootstrap a new IoC container instance.
     *
     * @return Application
     * @throws BindingResolutionException
     */
    function bootstrap_minimal_app(): Application
    {
        $app = app()::setInstance(new Application())::setHasBeenBootstrapped();
        return Bootloader::getInstance($app)->getApplication();
    }
}

if (! function_exists('__')) {
    /**
     * Translate a string.
     *
     * @param string $index
     * @param string $default
     * @return string
     * @throws BindingResolutionException
     */
    function __(string $index, $default = ''): string
    {
        return app()->make(Language::class)->__(index: $index, default: $default);
    }
}

if (! function_exists('view')) {
    /**
     * Get the view factory instance.
     *
     * @return Factory
     * @throws BindingResolutionException
     */
    function view(): Factory
    {
        return app()->make(Factory::class);
    }
}

if (! function_exists('array_sort')) {
    /**
     * sort array of arrqays by value
     *
     * @param array  $array
     * @param string $sortyBy
     * @return array
     */
    function array_sort(array $array, mixed $sortyBy): array
    {

        if (is_string($sortyBy)) {
            $collection = collect($array);

            $sorted = $collection->sortBy($sortyBy, SORT_NATURAL);

            return $sorted->values()->all();
        } else {
            return \Illuminate\Support\Collection::make($array)->sortBy($sortyBy)->all();
        }
    }
}

if (! function_exists('do_once')) {
    /**
     * Execute a callback only once.
     *
     * @param Closure $callback
     * @param bool    $across_requests
     * @param string  $key
     * @return void
     **/
    function do_once(string $key, Closure $callback, bool $across_requests = false): void
    {
        $key = "do_once_{$key}";

        if ($across_requests) {
            $_SESSION['do_once'] ??= [];

            if ($_SESSION['do_once'][$key] ?? false) {
                return;
            }

            $_SESSION['do_once'][$key] = true;
        } else {
            static $do_once;
            $do_once ??= [];

            if ($do_once[$key] ?? false) {
                return;
            }

            $do_once[$key] = true;
        }

        $callback();
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     * If an array is passed as the key, we will assume you want to set an array of values.
     * @param array|string|null $key
     * @param mixed             $default
     * @return mixed|Application
     * @throws BindingResolutionException
     **/
    function config(array|string|null $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('build')) {
    /**
     * Turns any object into a builder object
     * @param object $object
     * @return Build
     **/
    function build(object $object): Build
    {
        return new Build($object);
    }
}

if (! function_exists('format')) {
    /**
     * Returns a format object to format string values
     * @param string|int|float $value
     * @return Format
     **/
    function format(string|int|float|null|\DateTime $value, string|int|float|null|\DateTime $value2 =null): Format
    {
        if ($value instanceof \DateTime) {
            $value = $value->format("Y-m-d H:i:s");
        }

        return new Format($value, $value2);
    }
}

if (! function_exists('cast')) {
    /**
     * Casts a variable to a different type if possible.
     *
     * @param mixed $obj The object to be cast.
     * @param string $to_class The class to which the object should be cast.
     * @param array $construct_params Optional parameters to pass to the constructor.
     * @param array $mappings Make sure certain sub properties are casted to specific types.
     * @return mixed The casted object, or throws an exception on failure.
     * @throws \InvalidArgumentException If the class does not exist.
     * @throws \RuntimeException On serialization errors.
     */
    function cast(mixed $source, string $classOrType, array $constructParams = [], array $mappings = []): object {
        if (in_array($classOrType, ['int', 'integer', 'float', 'string', 'str', 'bool', 'boolean', 'object', 'stdClass', 'array'])) {
            return Cast::castSimple($source, $classOrType);
        }

        if (enum_exists($classOrType)) {
            return Cast::castEnum($source, $classOrType);
        }

        return (new Cast($source))->castTo($classOrType, $constructParams, $mappings);
    }
}
