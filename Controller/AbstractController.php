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

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController as BaseAbstractController;
use Zikula\Bundle\CoreBundle\UrlInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\Hook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * Provides some convenience functions for the MediaModule controllers regarding hooks.
 */
abstract class AbstractController extends BaseAbstractController
{
    /**
     * @var HookDispatcherInterface
     */
    protected $hookDispatcher;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var MediaTypeCollection
     */
    protected $mediaTypeCollection;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        HookDispatcherInterface $hookDispatcher,
        SecurityManager $securityManager,
        MediaTypeCollection $mediaTypeCollection
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->hookDispatcher = $hookDispatcher;
        $this->securityManager = $securityManager;
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    /**
     * Notifies subscribers of the given hook.
     *
     * @param string $name Hook event name
     * @param Hook   $hook Hook interface
     *
     * @return Hook
     */
    protected function dispatchHooks($name, Hook $hook)
    {
        return $this->hookDispatcher->dispatch($name, $hook);
    }

    /**
     * Get the display hook content for the given hook.
     *
     * @param string            $name
     * @param string            $hookType
     * @param string|null       $id
     *
     * @return string
     */
    protected function getDisplayHookContent($name, $hookType, $id = null, UrlInterface $url = null)
    {
        $eventName = 'cmfcmfmediamodule.ui_hooks.' . $name . '.' . $hookType;
        $hook = new DisplayHook($id, $url);
        $this->hookDispatcher->dispatch($eventName, $hook);
        /** @var DisplayHookResponse[] $responses */
        $responses = $hook->getResponses();

        $content = '';
        foreach ($responses as $result) {
            $result = $result->__toString();
            if (mb_strlen(trim($result)) > 0) {
                $content .= "<div class=\"col-xs-12\">${result}</div>\n";
            }
        }

        return 0 === mb_strlen($content) ? '' : "<div class=\"row\">\n${content}</div>";
    }

    /**
     * Applies process hooks.
     *
     * @param string            $name
     * @param string            $hookType
     * @param string            $id
     */
    protected function applyProcessHook($name, $hookType, $id, UrlInterface $url = null)
    {
        $eventName = 'cmfcmfmediamodule.ui_hooks.' . $name . '.' . $hookType;
        $hook = new ProcessHook($id, $url);
        $this->dispatchHooks($eventName, $hook);
    }

    /**
     * Applies form aware display hooks.
     *
     * @param string            $name
     * @param string            $hookType
     */
    protected function applyFormAwareDisplayHook(Form $form, $name, $hookType, UrlInterface $url = null)
    {
        $eventName = 'cmfcmfmediamodule.form_aware_hook.' . $name . '.' . $hookType;
        $hook = new FormAwareHook($form);
        $this->dispatchHooks($eventName, $hook);

        return $hook;
    }

    /**
     * Applies form aware process hooks.
     *
     * @param string              $name
     * @param string              $hookType
     * @param object|array|string $formSubject
     */
    protected function applyFormAwareProcessHook(Form $form, $name, $hookType, $formSubject, UrlInterface $url = null)
    {
        $formResponse = new FormAwareResponse($form, $formSubject, $url);
        $eventName = 'cmfcmfmediamodule.form_aware_hook.' . $name . '.' . $hookType;

        $this->dispatchHooks($eventName, $formResponse);
    }

    /**
     * Checks whether or not the hook validates.
     *
     * @param string $name
     * @param string $hookType
     *
     * @return bool
     */
    protected function hookValidates($name, $hookType)
    {
        $eventName = 'cmfcmfmediamodule.ui_hooks.' . $name . '.' . $hookType;
        $validationHook = new ValidationHook();
        /** @var ValidationProviders $hookvalidators */
        $hookvalidators = $this->dispatchHooks($eventName, $validationHook)->getValidators();

        return !$hookvalidators->hasErrors();
    }

    /**
     * Adds an error message to the form.
     */
    protected function hookValidationError(Form $form)
    {
        $form->addError(new FormError($this->trans('Hook validation failed!')));
    }
}
