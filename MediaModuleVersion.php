<?php

namespace Cmfcmf\Module\MediaModule;

class MediaModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta["displayname"]    = $this->__("Cmfcmf MediaModule");
        $meta["description"]    = $this->__("Cmfcmf MediaModule description");
        $meta["url"]            = $this->__("collections");
        $meta["version"]        = "1.0.0";
        $meta["core_min"]       = "1.4.0";
        $meta["securityschema"] = ["CmfcmfMediaModule::" => "::"];
        $meta['capabilities'] = [
            \HookUtil::SUBSCRIBER_CAPABLE => ['enabled' => true],
            \HookUtil::PROVIDER_CAPABLE => ['enabled' => true]
        ];

        return $meta;
    }

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

    // Subscriber hooks for media entities.
    // This allows other modules to hook to the media display pages,
    // for example the Tag module could be hooked.
    private function createSubscriberUIHook($name, $title)
    {
        $bundle = new \Zikula_HookManager_SubscriberBundle($this->name, "subscriber.cmfcmfmediamodule.ui_hooks.$name", "ui_hooks", $this->__f("%s hooks", [$title]));

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

    // Subscriber hooks for media entites.
    // This allows other modules to filter the media description.
    private function createSubscriberFilterHook($name, $title)
    {
        $bundle = new \Zikula_HookManager_SubscriberBundle($this->name, "subscriber.cmfcmfmediamodule.filter_hooks.$name", "filter_hooks", $this->__f("%s display hooks", [$title]));
        $bundle->addEvent('filter', "cmfcmfmediamodule.filter_hooks.$name.filter");

        $this->registerHookSubscriberBundle($bundle);
    }

    private function createProviderUIHook($name, $title)
    {
        $bundle = new \Zikula_HookManager_ProviderBundle($this->name, "provider.cmfcmfmediamodule.ui_hooks.$name", "ui_hooks", $this->__f("Media Module - %s", [$title]));

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
