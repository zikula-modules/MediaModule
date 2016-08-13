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
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Cmfcmf\Module\MediaModule\Upgrade\VersionChecker;
use Github\Exception\RuntimeException;
use Michelf\MarkdownExtra;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var \Zikula_HookDispatcher
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
     * @param MarkdownExtra          $markdownExtra
     * @param \Zikula_HookDispatcher $hookDispatcher
     * @param SecurityManager        $securityManager
     * @param VersionChecker         $versionChecker
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        MarkdownExtra $markdownExtra,
        \Zikula_HookDispatcher $hookDispatcher,
        SecurityManager $securityManager,
        VersionChecker $versionChecker,
        TranslatorInterface $translator
    ) {
        $this->markdownExtra = $markdownExtra;
        $this->hookDispatcher = $hookDispatcher;
        $this->securityManager = $securityManager;
        $this->versionChecker = $versionChecker;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'cmfcmfmediamodule_getdescription',
                [$this, 'escapeDescription'],
                ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('cmfcmfmediamodule_unamefromuid', [$this, 'userNameFromUid']),
            new \Twig_SimpleFilter('cmfcmfmediamodule_avatarfromuid', [$this, 'avatarFromUid'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cmfcmfmediamodule_hasPermission', [$this, 'hasPermission']),
            new \Twig_SimpleFunction(
                'cmfcmfmediamodule_newversionavailable',
                [$this, 'newVersionAvailable']),
            new \Twig_SimpleFunction('cmfcmfmediamodule_maxfilesize', [$this, 'maxFileSize'])
        ];
    }

    /**
     * Checks whether or not a new version of the module is available.
     *
     * @return array|bool|string
     */
    public function newVersionAvailable()
    {
        $lastNewVersionCheck = \ModUtil::getVar('CmfcmfMediaModule', 'lastNewVersionCheck', 0);
        $currentVersion = \ModUtil::getInfoFromName('CmfcmfMediaModule')['version'];
        if (time() > $lastNewVersionCheck + 24 * 60 * 60) {
            // Last version check older than a day.
            $this->checkForNewVersion($currentVersion);
        }

        $newVersionAvailable = \ModUtil::getVar('CmfcmfMediaModule', 'newVersionAvailable', false);
        if ($newVersionAvailable != false) {
            if ($newVersionAvailable == $currentVersion) {
                // Somehow the user manually upgraded the module.
                // Remove "Install new version" popup.
                \ModUtil::setVar('CmfcmfMediaModule', 'newVersionAvailable', false);
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

        \ModUtil::setVar('CmfcmfMediaModule', 'lastNewVersionCheck', time());
        try {
            if (!$this->versionChecker->checkRateLimit()) {
                // The remaining rate limit isn't high enough.
                return;
            }
            $release = $this->versionChecker->getReleaseToUpgradeTo($currentVersion);
            if ($release !== false) {
                \ModUtil::setVar('CmfcmfMediaModule', 'newVersionAvailable', $release['tag_name']);
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
            $strategy = \ModUtil::getVar(
                'CmfcmfMediaModule',
                'descriptionEscapingStrategyForCollection');
            $hookName = 'collections';
        } elseif ($entity instanceof AbstractMediaEntity) {
            $strategy = \ModUtil::getVar(
                'CmfcmfMediaModule',
                'descriptionEscapingStrategyForMedia');
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
                return htmlentities($description);
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
        // Based on http://stackoverflow.com/a/22500394/2560557
        // by Deckard http://stackoverflow.com/users/974390/deckard
        $phpSizeToBytes = function ($sSize) {
            if (is_numeric($sSize)) {
                return $sSize;
            }
            $sSuffix = substr($sSize, -1);
            $iValue = substr($sSize, 0, -1);
            switch (strtoupper($sSuffix)) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'P':
                    $iValue *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'T':
                    $iValue *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'G':
                    $iValue *= 1024;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'M':
                    $iValue *= 1024;
                case 'K':
                    $iValue *= 1024;
                    break;
            }

            return $iValue;
        };

        static $max = -1;

        if ($max < 0) {
            $max = $phpSizeToBytes(ini_get('post_max_size'));
            $uploadMax = $phpSizeToBytes(ini_get('upload_max_filesize'));
            if ($uploadMax > 0 && $uploadMax < $max) {
                $max = $uploadMax;
            }
        }

        return $max;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cmfcmfmediamodule_twigextension';
    }
}
