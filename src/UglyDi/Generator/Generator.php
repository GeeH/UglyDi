<?php

namespace UglyDi\Generator;

use ReflectionParameter;
use UglyDi\Exception\InvalidCacheDirException;
use UglyDi\UglyDi;

class Generator implements GeneratorInterface
{
    /**
     * Tab string
     */
    const TAB = '    ';
    /**
     * @var string
     */
    protected $cacheDir = '';

    /**
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        if (substr($cacheDir, -1) !== '/') {
            // add trailing slash
            $cacheDir .= '/';
        }
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param Name $className
     * @param array $parameters
     * @param array $userArguments
     * @return bool|mixed
     */
    public function generateFactory($className, array $parameters, array $userArguments)
    {
        $factory = '<?php' . PHP_EOL;
        $factory .= 'return function (' . UglyDi::class . ' $di) {' . PHP_EOL;
        if (empty($parameters)) {
            $factory .= self::TAB . 'return new ' . $className . '();' . PHP_EOL;
        } else {
            $factory .= $this->generateDependentClass($className, $parameters, $userArguments);
        }

        $factory .= '};' . PHP_EOL;

        return $this->writeFactoryToFile($className, $factory);
    }

    /**
     * @param $className
     * @param \ReflectionParameter[] $parameters
     * @return string
     */
    private function generateDependentClass($className, array $parameters, array $userArguments = [])
    {
        $factory = '';
        $values  = '(';
        foreach ($parameters as $parameter) {
            $argument = array_key_exists($parameter->getName(), $userArguments)
                ? $userArguments[$parameter->getName()]
                : null;
            $factory .= $this->generateArgument($parameter, $argument);
            $values .= '$' . $parameter->getName() . ', ';
        }
        $values = rtrim($values, ', ') . ')';
        $factory .= self::TAB . 'return new ' . $className . $values . ';' . PHP_EOL;

        return $factory;
    }

    /**
     * @param ReflectionParameter $argument
     * @param null $option
     * @return string
     */
    private function generateArgument(\ReflectionParameter $argument, $option = null)
    {
        $factory = self::TAB;
        if ($argument->getClass() && !$argument->isOptional()) {
            $factory .= '$' . $argument->getName() . ' = $di->get(\'';
            if (!is_null($option)) {
                $factory .= $option;
            } else {
                $factory .= $argument->getClass()->getName() . '\');' . PHP_EOL;
            }
            return $factory;
        }

        if ($argument->getClass() && $argument->isOptional() && !is_null($option)) {
            $factory .= '$' . $argument->getName() . ' = $di->get(\''
                . $option
                . '\');' . PHP_EOL;
            return $factory;
        }

        $option = var_export($option, true);
        $factory .= '$' . $argument->getName() . ' = ' . $option . ';' . PHP_EOL;

        return $factory;
    }

    /**
     * @param $className
     * @param $factory
     * @return bool
     */
    private function writeFactoryToFile($className, $factory)
    {
        $filename = sha1($className) . '.php';
        if (!($this->cacheDir)) {
            throw new InvalidCacheDirException('Directory `' . $this->cacheDir . '` is not writable');
        }

        return file_put_contents($this->cacheDir . $filename, $factory) === strlen($factory);
    }

}