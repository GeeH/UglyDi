<?php
/**
 * Created by Gary Hockin.
 * Date: 24/09/2014
 * @GeeH
 */

namespace UglyDiTest\Generator;


use BaconStringUtils\Slugifier;
use UglyDi\Generator\Generator;
use UglyDi\UglyDi;
use UglyDiTest\Asset\NoConstructor;
use UglyDiTest\Asset\OneConstructor;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Slugifier
     */
    protected $slugifier;

    public function setUp()
    {
        $this->slugifier = new Slugifier();
    }

    public function testGenerationForClassWithNoDependencies()
    {
        $className = NoConstructor::class;
        $arguments = [];

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir, new Slugifier());


        $result = $generator->generateFactory($className, $arguments, $arguments);
        $this->assertTrue($result);

        $function = require($generator->getFileName($className, $arguments));

        $class = call_user_func($function, new UglyDi($generator), $className, $arguments);
        $this->assertInstanceOf($className, $class);
    }


    public function testGenerationForClassWithSimpleDependency()
    {
        $className = OneConstructor::class;

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir, new Slugifier());

        $uglyDi    = new UglyDi($generator);
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $result = $generator->generateFactory($className, $arguments, []);
        $this->assertTrue($result);

        $function = require($generator->getFileName($className, []));

        $class = call_user_func($function, new UglyDi($generator), $className, $arguments);
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

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir, new Slugifier());

        $uglyDi    = new UglyDi($generator);
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $result = $generator->generateFactory($className, $arguments, $userParameters);
        $this->assertTrue($result);

        $function = require($generator->getFileName($className, $userParameters));

        $class = call_user_func($function, new UglyDi($generator), $className, $arguments);
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

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir, new Slugifier());

        $uglyDi    = new UglyDi($generator);
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $result = $generator->generateFactory($className, $arguments, $userParameters);
        $this->assertTrue($result);

        $function = require($generator->getFileName($className, $userParameters));

        $class = call_user_func($function, new UglyDi($generator), $className, $arguments);
        $this->assertInstanceOf($className, $class);

    }

    public function testGenerationForClassWithLotsOfDependencies()
    {
        /**
         * BAD COPY AND PASTE JOB, MAKE BETTER
         *
         */
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

        $cacheDir  = './tests/UglyDiTest/cache';
        $generator = new Generator($cacheDir, new Slugifier());

        $uglyDi    = new UglyDi($generator);
        $reflector = $uglyDi->getReflector($className);
        $arguments = $reflector->getConstructor()->getParameters();

        $result = $generator->generateFactory($className, $arguments, $userParameters);
        $this->assertTrue($result);

        $function = require($generator->getFileName($className, $userParameters));

        $class = call_user_func($function, new UglyDi($generator), $className, $arguments);
        $this->assertInstanceOf($className, $class);

        $className      = TableGateway::class;
        $userParameters = [
            'table' => 'table'
        ];

    }

}
 