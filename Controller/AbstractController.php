<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\Hook;
use Zikula\Core\Controller\AbstractController as BaseAbstractController;
use Zikula\Core\UrlInterface;

/**
 * Provides some convenience functions for the MediaModule controllers regarding hooks.
 */
abstract class AbstractController extends BaseAbstractController
{
    /**
     * Notifies subscribers of the given hook.
     *
     * @param Hook $hook
     *
     * @return Hook
     */
    protected function notifyHooks(Hook $hook)
    {
        return $this->get('hook_dispatcher')->dispatch($hook->getName(), $hook);
    }

    /**
     * Get the display hook content for the given hook.
     *
     * @param string            $name
     * @param string            $event
     * @param string|null       $id
     * @param UrlInterface|null $url
     *
     * @return string
     */
    protected function getDisplayHookContent($name, $event, $id = null, UrlInterface $url = null)
    {
        $eventName = "cmfcmfmediamodule.ui_hooks.$name.$event";
        $hook = new \Zikula_DisplayHook($eventName, $id, $url);
        $this->get('hook_dispatcher')->dispatch($eventName, $hook);
        /** @var DisplayHookResponse[] $responses */
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

    /**
     * Applies process hooks.
     *
     * @param string            $name
     * @param string            $event
     * @param string            $id
     * @param UrlInterface|null $url
     */
    protected function applyProcessHook($name, $event, $id, UrlInterface $url = null)
    {
        /* @noinspection PhpParamsInspection */
        $this->notifyHooks(new \Zikula_ProcessHook(
            "cmfcmfmediamodule.ui_hooks.$name.$event",
            $id,
            $url
        ));
    }

    /**
     * Checks whether or not the hook validates.
     *
     * @param string $name
     * @param string $event
     *
     * @return bool
     */
    protected function hookValidates($name, $event)
    {
        /* @noinspection PhpParamsInspection */
        $validationHook = new \Zikula_ValidationHook(
            "cmfcmfmediamodule.ui_hooks.$name.$event",
            new \Zikula_Hook_ValidationProviders()
        );
        /** @var \Zikula\Bundle\HookBundle\Hook\ValidationProviders $hookvalidators */
        $hookvalidators = $this->notifyHooks($validationHook)->getValidators();

        return !$hookvalidators->hasErrors();
    }

    /**
     * Adds an error message to the form.
     *
     * @param Form $form
     */
    protected function hookValidationError($form)
    {
        $form->addError(new FormError($this->__('Hook validation failed!')));
    }
}
