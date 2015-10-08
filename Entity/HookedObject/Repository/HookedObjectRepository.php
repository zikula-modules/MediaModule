<?php

namespace Cmfcmf\Module\MediaModule\Entity\HookedObject\Repository;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Doctrine\ORM\EntityRepository;
use Zikula\Component\HookDispatcher\Hook;

class HookedObjectRepository extends EntityRepository
{
    /**
     * @param Hook $hook
     *
     * @return HookedObjectEntity
     */
    public function getByHookOrCreate(Hook $hook)
    {
        $entity = $this->findOneBy([
            'module' => $hook->getCaller(),
            'areaId' => $hook->getAreaId(),
            'objectId' => $hook->getId()
        ]);

        if ($entity) {
            return $entity;
        }

        return new HookedObjectEntity($hook);
    }

    public function saveOrDelete(HookedObjectEntity $hookedObjectEntity)
    {
        if ($hookedObjectEntity->getId()) {
            if ($this->isSomethingHooked($hookedObjectEntity)) {
                $this->getEntityManager()->merge($hookedObjectEntity);
            } else {
                $this->getEntityManager()->remove($hookedObjectEntity);
            }
        } else {
            if (!$this->isSomethingHooked($hookedObjectEntity)) {
                return;
            }
            $this->getEntityManager()->persist($hookedObjectEntity);
        }
        $this->getEntityManager()->flush();
    }

    private function isSomethingHooked(HookedObjectEntity $hookedObjectEntity)
    {
        return !$hookedObjectEntity->getLicenses()->isEmpty() || !$hookedObjectEntity->getHookedObjectMedia()->isEmpty() || !$hookedObjectEntity->getHookedObjectCollections()->isEmpty();
    }

    public function deleteAllOfModule($name)
    {
        $qb = $this->createQueryBuilder('h');
        $qb
            ->delete('CmfcmfMediaModule:HookedObject\HookedObjectEntity', 'h')
            ->where($qb->expr()->eq('h.module', $qb->expr()->literal($name)))
        ;

        $qb->getQuery()->execute();
    }
}
