<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Block;

use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Form\Collection\CollectionBlockType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * Collection block handler.
 */
class CollectionBlock extends AbstractBlockHandler
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

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

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->translator = $container->get('translator');
        $this->collectionRepository = $container->get('doctrine.orm.entity_manager')
            ->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');
        $this->securityManager = $container->get('cmfcmf_media_module.security_manager');
        $this->twig = $container->get('twig');
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

        $selectedTemplateFactory = $this->get('cmfcmf_media_module.collection_template.selected_factory');
        try {
            $selectedTemplate = $selectedTemplateFactory->fromDB($properties['template']);
        } catch (\DomainException $e) {
            throw new NotFoundHttpException();
        }

        $content = $selectedTemplate->getTemplate()->render(
            $collection,
            $this->get('cmfcmf_media_module.media_type_collection'),
            isset($properties['showChildCollections']) ? $properties['showChildCollections'] : false,
            $selectedTemplate->getOptions()
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
        return CollectionBlockType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->translator->trans('Collection', [], 'cmfcmfmediamodule');
    }
}
