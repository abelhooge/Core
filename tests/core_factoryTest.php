<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */

use FuzeWorks\Factory;

/**
 * Class FactoryTest.
 *
 * Will test the FuzeWorks Factory.
 */
class factoryTest extends CoreTestAbstract
{
    public function testCanLoadFactory()
    {
        $this->assertInstanceOf('FuzeWorks\Factory', Factory::getInstance());
    }

    /**
     * @depends testCanLoadFactory
     */
    public function testLoadSameInstance()
    {
        $this->assertSame(Factory::getInstance(), Factory::getInstance());
    }

    /**
     * @depends testCanLoadFactory
     */
    public function testLoadDifferentInstance()
    {
        // First a situation where one is the shared instance and one is a cloned instance
        $this->assertNotSame(Factory::getInstance(), Factory::getInstance(true));

        // And a situation where both are cloned instances
        $this->assertNotSame(Factory::getInstance(true), Factory::getInstance(true));
    }

    /**
     * @depends testCanLoadFactory
     */
    public function testObjectsSameInstance()
    {
        // Create mock
        $mock = $this->getMock('MockInstance');

        // Test not set
        $this->assertNull(Factory::getInstance()->mock);

        // Same instance factories
        $factory1 = Factory::getInstance()->setInstance('Mock', $mock);
        $factory2 = Factory::getInstance()->setInstance('Mock', $mock);

        // Return the mocks
        $this->assertSame($factory1->mock, $factory2->mock);

        // Different instance factories
        $factory3 = Factory::getInstance(true)->setInstance('Mock', $mock);
        $factory4 = Factory::getInstance(true)->setInstance('Mock', $mock);

        // Return the mocks
        $this->assertSame($factory3->mock, $factory4->mock);
    }

    /**
     * @depends testObjectsSameInstance
     */
    public function testObjectsDifferentInstance()
    {
        // Create mock
        $mock = $this->getMock('MockInstance');

        // Test not set
        $this->assertNull(Factory::getInstance()->mock);

        // Same instance factories
        $factory1 = Factory::getInstance()->setInstance('Mock', $mock);
        $factory2 = Factory::getInstance()->setInstance('Mock', $mock);

        // Clone the instance in factory2
        $factory2->cloneInstance('Mock');

        // Should be true, since both Factories use the same Mock instance
        $this->assertSame($factory1->mock, $factory2->mock);

        // Different instance factories
        $factory3 = Factory::getInstance(true)->setInstance('Mock', $mock);
        $factory4 = Factory::getInstance(true)->setInstance('Mock', $mock);

        // Clone the instance in factory4
        $factory4->cloneInstance('Mock');

        // Should be false, since both Factories use a different Mock instance
        $this->assertNotSame($factory3->mock, $factory4->mock);
    }

    public function testGlobalCloneInstance()
    {
        // First test without global cloning
        $this->assertSame(Factory::getInstance(), Factory::getInstance());

        // Now enable global cloning
        Factory::enableCloneInstances();

        // Now test without global cloning
        $this->assertNotSame(Factory::getInstance(), Factory::getInstance());

        // Disable global cloning
        Factory::disableCloneInstances();

        // And test again without global cloning
        $this->assertSame(Factory::getInstance(), Factory::getInstance());
    }

    public function testNewFactoryInstance()
    {
        // Load the different factories
        $factory = new Factory();
        $factory2 = Factory::getInstance();

        // Test if the objects are different factory instances
        $this->assertNotSame($factory, $factory2);

        // Fetch the instances
        $instances1 = $factory->getClassInstances();
        $instances2 = $factory2->getClassInstances();

        // And test if all ClassInstances are the same
        foreach ($instances1 as $className => $object) {
            $this->assertSame($object, $instances2[$className]);
        }

        // And test when changing one classInstance
        $factory->newInstance('Layout');
        $this->assertNotSame($factory->layout, $factory2->layout);
    }

    public function tearDown()
    {
        Factory::disableCloneInstances();
        
        $factory = Factory::getInstance();
        if (isset($factory->Mock))
        {
           $factory->removeInstance('Mock'); 
        }
    }

}
