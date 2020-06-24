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

namespace Cmfcmf\Module\MediaModule\HookSubscriber;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

/**
 * Collection ui hooks subscriber.
 */
class CollectionUiHooksSubscriber implements HookSubscriberInterface
{
    public function getAreaName(): string
    {
        return 'subscriber.cmfcmfmediamodule.ui_hooks.collections';
    }

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner(): string
    {
        return 'CmfcmfMediaModule';
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory(): string
    {
        return UiHooksCategory::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->translator->trans('Collection ui hooks subscriber');
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents(): array
    {
        return [
            // Display hook for view/display templates.
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'cmfcmfmediamodule.ui_hooks.collections.display_view',
            // Display hook for create/edit forms.
            UiHooksCategory::TYPE_FORM_EDIT => 'cmfcmfmediamodule.ui_hooks.collections.form_edit',
            // Validate input from an item to be edited.
            UiHooksCategory::TYPE_VALIDATE_EDIT => 'cmfcmfmediamodule.ui_hooks.collections.validate_edit',
            // Perform the final update actions for an edited item.
            UiHooksCategory::TYPE_PROCESS_EDIT => 'cmfcmfmediamodule.ui_hooks.collections.process_edit',
            // Validate input from an item to be deleted.
            UiHooksCategory::TYPE_VALIDATE_DELETE => 'cmfcmfmediamodule.ui_hooks.collections.validate_delete',
            // Perform the final delete actions for a deleted item.
            UiHooksCategory::TYPE_PROCESS_DELETE => 'cmfcmfmediamodule.ui_hooks.collections.process_delete'
        ];
    }
}
