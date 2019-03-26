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

use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Media form aware hook subscriber.
 */
class MediaFormAwareHookSubscriber implements HookSubscriberInterface
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
        return FormAwareCategory::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->__('Media form aware subscriber');
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            // Display hook for create/edit forms.
            FormAwareCategory::TYPE_EDIT => 'cmfcmfmediamodule.form_aware_hook.media.edit',
            // Process the results of the edit form after the main form is processed.
            FormAwareCategory::TYPE_PROCESS_EDIT => 'cmfcmfmediamodule.form_aware_hook.media.process_edit'
        ];
    }
}
