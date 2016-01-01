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
use Zikula_Event;

/**
 * Listens to Scribite events.
 */
class ThirdPartyListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $zikulaRoot;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $resourceRoot;

    /**
     * @param string $kernelRootDir The kernel root directory.
     */
    public function __construct($kernelRootDir)
    {
        $this->zikulaRoot = realpath($kernelRootDir . '/..');
        $this->resourceRoot = realpath(__DIR__ . '/../Resources');
        $this->fs = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'moduleplugin.ckeditor.externalplugins' => 'getCKEditorPlugins',
            'module.scribite.editorhelpers' => 'getScribiteEditorHelpers'
        ];
    }

    /**
     * Adds external plugin to CKEditor.
     *
     * @param Zikula_Event $event The event instance.
     */
    public function getCKEditorPlugins(Zikula_Event $event)
    {
        $plugins = $event->getSubject();
        $plugins->add([
            'name' => 'cmfcmfmediamodule',
            'path' => $this->fs->makePathRelative($this->resourceRoot . '/public/js/CKEditorPlugin', $this->zikulaRoot),
            'file' => 'plugin.js',
            'img'  => $this->fs->makePathRelative($this->resourceRoot . '/public/images', $this->zikulaRoot) . 'admin.png'
        ]);
    }

    /**
     * Adds extra JS to load on pages using the Scribite editor.
     *
     * @param Zikula_Event $event
     */
    public function getScribiteEditorHelpers(Zikula_Event $event)
    {
        // intended is using the add() method to add a helper like below
        $helpers = $event->getSubject();

        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'javascript',
            'path'   => $this->fs->makePathRelative($this->resourceRoot . '/public/js/vendor', $this->zikulaRoot) . 'toastr.min.js'
        ]);
        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'stylesheet',
            'path'   => $this->fs->makePathRelative($this->resourceRoot . '/public/css/vendor', $this->zikulaRoot) . 'toastr.min.css'
        ]);
        $helpers->add([
            'module' => 'CmfcmfMediaModule',
            'type'   => 'javascript',
            'path'   => $this->fs->makePathRelative($this->resourceRoot . '/public/js', $this->zikulaRoot) . 'util.js'
        ]);
    }
}
