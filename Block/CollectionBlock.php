<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Block;

use Cmfcmf\Module\MediaModule\CollectionTemplate\SelectedTemplateFactory;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Form\Collection\CollectionBlockType;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

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
     * @var Environment
     */
    private $twig;

    /**
     * @var SelectedTemplateFactory
     */
    private $selectedTemplateFactory;

    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    public function __construct(
        AbstractExtension $extension,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        Environment $twig,
        CollectionRepository $collectionRepository,
        SecurityManager $securityManager,
        SelectedTemplateFactory $selectedTemplateFactory,
        MediaTypeCollection $mediaTypeCollection
    ) {
        parent::__construct($extension, $requestStack, $translator, $variableApi, $permissionApi, $twig);
        $this->collectionRepository = $collectionRepository;
        $this->securityManager = $securityManager;
        $this->selectedTemplateFactory = $selectedTemplateFactory;
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    /**
     * Display block.
     *
     * @param array $properties
     *
     * @return string|void The rendered block
     */
    public function display(array $properties): string
    {
        if (!$this->securityManager->hasPermissionRaw('CmfcmfMediaModule:collectionblock:', "{$properties['title']}::", ACCESS_READ)) {
            return '';
        }
        if (empty($properties['id'])) {
            return '';
        }

        $collection = $this->collectionRepository->findOneBy(['id' => $properties['id']]);
        if (!$collection) {
            return '';
        }
        if (!$this->securityManager->hasPermission($collection, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            return '';
        }

        try {
            $selectedTemplate = $this->selectedTemplateFactory->fromDB($properties['template']);
        } catch (\DomainException $e) {
            throw new NotFoundHttpException();
        }

        $content = $selectedTemplate->getTemplate()->render(
            $collection,
            $this->mediaTypeCollection,
            $properties['showChildCollections'] ?? false,
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
    public function getFormClassName(): string
    {
        return CollectionBlockType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->translator->trans('Collection', [], 'cmfcmfmediamodule');
    }
}
