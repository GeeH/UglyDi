<?php

namespace UglyUserTest;

use UglyDi\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Module
     */
    protected $module;

    /**
     *
     */
    public function setUp()
    {
        $this->module = new Module();
    }

    /**
     * Checks the autoloading for module exists at least
     */
    public function testGetAutoLoaderConfig()
    {
        $result = $this->module->getAutoloaderConfig();
        $this->assertArrayHasKey('UglyDi', $result['Zend\Loader\StandardAutoloader']['namespaces']);
    }

}