<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\UserPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @todo Once Zikula supports the Symfony user mechanism, retrieve the user
 * from a service instead of using the static method call.
 */
class UserCollectionPermission extends AbstractCollectionPermission
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('User', [], 'cmfcmfmediamodule');
    }

    /**
     * @param UserPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity)
    {
        $targets = [];
        foreach ($permissionEntity->getUserIds() as $userId) {
            $targets[] = \UserUtil::getVar('uname', $userId);
        }

        return implode(', ', $targets);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias, $allPasswordsValid = false)
    {
        if (php_sapi_name() === 'cli') {
            return null;
        } else {
            if (\UserUtil::isLoggedIn()) {
                $userId = (int)\UserUtil::getVar('uid');
            } else {
                // Cannot use PermissionAPI constant due to
                // https://github.com/zikula/core/issues/2800
                $userId = 1; //PermissionApi::UNREGISTERED_USER;
            }
        }

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_up", Expr\Join::WITH, "$permissionAlias.id = {$permissionAlias}_up.id");

        return self::whereInSimpleArray($qb, "{$permissionAlias}_up", 'user', $userId, 'userIds');
    }
}
