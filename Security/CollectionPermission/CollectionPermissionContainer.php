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
        $this->permissions[] = $permission;
    }

    /**
     * @return array|CollectionPermissionInterface[]
     */
    public function getCollectionPermissions()
    {
        return $this->permissions;
    }
}
