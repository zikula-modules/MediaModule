<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Repository;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CollectionRepository extends NestedTreeRepository
{
    /**
     * {@inheritdoc}
     */
    public function getRootNodesQueryBuilder($sortByField = null, $direction = 'asc')
    {
        $qb = parent::getRootNodesQueryBuilder($sortByField, $direction);
        $qb
            ->andWhere($qb->expr()->not($qb->expr()->eq('node.id', ':hiddenCollection')))
            ->setParameter('hiddenCollection', CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID)
        ;

        return $qb;
    }

    protected function getAllVisibleQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->select('c')
            ->where($qb->expr()->not($qb->expr()->eq('c.id', CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID)))
            ;
    }

    public function findAllVisible()
    {
        return $this->getAllVisibleQueryBuilder()->select('c')->getQuery()->execute();
    }

    /**
     * @param int $parentId
     *
     * @return CollectionEntity[]
     */
    public function findVisibleByParentId($parentId)
    {
        $qb = $this->getAllVisibleQueryBuilder();

        if ($parentId === null) {
            $qb->andWhere($qb->expr()->isNull('c.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('c.parent', ':parentId'))
                ->setParameter('parentId', $parentId)
            ;
        }

        return $qb->getQuery()
            ->execute()
        ;
    }
}
