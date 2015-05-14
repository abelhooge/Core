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

        $core->addMod('tests/modules/testFolderLoading/moduleInfo.php');
        $mod = $core->loadMod('ci/folderloading');
        $this->assertInstanceOf('\Module\FolderLoading\Main', $mod);
    }

    /**
     * Tests the enabling and disabling of modules
     */
    public function testModuleEnabling(){

        $core = $this->createCore();

        $core->addMod('tests/modules/testFolderLoading/moduleInfo.php');

        //Enable a module
        $core->mods->modules->enableModule('ci/folderloading');
        $cfg = (object) require('tests/modules/testFolderLoading/moduleInfo.php');
        $this->assertEquals(true, $cfg->enabled);

        //Disable a module
        $core->mods->modules->disableModule('ci/folderloading');
        $cfg = (object) require('tests/modules/testFolderLoading/moduleInfo.php');
        $this->assertEquals(false, $cfg->enabled);

        $core->mods->modules->enableModule('ci/folderloading');
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

    /**
     * Tests the loading of a moduleInfo in an unknown directory
     * @throws ModuleException
     * @expectedException \FuzeWorks\moduleException
     */
    public function testLoadingUnknownModuleInfoDirectory(){

        $core = $this->createCore();
        
        $core->addMod("tests/moduleInfo.php");
    }
}