<?php
/**
 * Created by Gary Hockin.
 * Date: 24/09/2014
 * @GeeH
 */

namespace UglyDiTest\Generator;


use UglyDi\Generator\Generator;
use UglyDi\UglyDi;
use UglyDiTest\Asset\NoConstructor;
use UglyDiTest\Asset\OneConstructor;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testGenerationForClassWithNoDependencies()
    {
        $className = NoConstructor::class;
        $arguments = [];

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir);


        $result = $generator->generateFactory($className, $arguments, $arguments);
        $this->assertTrue($result);

        $function = require($cacheDir . '/' . sha1($className) . '.php');

        $class = call_user_func($function, new UglyDi, $className, $arguments);
        $this->assertInstanceOf($className, $class);
    }


    public function testGenerationForClassWithSimpleDependency()
    {
        $className = OneConstructor::class;
        $uglyDi    = new UglyDi();
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir);

        $result = $generator->generateFactory($className, $arguments, []);
        $this->assertTrue($result);

        $function = require($cacheDir . '/' . sha1($className) . '.php');

        $class = call_user_func($function, new UglyDi, $className, $arguments);
        $this->assertInstanceOf($className, $class);
    }

    public function testGenerationForClassWithArguments()
    {
        $className      = Adapter::class;
        $userParameters = [
            'driver' => [
                'driver'   => 'Mysqli',
                'database' => 'zend_db_example',
                'username' => 'developer',
                'password' => 'developer-password'
            ],
        ];

        $uglyDi    = new UglyDi();
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir);

        $result = $generator->generateFactory($className, $arguments, $userParameters);
        $this->assertTrue($result);

        $function = require($cacheDir . '/' . sha1($className) . '.php');

        $class = call_user_func($function, new UglyDi, $className, $arguments);
        $this->assertInstanceOf($className, $class);

    }

    public function testGenerationForClassWithWeirdArguments()
    {
        $className      = Adapter::class;
        $userParameters = [
            'driver'               => [
                'driver'   => 'Mysqli',
                'database' => 'zend_db_example',
                'username' => 'developer',
                'password' => 'developer-password'
            ],
            'queryResultPrototype' => ResultSet::class,
        ];

        $uglyDi    = new UglyDi();
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir);

        $result = $generator->generateFactory($className, $arguments, $userParameters);
        $this->assertTrue($result);

        $function = require($cacheDir . '/' . sha1($className) . '.php');

        $class = call_user_func($function, new UglyDi, $className, $arguments);
        $this->assertInstanceOf($className, $class);

    }

    public function testGenerationForClassWithLotsOfDependencies()
    {
        
    }

}
 