<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\GroupPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @todo Once Zikula supports the Symfony group mechanism, retrieve the group
 * from a service instead of using the static method call.
 */
class GroupCollectionPermission extends AbstractCollectionPermission
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->translator->trans('Group', [], 'cmfcmfmediamodule');
    }

    /**
     * @param GroupPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity)
    {
        $targets = [];
        foreach ($permissionEntity->getGroupIds() as $groupId) {
            if ($groupId == -1) {
                $targets[] = $this->translator->trans('All groups', [], 'cmfcmfmediamodule');
            } else {
                $targets[] = \UserUtil::getGroup($groupId)['name'];
            }
        }

        return implode(', ', $targets);
    }

    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias)
    {
        if (php_sapi_name() === 'cli') {
            return null;
        } else {
            $groupIds = explode(',', \UserUtil::getGroupListForUser());
        }
        $groupIds[] = -1;

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_gp", Expr\Join::WITH, "$permissionAlias.id = {$permissionAlias}_gp.id");

        $or = $qb->expr()->orX();
        foreach ($groupIds as $c => $groupId) {
            $or->add(self::whereInSimpleArray($qb, "{$permissionAlias}_gp", "group$c", $groupId, 'groupIds'));
        }

        return $or;
    }
}
