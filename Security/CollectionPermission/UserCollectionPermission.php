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
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->translator->trans('User', [], 'cmfcmfmediamodule');
    }

    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias)
    {
        if (php_sapi_name() === 'cli') {
            return null;
        } else {
            if (\UserUtil::isLoggedIn()) {
                $userId = (int)\UserUtil::getVar('uid');
            } else {
                $userId = PermissionApi::UNREGISTERED_USER;
            }
        }

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_up", Expr\Join::WITH, "$permissionAlias.id = {$permissionAlias}_up.id");

        return self::whereInSimpleArray($qb, "{$permissionAlias}_up", 'user', $userId, 'userIds');
    }

}
