<?php

namespace Cmfcmf\Module\MediaModule\Tests\DependencyInjection;


use Cmfcmf\Module\MediaModule\DependencyInjection\CollectionTemplateCompilerPass;
use Cmfcmf\Module\MediaModule\DependencyInjection\MediaTypeCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class CollectionTemplateCompilerPassTest extends \PHPUnit_Framework_TestCase
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
            ->with($this->equalTo('cmfcmf_media_module.collection_template_collection'))
            ->willReturn(false)
        ;
        $containerMock->expects($this->never())
            ->method('findDefinition')
        ;

        $compilerPass = new CollectionTemplateCompilerPass();
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
            ->with($this->equalTo('cmfcmf_media_module.collection_template_collection'))
            ->willReturn(true)
        ;
        $containerMock->expects($this->any())
            ->method('findDefinition')
            ->with($this->equalTo('cmfcmf_media_module.collection_template_collection'))
            ->willReturn(null)
        ;
        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('cmfcmf_media_module.collection_template'))
            ->willReturn([])
        ;

        $compilerPass = new CollectionTemplateCompilerPass();
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
            ->with($this->equalTo('cmfcmf_media_module.collection_template_collection'))
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
                'addCollectionTemplate',
                [new Reference('foo')],
            ], [
                'addCollectionTemplate',
                [new Reference('bar')]
            ])
        ;
        $containerMock->expects($this->any())
            ->method('findDefinition')
            ->with($this->equalTo('cmfcmf_media_module.collection_template_collection'))
            ->willReturn($definitionMock)
        ;
        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('cmfcmf_media_module.collection_template'))
            ->willReturn([
                'foo' => [],
                'bar' => ['a', 'b']
            ])
        ;

        $compilerPass = new CollectionTemplateCompilerPass();
        $compilerPass->process($containerMock);
    }
}
