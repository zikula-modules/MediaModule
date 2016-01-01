<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Tests\DependencyInjection;

use Cmfcmf\Module\MediaModule\DependencyInjection\FontCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class FontCompilerPassTest extends \PHPUnit_Framework_TestCase
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
            ->with($this->equalTo('cmfcmf_media_module.font_collection'))
            ->willReturn(false)
        ;
        $containerMock->expects($this->never())
            ->method('findDefinition')
        ;

        $compilerPass = new FontCompilerPass();
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
            ->with($this->equalTo('cmfcmf_media_module.font_collection'))
            ->willReturn(true)
        ;
        $containerMock->expects($this->any())
            ->method('findDefinition')
            ->with($this->equalTo('cmfcmf_media_module.font_collection'))
            ->willReturn(null)
        ;
        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('cmfcmf_media_module.font'))
            ->willReturn([])
        ;

        $compilerPass = new FontCompilerPass();
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
            ->with($this->equalTo('cmfcmf_media_module.font_collection'))
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
                'addFontLoader',
                [new Reference('foo')],
            ], [
                'addFontLoader',
                [new Reference('bar')]
            ])
        ;
        $containerMock->expects($this->any())
            ->method('findDefinition')
            ->with($this->equalTo('cmfcmf_media_module.font_collection'))
            ->willReturn($definitionMock)
        ;
        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('cmfcmf_media_module.font'))
            ->willReturn([
                'foo' => [],
                'bar' => ['a', 'b']
            ])
        ;

        $compilerPass = new FontCompilerPass();
        $compilerPass->process($containerMock);
    }
}
