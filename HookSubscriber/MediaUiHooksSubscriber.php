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

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Media ui hooks subscriber.
 */
class MediaUiHooksSubscriber implements HookSubscriberInterface
{
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
    public function getOwner()
    {
        return 'CmfcmfMediaModule';
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->__('Media ui hooks subscriber');
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            // Display hook for view/display templates.
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'cmfcmfmediamodule.ui_hooks.media.display_view',
            // Display hook for create/edit forms.
            UiHooksCategory::TYPE_FORM_EDIT => 'cmfcmfmediamodule.ui_hooks.media.form_edit',
            // Validate input from an item to be edited.
            UiHooksCategory::TYPE_VALIDATE_EDIT => 'cmfcmfmediamodule.ui_hooks.media.validate_edit',
            // Perform the final update actions for an edited item.
            UiHooksCategory::TYPE_PROCESS_EDIT => 'cmfcmfmediamodule.ui_hooks.media.process_edit',
            // Validate input from an item to be deleted.
            UiHooksCategory::TYPE_VALIDATE_DELETE => 'cmfcmfmediamodule.ui_hooks.media.validate_delete',
            // Perform the final delete actions for a deleted item.
            UiHooksCategory::TYPE_PROCESS_DELETE => 'cmfcmfmediamodule.ui_hooks.media.process_delete'
        ];
    }
}
