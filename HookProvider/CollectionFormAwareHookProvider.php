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

namespace Cmfcmf\Module\MediaModule\HookProvider;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Form\Type\EditCollectionType;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse;

/**
 * Collection hook provider.
 */
class CollectionFormAwareHookProvider extends AbstractFormAwareHookProvider
{
    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    public function getTitle(): string
    {
        return $this->translator->trans('Collection FormAware hooks provider');
    }

    public function getProviderTypes(): array
    {
        return [
            FormAwareCategory::TYPE_EDIT => 'edit',
            FormAwareCategory::TYPE_PROCESS_EDIT => 'processEdit'
        ];
    }

    /**
     * Provide the inner editing form.
     */
    public function edit(FormAwareHook $hook): void
    {
        $this->saveObjectId($hook->getId());

        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $selectedCollections = array_map(function (HookedObjectCollectionEntity $hookedObjectCollectionEntity) {
            return $hookedObjectCollectionEntity->getCollection()->getId();
        }, $hookedObject->getHookedObjectCollections()->getValues());

        $hook
            /*->formAdd('cmfcmfmediamodule_hook_editcollection', EditCollectionType::class, [
                'auto_initialize' => false,
                'mapped' => false
            ])*/
            ->addTemplate('@CmfcmfMediaModule/Collection/hookEdit.html.twig', [
                'selectedCollections' => $selectedCollections,
                'hookedObject' => $hookedObject
            ])
        ;
    }

    /**
     * Process the inner editing form.
     */
    public function processEdit(FormAwareResponse $hook): void
    {
        //$innerForm = $hook->getFormData('cmfcmfmediamodule_hook_editcollection');
        //$collectionData = $innerForm['collections'] ?? [];
        //$collectionIds = is_array($collectionData) ? $collectionData : json_decode($collectionData);

        // TODO migrate this to a proper inner form
        $collectionData = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[collections]', '[]', true);
        $collectionData = $_POST['cmfcmfmediamodule']['collections'] ?? [];
        $collectionIds = is_array($collectionData) ? $collectionData : json_decode($collectionData);

        /** @var CollectionEntity[] $entities */
        $entities = [];
        foreach ($collectionIds as $collectionId) {
            if (empty($collectionId)) {
                continue;
            }
            $collectionEntity = $this->entityManager->find(CollectionEntity::class, $collectionId);
            if (!is_object($collectionEntity)) {
                //addError('collections', $this->translator->trans('Unknown collection', [], 'cmfcmfmediamodule'));
            } else {
                $entities[] = $collectionEntity;
            }
        }

        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook, $this->restoreObjectId());

        $hookedObject->clearCollections();

        foreach ($entities as $collectionEntity) {
            $hookedObject->addCollection($collectionEntity, null, false, true);
        }

        $repository->saveOrDelete($hookedObject);
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.form_aware_hooks.collections';
    }

    /**
     * @required
     */
    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }
}
