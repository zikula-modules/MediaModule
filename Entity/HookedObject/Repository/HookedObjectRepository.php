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

namespace Cmfcmf\Module\MediaModule\Entity\HookedObject\Repository;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\HookBundle\Hook\Hook;

class HookedObjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HookedObjectEntity::class);
    }

    /**
     * Returns the HookedObject related to the given Hook. If none exists, a new one is created.
     *
     * @param Hook $hook
     *
     * @return HookedObjectEntity
     */
    public function getByHookOrCreate(Hook $hook)
    {
        /** @var HookedObjectEntity $entity */
        $entity = $this->findOneBy([
            'module' => $hook->getCaller(),
            'areaId' => $hook->getAreaId(),
            'objectId' => $hook->getId()
        ]);

        return $entity ? $entity : new HookedObjectEntity($hook);
    }

    /**
     * Saves the given HookedObject if something is hooked to it.
     * Deletes it otherwise.
     *
     * @param HookedObjectEntity $hookedObjectEntity
     */
    public function saveOrDelete(HookedObjectEntity $hookedObjectEntity)
    {
        $entityManager = $this->getEntityManager();
        if ($hookedObjectEntity->getId()) {
            if ($this->isSomethingHooked($hookedObjectEntity)) {
                $entityManager->merge($hookedObjectEntity);
            } else {
                $entityManager->remove($hookedObjectEntity);
            }
        } else {
            if (!$this->isSomethingHooked($hookedObjectEntity)) {
                return;
            }
            $entityManager->persist($hookedObjectEntity);
        }
        $entityManager->flush();
    }

    /**
     * Deletes all HookedObjects related to the given module name.
     *
     * @param string $name the module name
     */
    public function deleteAllOfModule($name)
    {
        $qb = $this->createQueryBuilder('h');
        $qb
            ->delete('CmfcmfMediaModule:HookedObject\HookedObjectEntity', 'h')
            ->where($qb->expr()->eq('h.module', $qb->expr()->literal($name)))
            ->getQuery()
            ->execute();
    }

    /**
     * Checks if something is hooked to the HookedObject.
     *
     * @param HookedObjectEntity $hookedObjectEntity
     *
     * @return bool
     */
    private function isSomethingHooked(HookedObjectEntity $hookedObjectEntity)
    {
        return
            !$hookedObjectEntity->getLicenses()->isEmpty() ||
            !$hookedObjectEntity->getHookedObjectMedia()->isEmpty() ||
            !$hookedObjectEntity->getHookedObjectCollections()->isEmpty();
    }
}
