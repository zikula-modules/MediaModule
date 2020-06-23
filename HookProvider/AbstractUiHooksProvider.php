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

namespace Cmfcmf\Module\MediaModule\HookProvider;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\HookProviderInterface;

abstract class AbstractUiHooksProvider implements HookProviderInterface
{
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
     * @var Environment
     */
    protected $twig;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        Environment $twig,
        SecurityManager $securityManager
    ) {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        $this->securityManager = $securityManager;
    }

    public function getOwner(): string
    {
        return 'CmfcmfMediaModule';
    }

    public function getCategory(): string
    {
        return UiHooksCategory::NAME;
    }

    /**
     * Processes the hook deletion by removing the HookedObject.
     */
    public function processDelete(ProcessHook $hook)
    {
        $repository = $this->entityManager
            ->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $this->entityManager->remove($hookedObject);
        $this->entityManager->flush();
    }
}
