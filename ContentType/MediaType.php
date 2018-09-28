<?php

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
use Doctrine\ORM\EntityManagerInterface;
use Zikula\Common\Content\AbstractContentType;
use Zikula\Common\Content\ContentTypeInterface;

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
    public function getCategory()
    {
        return ContentTypeInterface::CATEGORY_BASIC;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'picture-o';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->__('Media detail', 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->__('Display a single medium.', 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData()
    {
        return [
            'id' => null
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTranslatableDataFields()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function displayView()
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
    public function displayEditing()
    {
        if (null === $this->data['id'] || empty($this->data['id'])) {
            return $this->translator->__('No medium selected.', 'cmfcmfmediamodule');
        }

        return parent::displayEditing();
    }

    /**
     * {@inheritdoc}
     */
    public function getEditFormClass()
    {
        $this->customInit();

        return FormType::class;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->mediaRepository = $em->getRepository('CmfcmfMediaModule:Media\AbstractMediaEntity');
    }

    /**
     * @param SecurityManager $securityManager
     */
    public function setSecurityManager(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * @param MediaTypeCollection $mediaTypeCollection
     */
    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    /**
     * @param boolean $enableMediaViewCounter
     */
    public function setEnableMediaViewCounter($enableMediaViewCounter)
    {
        $this->enableMediaViewCounter = $enableMediaViewCounter;
    }

    private function customInit()
    {
        $this->bundleName = 'CmfcmfMediaModule';
        $this->domain = strtolower($this->bundleName);

        include_once __DIR__ . '/../bootstrap.php';
    }
}
