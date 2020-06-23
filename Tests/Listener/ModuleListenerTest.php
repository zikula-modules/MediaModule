<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Tests\Listener;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\Repository\HookedObjectRepository;
use Cmfcmf\Module\MediaModule\Listener\ModuleListener;
use Doctrine\ORM\EntityManagerInterface;
use Zikula\ExtensionsModule\AbstractModule;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;

class ModuleListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testIfEventMethodsExist()
    {
        $emStub = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMockForAbstractClass()
        ;

        $listener = new ModuleListener($emStub);
        $events = $listener->getSubscribedEvents();
        foreach ($events as $event => $method) {
            $this->assertTrue(method_exists($listener, $method));
        }
    }

    public function testIfNothingHappensWhenModuleIsNotSetAndNameIsEmpty()
    {
        $emStub = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMockForAbstractClass()
        ;
        $eventStub = $this->getMockBuilder(ExtensionStateEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $emStub
            ->expects($this->never())
            ->method('getRepository')
        ;
        $eventStub
            ->expects($this->any())
            ->method('getModule')
            ->willReturn(null)
        ;

        $listener = new ModuleListener($emStub);
        $listener->moduleRemoved($eventStub);
    }

    public function testIfItWorksWhenModuleIsNotSetButNameIsSet()
    {
        $emStub = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMockForAbstractClass()
        ;
        $repositoryStub = $this->getMockBuilder(HookedObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $emStub
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;
        $repositoryStub
            ->expects($this->once())
            ->method('deleteAllOfModule')
            ->with('FooBarModule')
        ;

        $listener = new ModuleListener($emStub);
        $extensionEntity = new ExtensionEntity();
        $extensionEntity->setName('FooBarModule');
        $listener->moduleRemoved(new ExtensionStateEvent(null, $extensionEntity));
    }

    public function testIfItWorksWhenModuleIsSet()
    {
        $emStub = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMockForAbstractClass()
        ;
        $repositoryStub = $this->getMockBuilder(HookedObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $emStub
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;
        $repositoryStub
            ->expects($this->once())
            ->method('deleteAllOfModule')
            ->with('FooBarModule')
        ;

        $moduleStub = $this->getMockBuilder(AbstractModule::class)
            ->getMock()
        ;
        $r = new \ReflectionClass($moduleStub);
        $p = $r->getProperty('name');
        $p->setAccessible(true);
        $p->setValue($moduleStub, 'FooBarModule');

        $listener = new ModuleListener($emStub);
        $listener->moduleRemoved(new ExtensionStateEvent($moduleStub));
    }
}
