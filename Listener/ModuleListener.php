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

namespace Cmfcmf\Module\MediaModule\Listener;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;

/**
 * Listen to module removals.
 */
class ModuleListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ExtensionPostRemoveEvent::class => 'moduleRemoved'
        ];
    }

    /**
     * Called if a module is removed.
     */
    public function moduleRemoved(ExtensionStateEvent $event)
    {
        if ($event->getExtensionBundle()) {
            $name = $event->getExtensionBundle()->getName();
        } else {
            $name = $event->getExtensionEntity()->getName();
            if (empty($name)) {
                // Just to make sure..
                return;
            }
        }
        if ('CmfCmfMediaModule' === $name) {
            return; // when uninstalling this module, all tables are deleted, so this is not necessary
        }

        $this->em->getRepository(HookedObjectEntity::class)
            ->deleteAllOfModule($name);
    }
}
