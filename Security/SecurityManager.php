<?php

namespace Cmfcmf\Module\MediaModule\Security;

class SecurityManager
{
    public function hasPermission($objectOrType, $action)
    {
        if (is_object($objectOrType)) {
            $id = $objectOrType->getId();
            $class = get_class($objectOrType);
            $type = lcfirst(substr($class, strrpos($class, '/') + 1, -strlen('Entity')));
        } else {
            $id = "";
            $type = $objectOrType;
        }

        $levels = [
            'view'      => ACCESS_OVERVIEW,
            'display'   => ACCESS_READ,
            'download'  => ACCESS_COMMENT,
            'moderate'  => ACCESS_MODERATE,
            'add'       => ACCESS_ADD,
            'new'       => ACCESS_ADD,
            'create'    => ACCESS_ADD,
            'edit'      => ACCESS_EDIT,
            'delete'    => ACCESS_DELETE,
            'admin'     => ACCESS_ADMIN
        ];

        return \SecurityUtil::checkPermission("CmfcmfMediaModule:$type:", "$id::", $levels[$action]);
    }

    public function hasPermissionRaw($component, $instance, $level)
    {
        return \SecurityUtil::checkPermission($component, $instance, $level);
    }

    public function getCollectionSecurityLevels()
    {
        $levels = [];
    }
}
