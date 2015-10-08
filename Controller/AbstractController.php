<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Symfony\Component\Form\FormError;
use Zikula\Core\Controller\AbstractController as BaseAbstractController;
use Zikula\Core\UrlInterface;

abstract class AbstractController extends BaseAbstractController
{
    protected function notifyHooks(\Zikula\Component\HookDispatcher\Hook $hook)
    {
        return $this->get('hook_dispatcher')->dispatch($hook->getName(), $hook);
    }

    protected function getDisplayHookContent($name, $event, $id = null, UrlInterface $url = null)
    {
        $eventName = "cmfcmfmediamodule.ui_hooks.$name.$event";
        $hook = new \Zikula_DisplayHook($eventName, $id, $url);
        $this->get('hook_dispatcher')->dispatch($eventName, $hook);
        $responses = $hook->getResponses();

        $content = "";

        foreach ($responses as $result) {
            $result = $result->__toString();
            if (strlen(trim($result)) > 0) {
                $content .= "<div class=\"col-xs-12\">$result</div>\n";
            }
        }

        return strlen($content) == 0 ? "" : "<div class=\"row\">\n$content</div>";
    }

    protected function applyProcessHook($name, $event, $id, UrlInterface $url = null)
    {
        /* @noinspection PhpParamsInspection */
        $this->notifyHooks(new \Zikula_ProcessHook(
            "cmfcmfmediamodule.ui_hooks.$name.$event",
            $id,
            $url
        ));
    }

    protected function hookValidates($name, $event)
    {
        /* @noinspection PhpParamsInspection */
        $validationHook = new \Zikula_ValidationHook(
            "cmfcmfmediamodule.ui_hooks.$name.$event",
            new \Zikula_Hook_ValidationProviders()
        );
        /** @var \Zikula\Core\Hook\ValidationProviders $hookvalidators */
        $hookvalidators = $this->notifyHooks($validationHook)->getValidators();

        return !$hookvalidators->hasErrors();
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     */
    protected function hookValidationError($form)
    {
        $form->addError(new FormError($this->__('Hook validation failed!')));
    }
}
