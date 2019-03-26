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

/**
 * Holds all collection permissions.
 */
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
     * Adds a new collection permission to the list.
     *
     * @param CollectionPermissionInterface $permission
     */
    public function addCollectionPermission(CollectionPermissionInterface $permission)
    {
        $this->permissions[$permission->getId()] = $permission;
    }

    /**
     * Returns the specified collection permission.
     *
     * @param string $id
     *
     * @return CollectionPermissionInterface
     */
    public function getCollectionPermission($id)
    {
        if (!isset($this->permissions[$id])) {
            throw new \DomainException();
        }

        return $this->permissions[$id];
    }

    /**
     * Returns the appropriate collection permission for the given entity.
     *
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
     * Returns all collection permissions indexed by id.
     *
     * @return array|CollectionPermissionInterface[]
     */
    public function getCollectionPermissions()
    {
        return $this->permissions;
    }
}
