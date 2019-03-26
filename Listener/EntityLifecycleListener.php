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

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event subscriber class for entity lifecycle events.
 */
class EntityLifecycleListener implements EventSubscriber, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * Returns list of events to subscribe.
     *
     * @return string[] List of events
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad
        ];
    }

    /**
     * @param LifecycleEventArgs $args Event arguments
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$this->isEntityManagedByThisBundle($entity)) {
            return;
        }

        // TODO refactor these dependencies out of the entities
        if ($entity instanceof AbstractMediaEntity || $entity instanceof AbstractWatermarkEntity) {
            $entity->setRequestStack($this->container->get('request_stack'));
            $entity->setDataDirectory($this->container->getParameter('datadir'));
        }
    }

    /**
     * Checks whether this listener is responsible for the given entity or not.
     *
     * @param EntityAccess $entity The given entity
     *
     * @return boolean True if entity is managed by this listener, false otherwise
     */
    protected function isEntityManagedByThisBundle($entity)
    {
        $entityClassParts = explode('\\', get_class($entity));

        return 'Cmfcmf' === $entityClassParts[0] && 'MediaModule' === $entityClassParts[2];
    }
}
