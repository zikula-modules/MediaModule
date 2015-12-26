<?php

namespace Cmfcmf\Module\MediaModule\HookHandler;

use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Common\I18n\TranslatableInterface;
use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\ProcessHook;

abstract class AbstractHookHandler implements TranslatableInterface
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
     * @var string
     */
    private $domain;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, EngineInterface $renderEngine, SecurityManager $securityManager)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->renderEngine = $renderEngine;
        $this->securityManager = $securityManager;
        $this->domain = 'cmfcmfmediamodule';
    }

    public function getType()
    {
        $class = get_class($this);

        return lcfirst(substr($class, strrpos($class, '\\') + 1, -strlen('HookHandler')));
    }

    public function uiResponse(DisplayHook $hook, $content)
    {
        // Arrrr, we are forced to use Smarty -.-
        // We need to clone the instance, because it causes errors otherwise
        // when multiple hooks areas are hooked.
        $view = clone \Zikula_View::getInstance('CmfcmfMediaModule');
        $view->setCaching(\Zikula_View::CACHE_DISABLED);
        $view->assign('content', $content);

        $hook->setResponse(
            new \Zikula_Response_DisplayHook($this->getProvider(), $view, 'dummy.tpl')
        );
    }

    public function processDelete(ProcessHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $this->entityManager->remove($hookedObject);
        $this->entityManager->flush();
    }

    protected function getProvider()
    {
        return 'provider.cmfcmfmediamodule.ui_hooks.' . $this->getType();
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    public function __($msg)
    {
        return __($msg, $this->domain);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param int    $n  Count.
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _n($m1, $m2, $n, $this->domain);
    }

    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->domain);
    }

    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular.
     * @param string       $m2    Plural.
     * @param int          $n     Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function _fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->domain);
    }
}
