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

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Repository;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Zikula\CategoriesModule\Entity\CategoryEntity;

class CollectionRepository extends NestedTreeRepository
{
    /**
     * Code from Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = CategoryEntity::class;

        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($entityClass);
        if (null === $manager) {
            throw new \LogicException(sprintf('Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.', $entityClass));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    /**
     * @return CollectionEntity
     */
    public function getRootNode()
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('c')
            ->where($qb->expr()->isNull('c.parent'))
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
