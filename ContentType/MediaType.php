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

use Cmfcmf\Module\MediaModule\ContentType\Form\Type\MediaType as FormType;
use Cmfcmf\Module\MediaModule\Entity\Media\Repository\MediaRepository;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\ModuleInterface\Content\AbstractContentType;
use Zikula\ExtensionsModule\ModuleInterface\Content\ContentTypeInterface;

/**
 * Media content type.
 */
class MediaType extends AbstractContentType
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    /**
     * @var boolean
     */
    private $enableMediaViewCounter;

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
        return 'picture-o';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->translator->trans('Media detail');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->translator->trans('Display a single medium.');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData(): array
    {
        return [
            'id' => null
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslatableDataFields(): array
    {
        return ['id'];
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

        $medium = $this->mediaRepository->findOneBy(['id' => $this->data['id']]);
        if (!$medium) {
            return '';
        }
        if (!$this->securityManager->hasPermission($medium, CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS)) {
            return '';
        }

        return $this->twig->render('@CmfcmfMediaModule/Media/displayRaw.html.twig', [
            'mediaType' => $this->mediaTypeCollection->getMediaTypeFromEntity($medium),
            'entity' => $medium
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function displayEditing(): string
    {
        if (null === $this->data['id'] || empty($this->data['id'])) {
            return $this->translator->trans('No medium selected.');
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
        MediaRepository $mediaRepository,
        SecurityManager $securityManager,
        MediaTypeCollection $mediaTypeCollection,
        VariableApiInterface $variableApi
    ): void {
        $this->mediaRepository = $mediaRepository;
        $this->securityManager = $securityManager;
        $this->mediaTypeCollection = $mediaTypeCollection;
        $this->enableMediaViewCounter = $variableApi->get('CmfcmfMediaModule', 'enableMediaViewCounter', false);
    }

    private function customInit()
    {
        $this->bundleName = 'CmfcmfMediaModule';
        $this->domain = mb_strtolower($this->bundleName);

        include_once __DIR__ . '/../bootstrap.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets($context): array
    {
        $assets = parent::getAssets($context);
        if (in_array($context, [ContentTypeInterface::CONTEXT_EDIT, ContentTypeInterface::CONTEXT_TRANSLATION])) {
            $assets['js'][] = $this->assetHelper->resolve('@CmfcmfMediaModule:js/CmfcmfMediaModule.ContentType.Media.js');
        }

        return $assets;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsEntrypoint($context): ?string
    {
        if (ContentTypeInterface::CONTEXT_EDIT === $context) {
            return 'contentInitMediaEdit';
        }
        if (ContentTypeInterface::CONTEXT_TRANSLATION === $context) {
            return 'contentInitMediaTranslation';
        }

        return null;
    }
}
