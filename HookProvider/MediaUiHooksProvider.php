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

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationResponse;

/**
 * Media ui hooks provider.
 */
class MediaUiHooksProvider extends AbstractUiHooksProvider
{
    /**
     * @var AbstractMediaEntity[]
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
        return $this->translator->trans('Media ui hooks provider');
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

        $content = $this->twig->render('@CmfcmfMediaModule/Media/hookView.html.twig', [
            'hookedObjectMedia' => $hookedObject->getHookedObjectMedia(),
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

        $mediaTypeCollection = $this->mediaTypeCollection;
        $selectedMedia = array_map(function (HookedObjectMediaEntity $hookedObjectMediaEntity) use ($mediaTypeCollection) {
            return $hookedObjectMediaEntity->getMedia()->toArrayForFinder($mediaTypeCollection);
        }, $hookedObject->getHookedObjectMedia()->getValues());

        $content = $this->twig->render('@CmfcmfMediaModule/Media/hookEdit.html.twig', [
            'selectedMedia' => $selectedMedia,
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
        $mediaIds = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[media]', [], true);
        $mediaIds = $_POST['cmfcmfmediamodule']['media'] ?? [];

        $this->entities = [];
        $validationResponse = new ValidationResponse('media', $mediaIds);
        foreach ($mediaIds as $mediaId) {
            if (!empty($mediaId)) {
                $mediaEntity = $this->entityManager->find('CmfcmfMediaModule:Media\AbstractMediaEntity', $mediaId);
                if (!is_object($mediaEntity)) {
                    $validationResponse->addError('media', $this->translator->trans('Unknown medium', [], 'cmfcmfmediamodule'));
                } elseif (!$this->securityManager->hasPermission($mediaEntity, CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS)) {
                    $validationResponse->addError('media', $this->translator->trans('Unknown medium', [], 'cmfcmfmediamodule'));
                } else {
                    $this->entities[] = $mediaEntity;
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

        $hookedObject->clearMedia();
        foreach ($this->entities as $mediaEntity) {
            $hookedObject->addMedium($mediaEntity);
        }

        $repository->saveOrDelete($hookedObject);
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.ui_hooks.media';
    }
}
