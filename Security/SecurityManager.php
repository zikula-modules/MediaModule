<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Symfony\Component\Translation\TranslatorInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class SecurityManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var SecurityTree
     */
    private $collectionSecurityTree;

    public function __construct(TranslatorInterface $translator, PermissionApi $permissionApi)
    {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
    }
    
    public function hasPermission($objectOrType, $action)
    {
        if (is_object($objectOrType)) {
            /** @var mixed $objectOrType */
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


        return $this->permissionApi->hasPermission("CmfcmfMediaModule:$type:", "$id::", $levels[$action]);
    }

    public function hasPermissionRaw($component, $instance, $level)
    {
        return $this->permissionApi->hasPermission($component, $instance, $level);
    }

    /**
     * @return SecurityLevel[]
     */
    public function getCollectionSecurityLevels()
    {
        return $this->getCollectionSecurityTree()->getLevels();
    }

    /**
     * @return SecurityTree
     */
    public function getCollectionSecurityTree()
    {
        if (!$this->collectionSecurityTree) {
            $this->collectionSecurityTree = new SecurityTree($this->translator, $this->domain);
        }

        return $this->collectionSecurityTree;
    }
}
