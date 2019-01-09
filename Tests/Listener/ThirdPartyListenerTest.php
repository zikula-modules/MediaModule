<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Tests\Listener;

use Cmfcmf\Module\MediaModule\Listener\ThirdPartyListener;
use Symfony\Component\Filesystem\Filesystem;

class ThirdPartyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThirdPartyListener
     */
    private $listener;

    public function setUp()
    {
        if (!interface_exists('\\Zikula\\ScribiteModule\\Editor\\EditorHelperInterface')) {
            $this->markTestSkipped(
                'The Scribite module is not installed.'
            );
        }
        $this->listener = new ThirdPartyListener(new Filesystem(), __DIR__ . '/../../../..');
    }

    public function testIfEventMethodsExist()
    {
        $events = $this->listener->getSubscribedEvents();
        foreach ($events as $event => $method) {
            $this->assertTrue(method_exists($this->listener, $method));
        }
    }

    // TODO update obsolete test implementation
    public function testIfScribiteHelpersAreRegistered()
    {
        $eventStub = $this->getMockBuilder('Zikula_Event')
            ->setMethods(['getSubject'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $scribiteHelperStub = $this->getMockBuilder('Scribite_EditorHelper')
            ->getMock()
        ;

        $eventStub->expects($this->once())
            ->method('getSubject')
            ->willReturn($scribiteHelperStub)
        ;

        $scribiteHelperStub->expects($this->exactly(3))
            ->method('add')
            ->with($this->callback(function ($arr) {
                return
                    'CmfcmfMediaModule' == $arr['module']
                    && is_readable($arr['path'])
                    && in_array($arr['type'], ['stylesheet', 'javascript'])
                ;
            }))
            ->willReturn(null)
        ;

        $this->listener->getScribiteEditorHelpers($eventStub);
    }

    // TODO update obsolete test implementation
    public function testIfCKEditorPluginsAreRegistered()
    {
        $eventStub = $this->getMockBuilder('Zikula_Event')
            ->setMethods(['getSubject'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $scribiteHelperStub = $this->getMockBuilder('Scribite_EditorHelper')
            ->getMock()
        ;

        $eventStub->expects($this->once())
            ->method('getSubject')
            ->willReturn($scribiteHelperStub)
        ;

        $scribiteHelperStub->expects($this->once())
            ->method('add')
            ->with($this->callback(function ($arr) {
                return 'cmfcmfmediamodule' == $arr['name']
                    && is_dir($arr['path'])
                    && is_readable($arr['path']) . '/' . $arr['file']
                    //&& is_readable($arr['img'])
                ;
            }))
            ->willReturn(null)
        ;

        $this->listener->getCKEditorPlugins($eventStub);
    }
}
