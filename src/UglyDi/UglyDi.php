<?php
/**
 * Created by Gary Hockin.
 * Date: 18/09/2014
 * @GeeH
 */

namespace UglyDi;


use ReflectionClass;
use ReflectionParameter;
use UglyDi\Exception\InvalidClassException;
use Zend\Stdlib\ArrayUtils;

class UglyDi
{
    /**
     * @var array
     */
    protected $created = [];
    /**
     * @var bool
     */
    protected $reuse = true;
    /**
     * @var array
     */
    protected $config = [];


    /**
     * @param null $config
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            return true;
        }
        if (is_array($config)) {
            $this->config = $config;
            return true;
        }
        if (file_exists($config)) {
            $this->config = require($config);
            return true;
        }
        throw new \InvalidArgumentException('Config at path `' . $config . '` not found');
    }

    /**
     * @param $className
     * @param array $userArguments
     * @internal param bool $fillOptional
     * @return object
     */
    public function get($className, $userArguments = [])
    {
        // if this has been created before, and we should reuse classes, return that class
        if (array_key_exists($className, $this->created) && $this->reuse) {
            return $this->created[$className];
        }

        if (array_key_exists($className, $this->config)) {
            $userArguments = ArrayUtils::merge($userArguments, $this->config[$className]);

        }

        $reflector = $this->getReflector($className);

        // if there is a constructor with no parameters(?), just create class
        if (!$reflector->getConstructor() || $reflector->getConstructor()->getNumberOfParameters() === 0) {
            return $this->createAndStoreNewClass($className, $reflector);
        }

        // if there are no required parameters and we don't need to auto fill the optional dependencies
        // and no user arguments
        if ($reflector->getConstructor()->getNumberOfRequiredParameters() === 0
            && empty($userArguments)
        ) {
            return $this->createAndStoreNewClass($className, $reflector);
        }

        $userArguments = $this->getAndFillArguments($reflector, $userArguments);

        return $this->createAndStoreNewClass($className, $reflector, $userArguments);

    }

    /**
     * @param $className
     * @return ReflectionClass
     * @throws InvalidClassException
     */
    public function getReflector($className)
    {
        // can we create this class?
        if (!class_exists($className)) {
            throw new InvalidClassException("Cannot find class `$className`");
        }

        $reflector = new ReflectionClass($className);
        return $reflector;
    }

    /**
     * @param $className
     * @param ReflectionClass $reflectionClass
     * @param array $arguments
     * @return object
     */
    private function createAndStoreNewClass($className, ReflectionClass $reflectionClass, $arguments = [])
    {
        $class = $reflectionClass->newInstanceArgs($arguments);
        $this->setCreatedClass($className, $class);
        return $class;
    }

    /**
     * @param $className
     * @param $object
     */
    public function setCreatedClass($className, $object)
    {
        $this->created[$className] = $object;
    }

    /**
     * @param ReflectionClass $reflector
     * @param array $userArguments
     * @return array
     */
    private function getAndFillArguments(ReflectionClass $reflector, array $userArguments)
    {
        $classArguments     = $reflector->getConstructor()->getParameters();
        $completedArguments = [];
        foreach ($classArguments as $argument) {
            $completedArguments[] = $this->fillArgument($argument, $userArguments);
        }
        return $completedArguments;
    }

    /**
     * @param ReflectionParameter $argument
     * @param array $userArguments
     * @return object
     */
    private function fillArgument(ReflectionParameter $argument, array $userArguments)
    {

        if (!$argument->getClass()
            && array_key_exists($argument->getName(), $userArguments)
        ) {
            return $userArguments[$argument->getName()];
        }

        if ($argument->getClass()
            && !array_key_exists($argument->getName(), $userArguments)
            && !$argument->isOptional()
        ) {
            return $this->get($argument->getClass()->getName());
        }

        if (!$argument->isOptional()) {
            return $this->get($userArguments[$argument->getName()]);
        }

        return false;
    }

} 