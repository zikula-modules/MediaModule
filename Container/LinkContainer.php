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

namespace Cmfcmf\Module\MediaModule\Container;

use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;

/**
 * Provides a list of links for the admin interface.
 */
class LinkContainer implements LinkContainerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * LinkContainer constructor.
     *
     * @param RouterInterface        $router
     * @param SecurityManager        $securityManager
     * @param TranslatorInterface    $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        RouterInterface $router,
        SecurityManager $securityManager,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager
    ) {
        $this->router = $router;
        $this->securityManager = $securityManager;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return 'CmfcmfMediaModule';
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getLinks($type = self::TYPE_ADMIN)
    {
        if (!class_exists('\\Fhaculty\\Graph\\Graph')) {
            include_once __DIR__ . '/../bootstrap.php';
        }
        if (self::TYPE_ADMIN === $type) {
            return $this->adminLinks();
        }
        if (self::TYPE_USER === $type) {
            return $this->userLinks();
        }

        return [];
    }

    /**
     * @return array
     */
    private function adminLinks()
    {
        $links = [];

        $rootCollection = $this->entityManager
            ->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')
            ->getRootNode()
        ;

        if ($this->securityManager->hasPermission(
            $rootCollection,
            CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)
        ) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_collection_displayroot'),
                'text' => $this->translator->trans('Frontend', [], 'cmfcmfmediamodule'),
                'icon' => 'home'
            ];
        }
        if ($this->securityManager->hasPermission('media', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_media_adminlist'),
                'text' => $this->translator->trans('Media list', [], 'cmfcmfmediamodule'),
                'icon' => 'picture-o'
            ];
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_settings_index'),
                'text' => $this->translator->trans('Settings', [], 'cmfcmfmediamodule'),
                'icon' => 'cog'
            ];
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_import_select'),
                'text' => $this->translator->trans('Import', [], 'cmfcmfmediamodule'),
                'icon' => 'cloud-download'
            ];
        }

        return $links;
    }

    /**
     * @return array
     */
    private function userLinks()
    {
        $links = [];

        $rootCollection = $this->entityManager
            ->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')
            ->getRootNode()
        ;

        if ($this->securityManager->hasPermission(
            $rootCollection,
            CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)
        ) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_collection_displayroot'),
                'text' => $this->translator->trans('Collections', [], 'cmfcmfmediamodule'),
                'icon' => 'folder-o'
            ];
        }
        if ($this->securityManager->hasPermission('watermark', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_watermark_index'),
                'text' => $this->translator->trans('Watermarks', [], 'cmfcmfmediamodule'),
                'icon' => 'copyright'
            ];
        }
        if ($this->securityManager->hasPermission('license', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_license_index'),
                'text' => $this->translator->trans('Licenses', [], 'cmfcmfmediamodule'),
                'icon' => 'gavel'
            ];
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_settings_index'),
                'text' => $this->translator->trans('Backend', [], 'cmfcmfmediamodule'),
                'icon' => 'cog'
            ];
        }

        return $links;
    }
}
