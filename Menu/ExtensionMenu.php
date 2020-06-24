<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Menu;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\AbstractExtensionMenu;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu extends AbstractExtensionMenu
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var CollectionEntity
     */
    private $rootCollection;

    /**
     * @var CollectionRepository
     */
    private $collectionRepository;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        SecurityManager $securityManager,
        CollectionRepository $collectionRepository
    ) {
        parent::__construct($factory, $permissionApi);
        $this->securityManager = $securityManager;
        $this->collectionRepository = $collectionRepository;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (!class_exists('\\Fhaculty\\Graph\\Graph')) {
            include_once __DIR__ . '/../bootstrap.php';
        }
        $this->rootCollection = $this->collectionRepository->getRootNode();

        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        } elseif (self::TYPE_USER === $type) {
            return $this->getUser();
        }

        return null;
    }

    protected function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('mediaModuleAdminMenu');
        if ($this->securityManager->hasPermission(
            $this->rootCollection,
            CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW
        )
        ) {
            $menu->addChild('Frontend', [
                'route' => 'cmfcmfmediamodule_collection_displayroot',
            ])->setAttribute('icon', 'fas fa-home');
        }
        if ($this->securityManager->hasPermission('media', 'moderate')) {
            $menu->addChild('Media list', [
                'route' => 'cmfcmfmediamodule_media_adminlist',
            ])->setAttribute('icon', 'fas fa-image');
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $menu->addChild('Settings', [
                'route' => 'cmfcmfmediamodule_settings_index',
            ])->setAttribute('icon', 'fas fa-cog');
            $menu->addChild('Import', [
                'route' => 'cmfcmfmediamodule_import_select',
            ])->setAttribute('icon', 'fas fa-cloud-download-alt');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    protected function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('mediaModuleUserMenu');
        if ($this->securityManager->hasPermission(
            $this->rootCollection,
            CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW
        )
        ) {
            $menu->addChild('Collections', [
                'route' => 'cmfcmfmediamodule_collection_displayroot',
            ])->setAttribute('icon', 'fas fa-folder-o');
        }
        if ($this->securityManager->hasPermission('watermark', 'moderate')) {
            $menu->addChild('Watermarks', [
                'route' => 'cmfcmfmediamodule_watermark_index',
            ])->setAttribute('icon', 'fas fa-copyright');
        }
        if ($this->securityManager->hasPermission('license', 'moderate')) {
            $menu->addChild('Licenses', [
                'route' => 'cmfcmfmediamodule_license_index',
            ])->setAttribute('icon', 'fas fa-gavel');
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $menu->addChild('Backend', [
                'route' => 'cmfcmfmediamodule_settings_index',
            ])->setAttribute('icon', 'fas fa-cog');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'CmfcmfMediaModule';
    }
}
