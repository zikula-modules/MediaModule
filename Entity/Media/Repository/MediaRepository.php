<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media\Repository;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MediaRepository extends EntityRepository
{
    public function getPaginated($page, $perPage)
    {
        $qb = $this->createQueryBuilder('m');
        $query = $qb->select('m')
            ->setFirstResult($page * $perPage)
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
