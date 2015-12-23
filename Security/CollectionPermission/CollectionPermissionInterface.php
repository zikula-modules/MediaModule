<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

interface CollectionPermissionInterface
{
    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * @return string
     */
    public function getFormClass();

    /**
     * @param QueryBuilder &$qb
     * @return Expr|null
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb);

    /**
     * @param CollectionEntity $collection
     * @return mixed
     */
    public function onNoPermission(CollectionEntity $collection);

    /**
     * @param CollectionEntity $collection
     * @return mixed
     */
    public function acquirePermission(CollectionEntity $collection);
}
