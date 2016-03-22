<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Container;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;
use Zikula\Bundle\HookBundle\Bundle\ProviderBundle;

class HookContainer extends AbstractHookContainer
{
    /**
     * {@inheritdoc}
     */
    protected function setupHookBundles()
    {
        $entities = [
            "collection" => $this->__("Collections"),
            "media" => $this->__("Media"),
            "license" => $this->__("Licenses"),
        ];
        foreach ($entities as $name => $title) {
            if ($name != 'license') {
                $this->createSubscriberUIHook($name, $title);
                $this->createSubscriberFilterHook($name, $title);
            }
            $this->createProviderUIHook($name, $title);
        }
    }

    /**
     * Creates a subscriber UI hook using the given name and title.
     *
     * @param string $name
     * @param string $title
     */
    private function createSubscriberUIHook($name, $title)
    {
        $bundle = new SubscriberBundle("CmfcmfMediaModule", "subscriber.cmfcmfmediamodule.ui_hooks.$name", "ui_hooks", $this->__f("%s hooks", ['%s' => $title]));

        $bundle->addEvent("display_view", "cmfcmfmediamodule.ui_hooks.$name.display_view");
        $bundle->addEvent("form_edit", "cmfcmfmediamodule.ui_hooks.$name.form_edit");
        $bundle->addEvent("form_delete", "cmfcmfmediamodule.ui_hooks.$name.form_delete");
        //$bundle->addEvent("filter", "cmfcmfmediamodule.filter_hooks.$name.filter");
        $bundle->addEvent("validate_edit", "cmfcmfmediamodule.ui_hooks.$name.validate_edit");
        $bundle->addEvent("validate_delete", "cmfcmfmediamodule.ui_hooks.$name.validate_delete");
        $bundle->addEvent("process_edit", "cmfcmfmediamodule.ui_hooks.$name.process_edit");
        $bundle->addEvent("process_delete", "cmfcmfmediamodule.ui_hooks.$name.process_delete");

        $this->registerHookSubscriberBundle($bundle);
    }

    /**
     * Creates a subscriber filter hook using the given name and title.
     *
     * @param string $name
     * @param string $title
     */
    private function createSubscriberFilterHook($name, $title)
    {
        $bundle = new SubscriberBundle("CmfcmfMediaModule", "subscriber.cmfcmfmediamodule.filter_hooks.$name", "filter_hooks", $this->__f("%s display hooks", ['%s' => $title]));
        $bundle->addEvent('filter', "cmfcmfmediamodule.filter_hooks.$name.filter");

        $this->registerHookSubscriberBundle($bundle);
    }

    /**
     * Creates a provider UI hook using the given name and title.
     *
     * @param string $name
     * @param string $title
     */
    private function createProviderUIHook($name, $title)
    {
        $bundle = new ProviderBundle("CmfcmfMediaModule", "provider.cmfcmfmediamodule.ui_hooks.$name", "ui_hooks", $this->__f("Media Module - %s", ['%s' => $title]));

        $class = "Cmfcmf\\Module\\MediaModule\\HookHandler\\" . ucfirst($name) . "HookHandler";
        $service = "cmfcmf_media_module.hook_handler.$name";

        $bundle->addServiceHandler("display_view", $class, "uiView", $service);
        $bundle->addServiceHandler("form_edit", $class, "uiEdit", $service);
        $bundle->addServiceHandler("validate_edit", $class, "validateEdit", $service);
        $bundle->addServiceHandler("process_edit", $class, "processEdit", $service);
        $bundle->addServiceHandler("process_delete", $class, "processDelete", $service);

        $this->registerHookProviderBundle($bundle);
    }
}
