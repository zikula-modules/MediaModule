<?php

namespace Cmfcmf\Module\MediaModule\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;

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

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_REMOVE => 'moduleRemoved',
        ];
    }

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
            ->deleteAllOfModule($name)
        ;
    }
}
