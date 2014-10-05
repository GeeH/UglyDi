<?php
/**
 * Created by Gary Hockin.
 * Date: 18/09/2014
 * @GeeH
 */

namespace UglyDi;


use ReflectionClass;
use UglyDi\Exception\InvalidClassException;
use UglyDi\Generator\GeneratorInterface;
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
     * @var GeneratorInterface
     */
    protected $generator;
    /**
     * @var bool
     */
    protected $alwaysGenerate = false;


    /**
     * @param GeneratorInterface $generator
     * @param null $config
     */
    public function __construct(GeneratorInterface $generator, $config = null)
    {
        $this->generator = $generator;

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
     * @return object
     */
    public function get($className, $userArguments = [])
    {
        // if this has been created before, and we should reuse classes, return that class
        if (array_key_exists($className, $this->created) && $this->reuse) {
            return $this->created[$className];
        }

        // merge user arguments with any config for this class in config array
        if (array_key_exists($className, $this->config)) {
            $userArguments = ArrayUtils::merge($userArguments, $this->config[$className]);
        }

        if (!$this->generator->exists($className) || $this->getAlwaysGenerate()) {
            $reflector = $this->getReflector($className);
            $parameters = $reflector->getConstructor() ? $reflector->getConstructor()->getParameters() : [];
            $this->generator->generateFactory($className, $parameters, $userArguments);
        }

        $factory = require($this->generator->getFileName($className));
        $class   = call_user_func($factory, $this);
        $this->setCreatedClass($className, $class);
        return $class;

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
     * @param $object
     */
    public function setCreatedClass($className, $object)
    {
        $this->created[$className] = $object;
    }

    /**
     * @return boolean
     */
    public function getAlwaysGenerate()
    {
        return $this->alwaysGenerate;
    }

    /**
     * @param boolean $alwaysGenerate
     */
    public function setAlwaysGenerate($alwaysGenerate)
    {
        $this->alwaysGenerate = (bool) $alwaysGenerate;
    }

} 