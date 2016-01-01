<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Tests\Entity\Repository;

class HookedObjectRepositoryTest extends \PHPUnit_Framework_TestCase
{
    //    /**
//     * @var HookedObjectRepository
//     */
//    private $repository;
//
//    public function setUp()
//    {
//        $emStub = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
//            ->getMock();
//        $classMetadataStub = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $this->repository = new HookedObjectRepository($emStub, $classMetadataStub);
//    }
//
//    public function testIsSomethingHookedMethod()
//    {
//        $hookStub = $this->getMockBuilder('Zikula\Component\HookDispatcher\Hook')
//            ->getMock();
//        $this->repository->getByHookOrCreate($hookStub);
//    }

    public function testTest()
    {
        $this->assertTrue(true);
    }
}
