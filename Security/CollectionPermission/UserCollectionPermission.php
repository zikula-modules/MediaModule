<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * @todo Once Zikula supports the Symfony user mechanism, retrieve the user
 * from a service instead of using the static method call.
 */
class UserCollectionPermission extends AbstractCollectionPermission
{
    public function getApplicablePermissionsExpression(QueryBuilder &$qb)
    {
        if (php_sapi_name() === 'cli') {
            $userId = null;
        } else {
            if (\UserUtil::isLoggedIn()) {
                $userId = (int)\UserUtil::getVar('uid');
            } else {
                $userId = PermissionApi::UNREGISTERED_USER;
            }
        }

        if ($userId === null) {
            return $qb->expr()->eq(0, 1);
        }

        $qb->leftJoin($this->getEntityClass(), 'up', Expr\Join::WITH, 'p.id = up.id');

        return $this->whereInSimpleArray($qb, 'up', 'user', $userId, 'userIds');
    }

}
