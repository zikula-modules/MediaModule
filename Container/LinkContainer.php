<?php

namespace Cmfcmf\Module\MediaModule\Container;


use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;

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
     * @var string
     */
    private $domain;

    public function __construct(RouterInterface $router, SecurityManager $securityManager)
    {
        $this->router = $router;
        $this->securityManager = $securityManager;
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
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
     * @return array
     */
    public function getLinks($type = self::TYPE_ADMIN)
    {
        if ($type != self::TYPE_ADMIN) {
            return [];
        }

        $links = [];

        if ($this->securityManager->hasPermission('collection', 'view')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_collection_displayroot'),
                'text' => __('Frontend', $this->domain),
                'icon' => 'home'
            ];
        }
        if ($this->securityManager->hasPermission('media', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_media_adminlist'),
                'text' => __('Media list', $this->domain),
                'icon' => 'picture-o'
            ];
        }
        if ($this->securityManager->hasPermission('watermark', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_watermark_index'),
                'text' => __('Watermarks', $this->domain),
                'icon' => 'map-marker'
            ];
        }
        if ($this->securityManager->hasPermission('license', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_license_index'),
                'text' => __('Licenses', $this->domain),
                'icon' => 'copyright'
            ];
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_settings_settings'),
                'text' => __('Settings', $this->domain),
                'icon' => 'cog'
            ];
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_settings_upgrade'),
                'text' => __('Upgrade', $this->domain),
                'icon' => 'download'
            ];
        }

        return $links;
    }
}
