<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media\Repository;

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
}
