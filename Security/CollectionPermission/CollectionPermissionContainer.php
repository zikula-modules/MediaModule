<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

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
     * @return array|CollectionPermissionInterface[]
     */
    public function getCollectionPermissions()
    {
        return $this->permissions;
    }
}
