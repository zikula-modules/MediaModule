<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Fhaculty\Graph\Graph;
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
    private $collectionSecurityGraph;

    public function __construct(TranslatorInterface $translator, PermissionApi $permissionApi)
    {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
        $this->domain = 'cmfcmfmediamodule';
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
     * @return Graph
     */
    public function getCollectionSecurityGraph()
    {
        if (!$this->collectionSecurityGraph) {
            $this->collectionSecurityGraph = SecurityTree::createGraph($this->translator, $this->domain);
        }

        return $this->collectionSecurityGraph;
    }

    /**
     * @return SecurityCategory[]
     */
    public function getCollectionSecurityCategories()
    {
        return SecurityTree::getCategories($this->translator, $this->domain);
    }
}
