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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;

/**
 * Listen to module removals.
 */
class ModuleListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
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
            CoreEvents::MODULE_REMOVE => 'moduleRemoved'
        ];
    }

    /**
     * Called if a module is removed.
     *
     * @param ModuleStateEvent $event
     */
    public function moduleRemoved(ModuleStateEvent $event)
    {
        if ($event->getModule()) {
            $name = $event->getModule()->getName();
        } else {
            $name = $event->modinfo['name'];
            if (empty($name)) {
                // Just to make sure..
                return;
            }
        }

        $this->em->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity')
            ->deleteAllOfModule($name);
    }
}
