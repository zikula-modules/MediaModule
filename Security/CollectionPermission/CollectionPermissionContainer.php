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

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;

class CollectionPermissionContainer
{
    /**
     * @var CollectionPermissionInterface[]
     */
    private $permissions;

    public function __construct()
    {
        $this->permissions = [];
    }

    /**
     * @param CollectionPermissionInterface $permission
     */
    public function addCollectionPermission(CollectionPermissionInterface $permission)
    {
        $this->permissions[$permission->getId()] = $permission;
    }

    /**
     * @param $id
     *
     * @return CollectionPermissionInterface
     */
    public function getCollectionPermission($id)
    {
        if (!isset($this->permissions[$id])) {
            throw new \InvalidArgumentException();
        }

        return $this->permissions[$id];
    }

    /**
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return CollectionPermissionInterface
     */
    public function getCollectionPermissionFromEntity(AbstractPermissionEntity $permissionEntity)
    {
        foreach ($this->permissions as $permission) {
            if (is_a($permissionEntity, $permission->getEntityClass(), false)) {
                return $permission;
            }
        }
        throw new \DomainException();
    }

    /**
     * @return array|CollectionPermissionInterface[]
     */
    public function getCollectionPermissions()
    {
        return $this->permissions;
    }
}
