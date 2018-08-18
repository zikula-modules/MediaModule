<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\HookHandler;

use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;

/**
 * Provides convenience methods for hook handling.
 */
abstract class AbstractHookHandler
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param RequestStack           $requestStack
     * @param EngineInterface        $renderEngine
     * @param SecurityManager        $securityManager
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        EngineInterface $renderEngine,
        SecurityManager $securityManager,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->renderEngine = $renderEngine;
        $this->securityManager = $securityManager;
        $this->translator = $translator;
    }

    /**
     * Generates a Hook response using the given content.
     *
     * @param DisplayHook $hook
     * @param string      $content
     */
    public function uiResponse(DisplayHook $hook, $content)
    {
        $hook->setResponse(new DisplayHookResponse($this->getProvider(), $content));
    }

    /**
     * Processes the hook deletion by removing the HookedObject.
     *
     * @param ProcessHook $hook
     */
    public function processDelete(ProcessHook $hook)
    {
        $repository = $this->entityManager
            ->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $this->entityManager->remove($hookedObject);
        $this->entityManager->flush();
    }

    /**
     * @return string
     */
    public function getType()
    {
        $class = get_class($this);

        return lcfirst(substr($class, strrpos($class, '\\') + 1, -strlen('HookHandler')));
    }

    /**
     * @return string
     */
    protected function getProvider()
    {
        return 'provider.cmfcmfmediamodule.ui_hooks.' . $this->getType();
    }
}
