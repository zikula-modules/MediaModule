<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractCollectionPermission implements CollectionPermissionInterface
{
    public function getFormClass()
    {
        return 'Cmfcmf\Module\MediaModule\Form\Permission\\' . $this->getType() . 'PermissionType';
    }

    protected function getType()
    {
        $class = get_class($this);

        return substr($class, strrpos($class, '\\') + 1, -strlen('CollectionPermission'));
    }

    public function getEntityClass()
    {
        return 'Cmfcmf\Module\MediaModule\Entity\Permission\\' . $this->getType() . 'PermissionEntity';
    }

    /**
     * @param QueryBuilder $qb
     * @param              $entity
     * @param              $type
     * @param              $value
     * @param              $field
     * @return \Doctrine\ORM\Query\Expr\Composite
     */
    protected function whereInSimpleArray(QueryBuilder &$qb, $entity, $type, $value, $field)
    {
        $qb->setParameter($type . '1', $value);
        $qb->setParameter($type . '2', "%," . $value);
        $qb->setParameter($type . '3', $value . ",%");
        $qb->setParameter($type . '4', "%," . $value . ",%");

        return $qb->expr()->orX(
            $qb->expr()->eq("$entity.$field", ':' . $type . '1'),
            $qb->expr()->like("$entity.$field", ':' . $type . '2'),
            $qb->expr()->like("$entity.$field", ':' . $type . '3'),
            $qb->expr()->like("$entity.$field", ':' . $type . '4')
        );
    }

    public function onNoPermission(CollectionEntity $collection)
    {
        return false;
    }

    public function acquirePermission(CollectionEntity $collection)
    {
        return false;
    }
}
