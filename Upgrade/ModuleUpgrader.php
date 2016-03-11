<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Upgrade;

use Cmfcmf\Module\MediaModule\Exception\UpgradeFailedException;
use Github\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;

/**
 * Upgrades the module.
 */
class ModuleUpgrader
{
    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var string
     */
    private $moduleDir;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * ModuleUpgrader constructor.
     *
     * @param TranslatorInterface $translator
     * @param CacheClearer        $cacheClearer
     * @param string              $kernelCacheDir
     * @param string              $kernelRootDir
     */
    public function __construct(TranslatorInterface $translator,
        CacheClearer $cacheClearer,
        $kernelCacheDir,
        $kernelRootDir
    ) {
        $this->translator = $translator;
        $this->cacheClearer = $cacheClearer;

        $this->fs = new Filesystem();
        $this->cacheFile = $kernelCacheDir . "/CmfcmfMediaModule.zip";

        $zikulaDir = realpath($kernelRootDir . '/..');
        $this->moduleDir = $this->fs->makePathRelative(realpath(__DIR__ . '/..'), $zikulaDir);
        $this->moduleDir = rtrim($this->moduleDir, '/\\');
    }

    public function getUpgradeSteps()
    {
        return [
            'php-check' => [
                'text' => $this->translator->trans('Checking server requirements', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-server'
            ],
            'version-check' => [
                'text' => $this->translator->trans('Checking installed and available versions', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-github'
            ],
            'permission-check' => [
                'text' => $this->translator->trans('Checking file system permissions', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-files-o'
            ],
            'download' => [
                'text' => $this->translator->trans('Downloading new version', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-download'
            ],
            'extracting' => [
                'text' => $this->translator->trans('Extracting new version', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-file-archive-o'
            ],
            'upgrading' => [
                'text' => $this->translator->trans('Running upgrade', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-code'
            ],
        ];
    }

    /**
     * Execute the upgrade.
     *
     * @param string         $step           The step to execute.
     * @param VersionChecker $versionChecker
     *
     * @return bool Whether or not the upgrade is done.
     */
    public function upgrade($step, VersionChecker $versionChecker)
    {
        switch ($step) {
            case 'php-check':
                $this->checkRequirements();

                return false;
            case 'version-check':
                $this->getReleaseToUpgradeTo($versionChecker);

                return false;
            case 'permission-check':
                $this->checkPermissions();

                return false;
            case 'download':
                $release = $this->getReleaseToUpgradeTo($versionChecker);
                $asset = $versionChecker->getZipAssetFromRelease($release);
                if (!$asset) {
                    throw new UpgradeFailedException(
                        $this->translator->trans(
                            'Something went wrong. The release doesn\'t contain a ZIP asset.',
                            [],
                            'cmfcmfmediamodule'));
                }
                $this->downloadNewVersion($asset['browser_download_url']);

                return false;
            case 'extracting':
                $this->extractNewVersion();

                return false;
            case 'upgrading':
                $this->doUpgrade();

                return true;
            default:
                throw new \RuntimeException('Invalid upgrade step received!');
        }
    }

    /**
     * Checks whether the server fulfills all necessary requirements.
     *
     * @return bool|string
     */
    private function checkRequirements()
    {
        if (!class_exists('ZipArchive') || !extension_loaded('curl')) {
            throw new UpgradeFailedException(
                $this->translator->trans('Please enable the ZIP and CURL PHP extensions', [], 'cmfcmfmediamodule'));
        }
    }

    /**
     * Checks whether the permissions for the MediaModule directory are setup correctly.
     *
     * @return bool|string
     */
    private function checkPermissions()
    {
        if (!is_writable($this->moduleDir)) {
            throw new UpgradeFailedException(
                $this->translator->trans('Please make %s writable.', ['%s' => $this->moduleDir], 'cmfcmfmediamodule'));
        }
        if (!is_writable($this->moduleDir . '/Controller/MediaController.php')) {
            throw new UpgradeFailedException(
                $this->translator->trans(
                    '%s is writable but it\'s content is not. Please make sure to make it recursively writable.',
                    ['%s' => $this->moduleDir],
                    'cmfcmfmediamodule'));
        }
        if (!is_writable($this->moduleDir . '/.gitignore')) {
            throw new UpgradeFailedException(
                $this->translator->trans(
                    '%s is writable but some files are not. Please make sure to make it recursively writable. If you already tried, please try "chmod 777 -R media-module" from within the cmfcmf folder.',
                    ['%s' => $this->moduleDir],
                    'cmfcmfmediamodule'));
        }
    }

    /**
     * Downloads the new version from the given URL.
     *
     * @param string $url
     *
     * @return bool
     */
    private function downloadNewVersion($url)
    {
        file_put_contents($this->cacheFile, fopen($url, 'r'));
    }

    /**
     * Extracts the new version, removes old files and clears the cache.
     *
     * @return bool
     */
    private function extractNewVersion()
    {
        // First check if the module dir really is writable.
        $this->fs->touch($this->moduleDir . '/.gitignore');

        // Delete all the existing files first.
        $this->fs->remove(glob($this->moduleDir . '/*'));

        // Extract new files.
        $zip = new \ZipArchive();
        $zip->open($this->cacheFile);
        $zip->extractTo($this->moduleDir);
        $zip->close();

        $this->fs->remove($this->cacheFile);

        $this->cacheClearer->clear('symfony');
    }

    /**
     * Executes the upgrade.
     *
     * @return bool|string
     */
    private function doUpgrade()
    {
        $filemodules = \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getfilemodules');
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', ['filemodules' => $filemodules]);

        $worked = \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgrade', [
            'id' => \ModUtil::getIdFromName('CmfcmfMediaModule')
        ]);

        if ($worked != true) {
            throw new UpgradeFailedException(
                $this->translator->trans(
                    'Something went wrong with the upgrade code. This should not have happened!',
                    [],
                    'cmfcmfmediamodule'));
        }
    }

    /**
     * @param VersionChecker $versionChecker
     *
     * @return array The release to upgrade to.
     */
    private function getReleaseToUpgradeTo(VersionChecker $versionChecker)
    {
        try {
            if (!$versionChecker->checkRateLimit()) {
                throw new UpgradeFailedException(
                    $this->translator->trans(
                        'Your GitHub API Rate limit is exceeded. Please try again later.',
                        [],
                        'cmfcmfmediamodule'));
            }
            $info = \ModUtil::getInfoFromName('CmfcmfMediaModule');
            $release = $versionChecker->getReleaseToUpgradeTo($info['version']);
            if ($release === false) {
                throw new UpgradeFailedException(
                    $this->translator->trans('No release to upgrade to available!', [], 'cmfcmfmediamodule'));
            }

            return $release;
        } catch (RuntimeException $e) {
            // Something went wrong with the GitHub API.
            throw new UpgradeFailedException($this->translator->trans(
                'Could not connect to GitHub. Is the server connected to the internet?',
                [],
                'cmfcmfmediamodule'));
        }
    }
}
