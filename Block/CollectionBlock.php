<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Cmfcmf\Module\MediaModule\Block;

use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * Class PageBlock
 * @package Zikula\PagesModule\Block
 */
class CollectionBlock extends AbstractBlockHandler
{
    /**
     * @var CollectionRepository
     */
    private $collectionRepository;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->collectionRepository = $container->get('doctrine.orm.entity_manager')
            ->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');
        $this->securityManager = $container->get('cmfcmf_media_module.security_manager');
        $this->twig = $container->get('twig');
        $this->translator = $container->get('translator');
    }
    /**
     * Display block.
     *
     * @param array $properties
     *
     * @return string|void The rendered block
     */
    public function display(array $properties)
    {
        if (!$this->securityManager->hasPermissionRaw('CmfcmfMediaModule:collectionblock:', "{$properties['title']}::", ACCESS_READ)) {
            return false;
        }
        if (empty($properties['id'])) {
            return false;
        }

        $collection = $this->collectionRepository->findOneBy(['id' => $properties['id']]);
        if (!$collection) {
            return false;
        }
        if (!$this->securityManager->hasPermission($collection, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            return false;
        }

        $content = $this->get('cmfcmf_media_module.collection_template_collection')->getCollectionTemplate($properties['template'])->render(
            $collection,
            $this->get('cmfcmf_media_module.media_type_collection'),
            isset($properties['showChildCollections']) ? $properties['showChildCollections'] : false
        );

        $hook = '';
        if (isset($properties['showHooks']) && $properties['showHooks']) {
            // @todo enable hooks.
        }

        if (isset($properties['showEditAndDownloadLinks']) && $properties['showEditAndDownloadLinks']) {
            $content = $this->twig->render('@CmfcmfMediaModule/Collection/display.html.twig', [
                'collection' => $collection,
                'renderRaw' => true,
                'content' => $content,
                'hook' => $hook
            ]);
        } else {
            $content .= $hook;
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormClassName()
    {
        return 'Cmfcmf\Module\MediaModule\Form\Collection\CollectionBlockType';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'translator' => $this->translator,
            'securityManager' => $this->securityManager,
            'templateCollection' => $this->get('cmfcmf_media_module.collection_template_collection'),
            'collectionRepository' => $this->collectionRepository
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->translator->trans('Collection', [], 'cmfcmfmediamodule');
    }
}
