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

use Cmfcmf\Module\MediaModule\CollectionTemplate\SelectedTemplateFactory;
use Cmfcmf\Module\MediaModule\ContentType\Form\Type\CollectionType as FormType;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Common\Content\AbstractContentType;
use Zikula\Common\Content\ContentTypeInterface;

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
    public function getCategory()
    {
        return ContentTypeInterface::CATEGORY_BASIC;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'folder-o';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->__('Media collection', 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->__('Display a media collection.', 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData()
    {
        return [
            'id' => null,
            'template' => ''
        ];
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
    public function displayEditing()
    {
        if (null === $this->data['id'] || empty($this->data['id'])) {
            return $this->__('No collection selected.', 'cmfcmfmediamodule');
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
        $this->collectionRepository = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');
    }

    /**
     * @param SecurityManager $securityManager
     */
    public function setSecurityManager(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * @param SelectedTemplateFactory $selectedTemplateFactory
     */
    public function setSelectedTemplateFactory(SelectedTemplateFactory $selectedTemplateFactory)
    {
        $this->selectedTemplateFactory = $selectedTemplateFactory;
    }

    /**
     * @param MediaTypeCollection $mediaTypeCollection
     */
    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    private function customInit()
    {
        $this->bundleName = 'CmfcmfMediaModule';
        $this->domain = strtolower($this->bundleName);

        include_once __DIR__ . '/../bootstrap.php';
    }
}
