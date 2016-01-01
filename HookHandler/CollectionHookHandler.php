<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\HookHandler;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationResponse;

/**
 * Handles collection hooks.
 */
class CollectionHookHandler extends AbstractHookHandler
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

    /**
     * @param DisplayHook $hook
     */
    public function uiView(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $content = $this->renderEngine->render('CmfcmfMediaModule:Collection:hookView.html.twig', [
            'hookedObjectCollections' => $hookedObject->getHookedObjectCollections(),
            'mediaTypeCollection' => $this->mediaTypeCollection
        ]);

        $this->uiResponse($hook, $content);
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

        $content = $this->renderEngine->render('CmfcmfMediaModule:Collection:hookEdit.html.twig', [
            'selectedCollections' => $selectedCollections,
            'hookedObject' => $hookedObject
        ]);
        $this->uiResponse($hook, $content);
    }

    /**
     * @param ValidationHook $hook
     */
    public function validateEdit(ValidationHook $hook)
    {
        $request = $this->requestStack->getCurrentRequest()->request;

        $collectionIds = json_decode($request->get('cmfcmfmediamodule[collections]', "[]", true));

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

        $hook->setValidator($this->getProvider(), $validationResponse);
    }

    /**
     * @param ProcessHook $hook
     */
    public function processEdit(ProcessHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearCollections();

        foreach ($this->entities as $collectionEntity) {
            // @todo Make these parameters non-static.
            $hookedObject->addCollection($collectionEntity, null, false, true);
        }

        $repository->saveOrDelete($hookedObject);
    }
}
