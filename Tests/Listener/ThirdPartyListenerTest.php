<?php

namespace Cmfcmf\Module\MediaModule\Tests\Listener;

use Cmfcmf\Module\MediaModule\Listener\ThirdPartyListener;

class ThirdPartyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThirdPartyListener
     */
    private $listener;

    public function setUp()
    {
        if (!class_exists('Scribite_EditorHelper')) {
            $this->markTestSkipped(
                'The Scribite module is not installed.'
            );
        }
        $this->listener = new ThirdPartyListener(__DIR__ . '/../../../..');
        \ModUtil::registerAutoloaders();
    }

    public function testIfEventMethodsExist()
    {
        $events = $this->listener->getSubscribedEvents();
        foreach ($events as $event => $method) {
            $this->assertTrue(method_exists($this->listener, $method));
        }
    }

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
                    $arr['module'] == 'CmfcmfMediaModule'
                    && is_readable($arr['path'])
                    && in_array($arr['type'], ['stylesheet', 'javascript'])
                ;
            }))
            ->willReturn(null)
        ;

        $this->listener->getScribiteEditorHelpers($eventStub);
    }

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
                return $arr['name'] == 'cmfcmfmediamodule'
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
