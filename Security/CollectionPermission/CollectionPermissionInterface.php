<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

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
     * @return Expr
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb);
}
