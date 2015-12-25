<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Repository;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CollectionRepository extends NestedTreeRepository
{
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
