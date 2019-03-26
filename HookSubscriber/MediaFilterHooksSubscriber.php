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

use Zikula\Bundle\HookBundle\Category\FilterHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Media filter hooks subscriber.
 */
class MediaFilterHooksSubscriber implements HookSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

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
        return FilterHooksCategory::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->__('Media filter hooks subscriber');
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            FilterHooksCategory::TYPE_FILTER => 'cmfcmfmediamodule.filter_hooks.media.filter'
        ];
    }
}
