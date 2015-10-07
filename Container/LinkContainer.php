<?php

namespace Cmfcmf\Module\MediaModule\Container;


use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
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

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(RouterInterface $router, SecurityManager $securityManager, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->securityManager = $securityManager;
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
        $this->translator = $translator;
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
                'text' => $this->translator->trans('Frontend', [], $this->domain),
                'icon' => 'home'
            ];
        }
        if ($this->securityManager->hasPermission('media', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_media_adminlist'),
                'text' => $this->translator->trans('Media list', [], $this->domain),
                'icon' => 'picture-o'
            ];
        }
        if ($this->securityManager->hasPermission('watermark', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_watermark_index'),
                'text' => $this->translator->trans('Watermarks', [], $this->domain),
                'icon' => 'map-marker'
            ];
        }
        if ($this->securityManager->hasPermission('license', 'moderate')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_license_index'),
                'text' => $this->translator->trans('Licenses', [], $this->domain),
                'icon' => 'copyright'
            ];
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_settings_settings'),
                'text' => $this->translator->trans('Settings', [], $this->domain),
                'icon' => 'cog'
            ];
        }
        if ($this->securityManager->hasPermission('settings', 'admin')) {
            $links[] = [
                'url' => $this->router->generate('cmfcmfmediamodule_settings_upgrade'),
                'text' => $this->translator->trans('Upgrade', [], $this->domain),
                'icon' => 'download'
            ];
        }

        return $links;
    }
}
