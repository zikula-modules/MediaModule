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

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\OwnerPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @todo Once Zikula supports the Symfony user mechanism, retrieve the user
 * from a service instead of using the static method call.
 */
class OwnerCollectionPermission extends AbstractCollectionPermission
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Owner', [], 'cmfcmfmediamodule');
    }

    /**
     * @param OwnerPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity)
    {
        return $this->translator->trans('Owner', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
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
