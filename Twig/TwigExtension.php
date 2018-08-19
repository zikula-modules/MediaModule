<?php

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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcher;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

/**
 * Provides some custom Twig extensions.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var MarkdownExtra
     */
    private $markdownExtra;

    /**
     * @var HookDispatcher
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param MarkdownExtra        $markdownExtra
     * @param HookDispatcher       $hookDispatcher
     * @param SecurityManager      $securityManager
     * @param VersionChecker       $versionChecker
     * @param TranslatorInterface  $translator
     * @param VariableApiInterface $variableApi
     * @param RequestStack         $requestStack
     */
    public function __construct(
        MarkdownExtra $markdownExtra,
        HookDispatcher $hookDispatcher,
        SecurityManager $securityManager,
        VersionChecker $versionChecker,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        RequestStack $requestStack
    ) {
        $this->markdownExtra = $markdownExtra;
        $this->hookDispatcher = $hookDispatcher;
        $this->securityManager = $securityManager;
        $this->versionChecker = $versionChecker;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('cmfcmfmediamodule_getdescription', [$this, 'escapeDescription'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('cmfcmfmediamodule_unamefromuid', [$this, 'userNameFromUid']),
            new \Twig_SimpleFilter('cmfcmfmediamodule_avatarfromuid', [$this, 'avatarFromUid']),
            new \Twig_SimpleFilter('cmfcmfmediamodule_categorytitle', [$this, 'categoryTitle'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cmfcmfmediamodule_hasPermission', [$this, 'hasPermission']),
            new \Twig_SimpleFunction('cmfcmfmediamodule_newversionavailable', [$this, 'newVersionAvailable']),
            new \Twig_SimpleFunction('cmfcmfmediamodule_maxfilesize', [$this, 'maxFileSize'])
        ];
    }

    public function categoryTitle(CategoryEntity $category)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        return $category->getDisplay_name($locale);
    }

    /**
     * Checks whether or not a new version of the module is available.
     *
     * @return array|bool|string
     */
    public function newVersionAvailable()
    {
        $lastNewVersionCheck = $this->variableApi->get('CmfcmfMediaModule', 'lastNewVersionCheck', 0);
        $currentVersion = \ModUtil::getInfoFromName('CmfcmfMediaModule')['version'];
        if (time() > $lastNewVersionCheck + 24 * 60 * 60) {
            // Last version check older than a day.
            $this->checkForNewVersion($currentVersion);
        }

        $newVersionAvailable = $this->variableApi->get('CmfcmfMediaModule', 'newVersionAvailable', false);
        if ($newVersionAvailable != false) {
            if ($newVersionAvailable == $currentVersion) {
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
            if ($release !== false) {
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

        $eventName = "cmfcmfmediamodule.filter_hooks.$hookName.filter";
        $hook = new \Zikula_FilterHook($eventName, $description);
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
     * Converts a user id to it's username.
     *
     * @param int $uid The user id.
     *
     * @return string
     *
     * @TODO maybe remove in favour of core function
     * @see https://github.com/zikula/core/blob/2.0/src/docs/Twig/Functions.md#profiles
     */
    public function userNameFromUid($uid)
    {
        if ($uid == 0) {
            return $this->translator->trans('Anonymous', [], 'cmfcmfmediamodule');
        }
        $uname = \UserUtil::getVar('uname', $uid);
        $realname = \UserUtil::getVar('realname', $uid);

        return !empty($realname) ? $realname : $uname;
    }

    /**
     * Returns the url to the avatar image of the given user by it's id.
     *
     * @param int $uid The user id.
     *
     * @return string
     *
     * @TODO maybe remove in favour of core function
     * @see https://github.com/zikula/core/blob/2.0/src/docs/Twig/Functions.md#profiles
     */
    public function avatarFromUid($uid)
    {
        $email = \UserUtil::getVar('email', $uid);

        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/$hash.jpg?d=mm";
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
