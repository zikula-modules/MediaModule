<?php

namespace Cmfcmf\Module\MediaModule\Tests\DependencyInjection;

use Cmfcmf\Module\MediaModule\DependencyInjection\ImporterCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class ImporterCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testNothingHappensIfCollectionDoesNotExist()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['has'])
            ->getMock()
        ;
        $containerMock->expects($this->once())
            ->method('has')
            ->with($this->equalTo('cmfcmf_media_module.importer_collection'))
            ->willReturn(false)
        ;
        $containerMock->expects($this->never())
            ->method('findDefinition')
        ;

        $compilerPass = new ImporterCompilerPass();
        $compilerPass->process($containerMock);
    }

    public function testNothingHappensIfNoTaggedServices()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['has', 'findDefinition', 'findTaggedServiceIds'])
            ->getMock()
        ;
        $containerMock->expects($this->once())
            ->method('has')
            ->with($this->equalTo('cmfcmf_media_module.importer_collection'))
            ->willReturn(true)
        ;
        $containerMock->expects($this->any())
            ->method('findDefinition')
            ->with($this->equalTo('cmfcmf_media_module.importer_collection'))
            ->willReturn(null)
        ;
        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('cmfcmf_media_module.importer'))
            ->willReturn([])
        ;

        $compilerPass = new ImporterCompilerPass();
        $compilerPass->process($containerMock);
    }

    public function testItWorksIfTaggedServicesAvailable()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['has', 'findDefinition', 'findTaggedServiceIds'])
            ->getMock()
        ;
        $containerMock->expects($this->once())
            ->method('has')
            ->with($this->equalTo('cmfcmf_media_module.importer_collection'))
            ->willReturn(true)
        ;
        $definitionMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->setMethods(['addMethodCall'])
            ->getMock()
        ;
        $definitionMock->expects($this->exactly(2))
            ->method('addMethodCall')
            ->withConsecutive([
                'addImporter',
                [new Reference('foo')],
            ], [
                'addImporter',
                [new Reference('bar')]
            ])
        ;
        $containerMock->expects($this->any())
            ->method('findDefinition')
            ->with($this->equalTo('cmfcmf_media_module.importer_collection'))
            ->willReturn($definitionMock)
        ;
        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('cmfcmf_media_module.importer'))
            ->willReturn([
                'foo' => [],
                'bar' => ['a', 'b']
            ])
        ;

        $compilerPass = new ImporterCompilerPass();
        $compilerPass->process($containerMock);
    }
}
