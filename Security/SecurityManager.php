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

    public function getCollectionSecurityLevels()
    {
        $levels = [];

        $levels[0] = new SecurityLevel(
            0,
            $this->translator->trans('No access', [], $this->domain),
            $this->translator->trans('Doesn\', [], $this->domaint grant any kind of access.'),
            $this->translator->trans('No access', [], $this->domain),
            [],
            []
        );

        $levels[1] = new SecurityLevel(
            1,
            $this->translator->trans('Subcollection and media overview', [], $this->domain),
            $this->translator->trans('Grants access to view the collection\', [], $this->domains media and subcollections.'),
            $this->translator->trans('View', [], $this->domain),
            [],
            [$levels[0]]
        );

        $levels[2] = new SecurityLevel(
            2,
            $this->translator->trans('Download all media', [], $this->domain),
            $this->translator->trans('Grants access to download the whole collection at once.', [], $this->domain),
            $this->translator->trans('View', [], $this->domain),
            [$levels[1]],
            [$levels[0]]
        );

        $levels[3] = new SecurityLevel(
            3,
            $this->translator->trans('Display media details', [], $this->domain),
            $this->translator->trans('Grants access to the media details page.', [], $this->domain),
            $this->translator->trans('View', [], $this->domain),
            [$levels[1]],
            [$levels[0]]
        );

        $levels[4] = new SecurityLevel(
            4,
            $this->translator->trans('Download single media', [], $this->domain),
            $this->translator->trans('Grants access to download a single medium.', [], $this->domain),
            $this->translator->trans('View', [], $this->domain),
            [$levels[2], $levels[3]],
            [$levels[0]]
        );

        return $levels;
    }
}
