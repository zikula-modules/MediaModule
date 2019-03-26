<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * Represents a collection permission.
 */
interface CollectionPermissionInterface
{
    /**
     * Get the collection permission id.
     *
     * @return string
     */
    public function getId();

    /**
     * Get the collection permission title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get a string representation of the targeted users of the given permission entity.
     *
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity);

    /**
     * Get the entity class related to this collection permission.
     *
     * @return string
     */
    public function getEntityClass();

    /**
     * Get the form class related to this collection permission.
     *
     * @return string
     */
    public function getFormClass();

    /**
     * Extends the given query builder to only return applicable permissions to the current user.
     *
     * @param QueryBuilder &$qb
     * @param              $permissionAlias
     *
     * @return Expr|null
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias);
}
