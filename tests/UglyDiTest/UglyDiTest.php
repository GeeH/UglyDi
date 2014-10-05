<?php
/**
 * Created by Gary Hockin.
 * Date: 18/09/2014
 * @GeeH
 */

namespace UglyUserTest;


use BaconStringUtils\Slugifier;
use UglyDi\Generator\Generator;
use UglyDi\UglyDi;
use UglyDiTest\Asset\NoConstructor;
use UglyDiTest\Asset\OneConstructor;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;
use Zend\View\Model\ViewModel;

class UglyDiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UglyDi
     */
    protected $di;
    /**
     * @var Generator
     */
    protected $generator;

    public function testSetCreatedClassShortCircuits()
    {
        $class = new \stdClass();
        $this->di->setCreatedClass('My\New\Class\Name', $class);
        $this->assertEquals($this->di->get('My\New\Class\Name'), $class);
    }

    public function testNoClassThrowsException()
    {
        $this->setExpectedException('UglyDi\Exception\InvalidClassException');
        $this->di->get('BigBadBarry');
    }


    public function testNoConstructorClassCreatesClass()
    {
        $className = NoConstructor::class;
        $class     = $this->di->get($className);

        $this->assertInstanceOf($className, $class);
    }

    public function testOnlyOptionalDependenciesAndNotFillThem()
    {
        $className = ViewModel::class;
        $class     = $this->di->get($className);

        $this->assertInstanceOf($className, $class);
    }

    public function testOnlyOptionalDependenciesFillingThemWithArguments()
    {
        $className = ViewModel::class;
        /** @var ViewModel $class */
        $class = $this->di->get($className, [
                'variables' =>
                    ['bot' => 'clamps'],
            ]
        );

        $this->assertInstanceOf($className, $class);
        $this->assertEquals('clamps', $class->getVariable('bot'));
    }

    public function testOnlyOptionalDependenciesFillingThemWithDependency()
    {
        $className = OneConstructor::class;
        /** @var OneConstructor $class */
        $class = $this->di->get($className);

        $this->assertInstanceOf($className, $class);
        $this->assertInstanceOf(NoConstructor::class, $class->noConstructor);
    }


    public function testDependenciesThatHaveBeenPreConfigured()
    {
        $className = Adapter::class;
        $arguments = [
            'driver' => [
                'driver'   => 'Mysqli',
                'database' => 'zend_db_example',
                'username' => 'developer',
                'password' => 'developer-password'
            ]
        ];

        $class = $this->di->get($className, $arguments);
        $this->assertInstanceOf($className, $class);

        $className = TableGateway::class;
        $arguments = [
            'table'   => 'donbot',
            'adapter' => Adapter::class,
        ];
        $class     = $this->di->get($className, $arguments);
        $this->assertInstanceOf($className, $class);

    }

    public function testRandomComplexClass()
    {
        $className = ModuleManager::class;
        $arguments = [
            'modules' => [],
        ];
        $class     = $this->di->get($className, $arguments);
        $this->assertInstanceOf($className, $class);
    }

    public function testDependenciesThatHaveComeFromConfig()
    {
        $this->di  = new UglyDi($this->generator, 'tests/UglyDiTest/Asset/config.php');
        $className = Adapter::class;
        $class     = $this->di->get($className);
        $this->assertInstanceOf($className, $class);
    }

    public function testDependenciesThatComeFromConfigImplied()
    {
        $this->di  = new UglyDi($this->generator, 'tests/UglyDiTest/Asset/config.php');
        $className = TableGateway::class;
        $arguments = [
            'table'   => 'bender',
            'adapter' => Adapter::class,
        ];
        $class     = $this->di->get($className, $arguments);
        $this->assertInstanceOf($className, $class);
    }

    public function testNoConfigExcepts()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->di = new UglyDi($this->generator, 'this/wont/exist.php');
    }

    protected function setUp()
    {
        $cacheDir  = './tests/UglyDiTest/cache';
        $this->generator = new Generator($cacheDir, new Slugifier());
        $this->di = new UglyDi($this->generator);
        $this->di->setAlwaysGenerate(true);
    }
}
 