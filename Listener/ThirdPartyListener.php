<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Core\Event\GenericEvent;
use Zikula\ScribiteModule\Event\EditorHelperEvent;

/**
 * Listens to Scribite events.
 */
class ThirdPartyListener implements EventSubscriberInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param Filesystem   $filesystem
     * @param RequestStack $requestStack
     */
    public function __construct(Filesystem $filesystem, RequestStack $requestStack)
    {
        $this->filesystem = $filesystem;
        $this->requestStack = $requestStack;
    }
    
    /**
     * Makes our handlers known to the event system.
     */
    public static function getSubscribedEvents()
    {
        return [
            'module.scribite.editorhelpers'           => ['getEditorHelpers', 5],
            'moduleplugin.ckeditor.externalplugins'   => ['getCKEditorPlugins', 5],
            'moduleplugin.quill.externalplugins'      => ['getQuillPlugins', 5],
            'moduleplugin.summernote.externalplugins' => ['getSummernotePlugins', 5],
            'moduleplugin.tinymce.externalplugins'    => ['getTinyMcePlugins', 5]
        ];
    }

    /**
     * Listener for the `module.scribite.editorhelpers` event.
     *
     * This occurs when Scribite adds pagevars to the editor page.
     * CmfcmfMediaModule will use this to add a javascript helper to add custom items.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * @param EditorHelperEvent $event The event instance
     */
    public function getEditorHelpers(EditorHelperEvent $event)
    {
        // install assets for Scribite plugins
        $targetDir = 'web/modules/cmfcmfmedia';
        $finder = new Finder();
        if (!$this->filesystem->exists($targetDir)) {
            $this->filesystem->mkdir($targetDir, 0777);
            if (is_dir($originDir = 'modules/cmfcmf/media-module/Resources/public')) {
                $this->filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
            }
            if (is_dir($originDir = 'modules/cmfcmf/media-module/Resources/scribite')) {
                $targetDir .= '/scribite';
                $this->filesystem->mkdir($targetDir, 0777);
                $this->filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
            }
        }

        $basePath = $this->requestStack->getCurrentRequest()->getBasePath();
        $helpers = $event->getHelperCollection();
        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'javascript',
            'path'   => $basePath . '/web/modules/cmfcmfmedia/js/vendor/toastr.min.js'
        ]);
        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'stylesheet',
            'path'   => $basePath . '/web/modules/cmfcmfmedia/css/vendor/toastr.min.css'
        ]);
        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'javascript',
            'path'   => $basePath . '/web/modules/cmfcmfmedia/js/util.js'
        ]);
        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'javascript',
            'path'   => $basePath . '/web/modules/cmfcmfmedia/js/Finder/opener.js'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.ckeditor.externalplugins` event.
     *
     * Adds external plugin to CKEditor.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getCKEditorPlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'cmfcmfmediamodule',
            'path' => $this->requestStack->getCurrentRequest()->getBasePath() . '/web/modules/cmfcmfmedia/scribite/CKEditor/cmfcmfmediamodule/',
            'file' => 'plugin.js',
            'img'  => 'ed_cmfcmfmediamodule.gif'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.quill.externalplugins` event.
     *
     * Adds external plugin to Quill.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getQuillPlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'cmfcmfmediamodule',
            'path' => $this->requestStack->getCurrentRequest()->getBasePath() . '/web/modules/cmfcmfmedia/scribite/Quill/cmfcmfmediamodule/plugin.js'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.summernote.externalplugins` event.
     *
     * Adds external plugin to Summernote.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getSummernotePlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'cmfcmfmediamodule',
            'path' => $this->requestStack->getCurrentRequest()->getBasePath() . '/web/modules/cmfcmfmedia/scribite/Summernote/cmfcmfmediamodule/plugin.js'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.tinymce.externalplugins` event.
     *
     * Adds external plugin to TinyMce.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getTinyMcePlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'cmfcmfmediamodule',
            'path' => $this->requestStack->getCurrentRequest()->getBasePath() . '/web/modules/cmfcmfmedia/scribite/TinyMce/cmfcmfmediamodule/plugin.js'
        ]);
    }
}
