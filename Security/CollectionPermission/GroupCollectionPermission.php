<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @todo Once Zikula supports the Symfony group mechanism, retrieve the group
 * from a service instead of using the static method call.
 */
class GroupCollectionPermission extends AbstractCollectionPermission
{
    public function getApplicablePermissionsExpression(QueryBuilder &$qb)
    {
        if (php_sapi_name() === 'cli') {
            $groupIds = null;
        } else {
            $groupIds = explode(',', \UserUtil::getGroupListForUser());
        }

        if ($groupIds === null) {
            return $qb->expr()->eq(0, 1);
        }

        $qb->leftJoin($this->getEntityClass(), 'gp', Expr\Join::WITH, 'p.id = gp.id');

        $or = $qb->expr()->orX();
        foreach ($groupIds as $c => $groupId) {
            $or->add($this->whereInSimpleArray($qb, 'gp', "group$c", $groupId, 'groupIds'));
        }

        return $or;
    }
}
