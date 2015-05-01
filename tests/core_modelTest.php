<?php

/**
 * Class ModelTest
 *
 * Core model testing suite, will test model parent class
 *
 */
class ModelTest extends CoreTestAbstract
{

    /**
     * Tests wether FuzeWorks is able to load a simple Dummy Model
     */
    public function testModelLoading() {
         $core = $this->createCore();

         $core->mods->models->loadModel('dummy', 'tests/models/testModelLoading/');
         $this->assertInstanceOf('\Model\Dummy', $core->mods->models->dummy);
    }

    // PARENT INTERACTION VIA A DUMMY MODELSERVER IN DUMMY MODULE
}