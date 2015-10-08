<?php

namespace Cmfcmf\Module\MediaModule\Tests\Listener;


use Cmfcmf\Module\MediaModule\Listener\ModuleListener;
use Zikula\Core\Event\ModuleStateEvent;

class ModuleListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testIfEventMethodsExist()
    {
        $emStub = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
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
        $emStub = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMockForAbstractClass()
        ;
        $eventStub = $this->getMockBuilder('Zikula\Core\Event\ModuleStateEvent')
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
        $emStub = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMockForAbstractClass()
        ;
        $repositoryStub = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Entity\HookedObject\Repository\HookedObjectRepository')
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
        $listener->moduleRemoved(new ModuleStateEvent(null, ['name' => 'FooBarModule']));
    }

    public function testIfItWorksWhenModuleIsSet()
    {
        $emStub = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMockForAbstractClass()
        ;
        $repositoryStub = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Entity\HookedObject\Repository\HookedObjectRepository')
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

        $moduleStub = $this->getMockBuilder('Zikula\Core\AbstractModule')
            ->getMock()
        ;
        $r = new \ReflectionClass($moduleStub);
        $p = $r->getProperty('name');
        $p->setAccessible(true);
        $p->setValue($moduleStub, 'FooBarModule');

        $listener = new ModuleListener($emStub);
        $listener->moduleRemoved(new ModuleStateEvent($moduleStub));
    }
}
