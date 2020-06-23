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
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationResponse;

/**
 * Collection ui hooks provider.
 */
class CollectionUiHooksProvider extends AbstractUiHooksProvider
{
    /**
     * @var CollectionEntity[]
     */
    private $entities;

    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    /**
     * Sets the media type collection.
     *
     * @param MediaTypeCollection $mediaTypeCollection
     */
    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    public function getTitle(): string
    {
        return $this->translator->trans('Collection ui hooks provider');
    }

    public function getProviderTypes(): array
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'uiView',
            UiHooksCategory::TYPE_FORM_EDIT => 'uiEdit',
            UiHooksCategory::TYPE_VALIDATE_EDIT => 'validateEdit',
            UiHooksCategory::TYPE_PROCESS_EDIT => 'processEdit',
            UiHooksCategory::TYPE_PROCESS_DELETE => 'processDelete'
        ];
    }

    /**
     * @param DisplayHook $hook
     */
    public function uiView(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $content = $this->twig->render('@CmfcmfMediaModule/Collection/hookView.html.twig', [
            'hookedObjectCollections' => $hookedObject->getHookedObjectCollections(),
            'mediaTypeCollection' => $this->mediaTypeCollection
        ]);

        $hook->setResponse(new DisplayHookResponse($this->getAreaName(), $content));
    }

    /**
     * @param DisplayHook $hook
     */
    public function uiEdit(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $selectedCollections = array_map(function (HookedObjectCollectionEntity $hookedObjectCollectionEntity) {
            return $hookedObjectCollectionEntity->getCollection()->getId();
        }, $hookedObject->getHookedObjectCollections()->getValues());

        $content = $this->twig->render('@CmfcmfMediaModule/Collection/hookEdit.html.twig', [
            'selectedCollections' => $selectedCollections,
            'hookedObject' => $hookedObject
        ]);

        $hook->setResponse(new DisplayHookResponse($this->getAreaName(), $content));
    }

    /**
     * @param ValidationHook $hook
     */
    public function validateEdit(ValidationHook $hook)
    {
        include_once __DIR__ . '/../bootstrap.php';

        // TODO migrate this to a FormAware hook provider
        $collectionData = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[collections]', '[]', true);
        $collectionData = $_POST['cmfcmfmediamodule']['collections'] ?? [];
        $collectionIds = is_array($collectionData) ? $collectionData : json_decode($collectionData);

        $this->entities = [];
        $validationResponse = new ValidationResponse('collections', $collectionIds);
        foreach ($collectionIds as $collectionId) {
            if (!empty($collectionId)) {
                $collectionEntity = $this->entityManager->find('CmfcmfMediaModule:Collection\CollectionEntity', $collectionId);
                if (!is_object($collectionEntity)) {
                    $validationResponse->addError('collections', $this->translator->trans('Unknown collection', [], 'cmfcmfmediamodule'));
                } else {
                    $this->entities[] = $collectionEntity;
                }
            }
        }

        $hook->setValidator($this->getAreaName(), $validationResponse);
    }

    /**
     * @param ProcessHook $hook
     */
    public function processEdit(ProcessHook $hook)
    {
        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearCollections();

        foreach ($this->entities as $collectionEntity) {
            // @todo Make these parameters non-static.
            $hookedObject->addCollection($collectionEntity, null, false, true);
        }

        $repository->saveOrDelete($hookedObject);
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.ui_hooks.collections';
    }
}
