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

namespace Cmfcmf\Module\MediaModule\Entity\Media\Repository;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractMediaEntity::class);
    }

    /**
     * Creates a Doctrine Paginator with the given page and entities per page.
     *
     * @param int $page
     * @param int $perPage
     * @param string $q
     *
     * @return Paginator
     */
    public function getPaginated($page, $perPage, $q)
    {
        $qb = $this->createQueryBuilder('m');
        $query = $qb->select('m')
            ->orderBy('m.title')
        ;
        if (!empty($q)) {
            $qb->where($qb->expr()->like('m.title', ':q'))
                ->setParameter('q', "%${q}%")
            ;
        }
        $query = $qb->setFirstResult($page * $perPage)
            ->setMaxResults($perPage)
        ;

        return new Paginator($query);
    }

    /**
     * Find one entity by it's two slugs. Used in the ParamConverter annotations.
     *
     * @param string $collectionSlug
     * @param string $slug
     *
     * @return AbstractMediaEntity|null
     */
    public function findBySlugs($collectionSlug, $slug)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->select('m')
            ->leftJoin('m.collection', 'c')
            ->where($qb->expr()->eq('m.slug', ':slug'))
            ->andWhere($qb->expr()->eq('c.slug', ':collectionSlug'))
            ->setParameter('slug', $slug)
            ->setParameter('collectionSlug', $collectionSlug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
