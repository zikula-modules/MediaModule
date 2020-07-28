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
use Zikula\Bundle\HookBundle\Category\FilterHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

/**
 * Collection filter hooks subscriber.
 */
class CollectionFilterHooksSubscriber implements HookSubscriberInterface
{
    public function getAreaName(): string
    {
        return 'subscriber.cmfcmfmediamodule.filter_hooks.collections';
    }

    /**
     * @var TranslatorInterface
     */
    private $translator;

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
        return FilterHooksCategory::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->translator->trans('Collection filter hooks subscriber');
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents(): array
    {
        return [
            FilterHooksCategory::TYPE_FILTER => 'cmfcmfmediamodule.filter_hooks.collections.filter'
        ];
    }
}
