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
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;

/**
 * Collection ui hooks provider.
 */
class CollectionUiHooksProvider extends AbstractUiHooksProvider
{
    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    public function getTitle(): string
    {
        return $this->translator->trans('Collection UI hooks provider');
    }

    public function getProviderTypes(): array
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'view',
            UiHooksCategory::TYPE_PROCESS_DELETE => 'processDelete'
        ];
    }

    public function view(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $content = $this->twig->render('@CmfcmfMediaModule/Collection/hookView.html.twig', [
            'hookedObjectCollections' => $hookedObject->getHookedObjectCollections(),
            'mediaTypeCollection' => $this->mediaTypeCollection
        ]);

        $hook->setResponse(new DisplayHookResponse($this->getAreaName(), $content));
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.ui_hooks.collections';
    }

    /**
     * @required
     */
    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }
}
