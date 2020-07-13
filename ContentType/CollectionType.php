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

namespace Cmfcmf\Module\MediaModule\ContentType;

use Cmfcmf\Module\MediaModule\CollectionTemplate\SelectedTemplateFactory;
use Cmfcmf\Module\MediaModule\ContentType\Form\Type\CollectionType as FormType;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\ExtensionsModule\ModuleInterface\Content\AbstractContentType;
use Zikula\ExtensionsModule\ModuleInterface\Content\ContentTypeInterface;

/**
 * Collection content type.
 */
class CollectionType extends AbstractContentType
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
     * @var SelectedTemplateFactory
     */
    private $selectedTemplateFactory;

    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    /**
     * {@inheritdoc}
     */
    public function getCategory(): string
    {
        return ContentTypeInterface::CATEGORY_BASIC;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'folder-o';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->translator->trans('Media collection');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->translator->trans('Display a media collection.');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData(): array
    {
        return [
            'id' => null,
            'template' => ''
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function displayView(): string
    {
        $this->customInit();

        $this->data = $this->getData();
        if (null === $this->data['id'] || empty($this->data['id'])) {
            return '';
        }

        $collection = $this->collectionRepository->findOneBy(['id' => $this->data['id']]);
        if (!$collection) {
            return '';
        }
        if (!$this->securityManager->hasPermission($collection, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            return '';
        }

        try {
            $selectedTemplate = $this->selectedTemplateFactory->fromDB($this->data['template']);
        } catch (\DomainException $e) {
            throw new NotFoundHttpException();
        }

        $content = $selectedTemplate->getTemplate()->render(
            $collection,
            $this->mediaTypeCollection,
            isset($this->data['showChildCollections']) ? $this->data['showChildCollections'] : false,
            $selectedTemplate->getOptions()
        );

        if (isset($this->data['showEditAndDownloadLinks']) && $this->data['showEditAndDownloadLinks']) {
            $content = $this->twig->render('@CmfcmfMediaModule/Collection/display.html.twig', [
                'collection' => $collection,
                'renderRaw' => true,
                'content' => $content,
                'hook' => ''
            ]);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function displayEditing(): string
    {
        if (null === $this->data['id'] || empty($this->data['id'])) {
            return $this->translator->trans('No collection selected.');
        }

        return parent::displayEditing();
    }

    /**
     * {@inheritdoc}
     */
    public function getEditFormClass(): string
    {
        $this->customInit();

        return FormType::class;
    }

    /**
     * @required
     */
    public function injectAdditions(
        CollectionRepository $collectionRepository,
        SecurityManager $securityManager,
        SelectedTemplateFactory $selectedTemplateFactory,
        MediaTypeCollection $mediaTypeCollection
    ): void {
        $this->collectionRepository = $collectionRepository;
        $this->securityManager = $securityManager;
        $this->selectedTemplateFactory = $selectedTemplateFactory;
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    private function customInit()
    {
        $this->bundleName = 'CmfcmfMediaModule';
        $this->domain = mb_strtolower($this->bundleName);

        include_once __DIR__ . '/../bootstrap.php';
    }
}
