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

/*use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Zikula\Bundle\HookBundle\Hook\Hook;*/

class HookedObjectRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HookedObjectRepository
     * /
    private $repository;

    public function setUp()
    {
        $emStub = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $classMetadataStub = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = new HookedObjectRepository($emStub, $classMetadataStub);
    }

    public function testIsSomethingHookedMethod()
    {
        $hookStub = $this->getMockBuilder(Hook::class)
            ->getMock();
        $this->repository->getByHookOrCreate($hookStub);
    }*/

    public function testTest()
    {
        $this->assertTrue(true);
    }
}
