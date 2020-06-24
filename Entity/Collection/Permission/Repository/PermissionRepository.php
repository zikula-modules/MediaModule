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

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Repository;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Exception\InvalidPositionException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractPermissionEntity::class);
    }

    /**
     * Persists / merges the permission entity and flushes afterwards.
     *
     * It also makes sure that there isn't a goOn = 0 entity above a locked entity.
     *
     * @param AbstractPermissionEntity $permissionEntity
     * @param bool                     $fixPosition      Whether or not the position of the permission
     *                                                   shall be automatically fixed. If false, an exception will be thrown.
     */
    public function save(AbstractPermissionEntity $permissionEntity, $fixPosition)
    {
        if (!$permissionEntity->isGoOn() && !$permissionEntity->isLocked()) {
            // Make sure this doesn't override a locked permission.
            // If it does, raise it's position.
            $highestLockedPermission = $this->getLockedPermissionWithHighestPosition();
            if ($permissionEntity->getPosition() <= $highestLockedPermission->getPosition()) {
                if ($fixPosition) {
                    $permissionEntity->setPosition($highestLockedPermission->getPosition() + 1);
                } else {
                    throw new InvalidPositionException();
                }
            }
        }

        $em = $this->getEntityManager();
        if ($em->contains($permissionEntity)) {
            $em->merge($permissionEntity);
        } else {
            $em->persist($permissionEntity);
        }

        $em->flush($permissionEntity);
    }

    /**
     * @return null|AbstractPermissionEntity
     */
    private function getLockedPermissionWithHighestPosition()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->where($qb->expr()->eq('p.locked', 1))
            ->orderBy('p.position', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
