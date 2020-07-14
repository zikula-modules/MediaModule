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
use Cmfcmf\Module\MediaModule\Form\Type\EditMediaType;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse;

/**
 * Media hook provider.
 */
class MediaFormAwareHookProvider extends AbstractFormAwareHookProvider
{
    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    public function getTitle(): string
    {
        return $this->translator->trans('Media FormAware hooks provider');
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
        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $mediaTypeCollection = $this->mediaTypeCollection;
        $selectedMedia = array_map(function (HookedObjectMediaEntity $hookedObjectMediaEntity) use ($mediaTypeCollection) {
            return $hookedObjectMediaEntity->getMedia()->toArrayForFinder($mediaTypeCollection);
        }, $hookedObject->getHookedObjectMedia()->getValues());

        $hook
            /*->formAdd('cmfcmfmediamodule_hook_editmedia', EditMediaType::class, [
                'auto_initialize' => false,
                'mapped' => false
            ])*/
            ->addTemplate('@CmfcmfMediaModule/Media/hookEdit.html.twig', [
                'selectedMedia' => $selectedMedia,
            ])
        ;
    }

    /**
     * Process the inner editing form.
     */
    public function processEdit(FormAwareResponse $hook): void
    {
        //$innerForm = $hook->getFormData('cmfcmfmediamodule_hook_editmedia');
        //$mediaIds = $innerForm['media'] ?? [];

        // TODO migrate this to a proper inner form
        $mediaIds = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[media]', [], true);
        $mediaIds = $_POST['cmfcmfmediamodule']['media'] ?? [];

        /** @var AbstractMediaEntity[] $entities */
        $entities = [];
        foreach ($mediaIds as $mediaId) {
            if (empty($mediaId)) {
                continue;
            }
            $mediaEntity = $this->entityManager->find(AbstractMediaEntity::class, $mediaId);
            if (!is_object($mediaEntity)) {
                //addError('media', $this->translator->trans('Unknown medium', [], 'cmfcmfmediamodule'));
            } elseif (!$this->securityManager->hasPermission($mediaEntity, CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS)) {
                //addError('media', $this->translator->trans('Unknown medium', [], 'cmfcmfmediamodule'));
            } else {
                $entities[] = $mediaEntity;
            }
        }

        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearMedia();
        foreach ($entities as $mediaEntity) {
            $hookedObject->addMedium($mediaEntity);
        }

        $repository->saveOrDelete($hookedObject);
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.form_aware_hooks.media';
    }

    /**
     * @required
     */
    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }
}
