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
    public function getApplicablePermissionsExpression(QueryBuilder &$qb)
    {
        if (php_sapi_name() === 'cli' || !\UserUtil::isLoggedIn()) {
            return null;
        }

        $userId = (int)\UserUtil::getVar('uid');

        $qb->leftJoin($this->getEntityClass(), 'op', Expr\Join::WITH, 'p.id = op.id');
        $qb->setParameter('opUserId', $userId);

        return $qb->expr()->eq('c.createdUserId', ':opUserId');
    }

}
