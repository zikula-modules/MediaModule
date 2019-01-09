<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\HookProvider;

use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\ServiceIdTrait;

/**
 * Provides convenience methods for hook handling.
 */
abstract class AbstractUiHooksProvider implements HookProviderInterface
{
    use ServiceIdTrait;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

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
     * @param EntityManagerInterface $entityManager
     * @param RequestStack           $requestStack
     * @param EngineInterface        $renderEngine
     * @param SecurityManager        $securityManager
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        EngineInterface $renderEngine,
        SecurityManager $securityManager
    ) {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->renderEngine = $renderEngine;
        $this->securityManager = $securityManager;
    }

    public function getOwner()
    {
        return 'CmfcmfMediaModule';
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
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
}
