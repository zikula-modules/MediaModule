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

namespace Cmfcmf\Module\MediaModule\Twig;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Helper\PHPIniHelper;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Cmfcmf\Module\MediaModule\Upgrade\VersionChecker;
use Github\Exception\RuntimeException;
use Michelf\MarkdownExtra;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\FilterHook;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * Provides some custom Twig extensions.
 */
class TwigExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MarkdownExtra
     */
    private $markdownExtra;

    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var VersionChecker
     */
    private $versionChecker;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @param TranslatorInterface          $translator
     * @param MarkdownExtra                $markdownExtra
     * @param HookDispatcherInterface      $hookDispatcher
     * @param SecurityManager              $securityManager
     * @param VersionChecker               $versionChecker
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param VariableApiInterface         $variableApi
     */
    public function __construct(
        TranslatorInterface $translator,
        MarkdownExtra $markdownExtra,
        HookDispatcherInterface $hookDispatcher,
        SecurityManager $securityManager,
        VersionChecker $versionChecker,
        ExtensionRepositoryInterface $extensionRepository,
        VariableApiInterface $variableApi
    ) {
        $this->translator = $translator;
        $this->markdownExtra = $markdownExtra;
        $this->hookDispatcher = $hookDispatcher;
        $this->securityManager = $securityManager;
        $this->versionChecker = $versionChecker;
        $this->extensionRepository = $extensionRepository;
        $this->variableApi = $variableApi;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('cmfcmfmediamodule_getdescription', [$this, 'escapeDescription'], ['is_safe' => ['html']])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('cmfcmfmediamodule_hasPermission', [$this, 'hasPermission']),
            new TwigFunction('cmfcmfmediamodule_newversionavailable', [$this, 'newVersionAvailable']),
            new TwigFunction('cmfcmfmediamodule_maxfilesize', [$this, 'maxFileSize'])
        ];
    }

    /**
     * Checks whether or not a new version of the module is available.
     *
     * @return array|bool|string
     */
    public function newVersionAvailable()
    {
        $lastNewVersionCheck = $this->variableApi->get('CmfcmfMediaModule', 'lastNewVersionCheck', 0);

        $extension = $this->extensionRepository->findOneByName('CmfcmfMediaModule');
        if (null === $extension) {
            return false;
        }

        $currentVersion = $extension['version'];
        if (time() > $lastNewVersionCheck + 24 * 60 * 60) {
            // Last version check older than a day.
            $this->checkForNewVersion($currentVersion);
        }

        $newVersionAvailable = $this->variableApi->get('CmfcmfMediaModule', 'newVersionAvailable', false);
        if (false !== $newVersionAvailable) {
            if ($newVersionAvailable === $currentVersion) {
                // Somehow the user manually upgraded the module.
                // Remove "Install new version" popup.
                $this->variableApi->set('CmfcmfMediaModule', 'newVersionAvailable', false);
            } else {
                return $newVersionAvailable;
            }
        }

        return false;
    }

    /**
     * Checks if a new version of the module is available.
     * Requires the CURL PHP extension.
     *
     * @param $currentVersion
     */
    private function checkForNewVersion($currentVersion)
    {
        if (!function_exists('curl_init')) {
            return;
        }

        $this->variableApi->set('CmfcmfMediaModule', 'lastNewVersionCheck', time());
        try {
            if (!$this->versionChecker->checkRateLimit()) {
                // The remaining rate limit isn't high enough.
                return;
            }
            $release = $this->versionChecker->getReleaseToUpgradeTo($currentVersion);
            if (false !== $release) {
                $this->variableApi->set('CmfcmfMediaModule', 'newVersionAvailable', $release['tag_name']);
            }
        } catch (RuntimeException $e) {
            // Something went wrong with the GitHub API. Fail silently.
        }
    }

    /**
     * @param CollectionEntity|AbstractMediaEntity $entity
     *
     * @return string
     */
    public function escapeDescription($entity)
    {
        $description = $entity->getDescription();

        $strategy = null;
        $hookName = null;
        if ($entity instanceof CollectionEntity) {
            $strategy = $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection');
            $hookName = 'collections';
        } elseif ($entity instanceof AbstractMediaEntity) {
            $strategy = $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia');
            $hookName = 'media';
        } else {
            throw new \LogicException();
        }

        $eventName = 'cmfcmfmediamodule.filter_hooks.' . $hookName . '.filter';
        $hook = new FilterHook($description);
        $description = $this->hookDispatcher->dispatch($eventName, $hook)->getData();

        switch ($strategy) {
            case 'raw':
                return $description;
            case 'text':
                return nl2br(htmlentities($description));
            case 'markdown':
                return $this->markdownExtra->transform($description);
            default:
                throw new \LogicException();
        }
    }

    /**
     * Checks whether or not the current user has permission.
     *
     * @param string|object $objectOrType
     * @param string        $action
     *
     * @return bool
     */
    public function hasPermission($objectOrType, $action)
    {
        return $this->securityManager->hasPermission($objectOrType, $action);
    }

    public function maxFileSize()
    {
        return PHPIniHelper::getMaxUploadSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cmfcmfmediamodule_twigextension';
    }
}
