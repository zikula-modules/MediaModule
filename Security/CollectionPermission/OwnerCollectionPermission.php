<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @todo Once Zikula supports the Symfony user mechanism, retrieve the user
 * from a service instead of using the static method call.
 */
class OwnerCollectionPermission extends AbstractCollectionPermission
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->translator->trans('Owner', [], 'cmfcmfmediamodule');
    }

    /**
     * @param QueryBuilder $qb
     * @param              $permissionAlias
     * @return Expr\Comparison|null
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias)
    {
        if (php_sapi_name() === 'cli' || !\UserUtil::isLoggedIn()) {
            return null;
        }

        $userId = (int)\UserUtil::getVar('uid');

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_op", Expr\Join::WITH, "$permissionAlias.id = {$permissionAlias}_op.id");
        $qb->setParameter('opUserId', $userId);

        return $qb->expr()->eq('c.createdUserId', ':opUserId');
    }
}
