<?php

/**
 * Class ModuleTest
 *
 * Core module testing suite, will test basic module functionality
 *
 */
class ModuleTest extends CoreTestAbstract
{

    /**
     * Tests the loading of a single module located in a folder
     */
    public function testFolderLoading(){

        $core = $this->createCore();

        $mod = $core->loadMod('mycorp/example');
        $this->assertInstanceOf('\Module\Example\Main', $mod);
    }

    /**
     * Test if a reloaded module is the same Id
     */
    public function testDuplicateInstance() {

        $core = $this->createCore();

        // The 2 instances which should be the same
        $mod = $core->loadMod('mycorp/example');
        $mod2 = $core->loadMod('mycorp/example');

        $this->assertEquals(spl_object_hash($mod), spl_object_hash($mod2));
    }

    /**
     * Test if the retrieved module info is correct
     */
    public function testModuleInfo() {
        $core = $this->createCore();
        $mod = $core->loadMod('mycorp/example');

        // The name
        $this->assertEquals('mycorp/example', $mod->getModuleName());

        // The directory
        $this->assertEquals('Modules/example/', $mod->getModulePath());
    }
}