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
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
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
            'zikula-version-check' => [
                'text' => $this->translator->trans('Checking installed Zikula version', [], 'cmfcmfmediamodule'),
                'icon' => 'fa-code-fork'
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
            case 'zikula-version-check':
                $zip = new \ZipArchive();
                $zip->open($this->cacheFile);
                $content = $zip->getFromName('composer.json');
                if ($content === false) {
                    throw new UpgradeFailedException(
                        $this->translator->trans(
                            'Could not read composer.json file from downloaded zip archive.',
                            [],
                            'cmfcmfmediamodule'
                        )
                    );
                }
                $json = json_decode($content);
                $coreCompat = $json['extra']['zikula']['core-compatibility'];
                $coreCompatExpr = new expression($coreCompat);
                if (!$coreCompatExpr->satisfiedBy(new version(\Zikula_Core::VERSION_NUM))) {
                    throw new UpgradeFailedException(
                        $this->translator->trans(
                            'Your installed Core version is not capable of running the upgrade. Please upgrade your core to match %version% first.',
                            ['%version%' => $coreCompat],
                            'cmfcmfmediamodule'
                        )
                    );
                }

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
     */
    private function checkPermissions()
    {
        if (!$this->is_writable_r($this->moduleDir)) {
            throw new UpgradeFailedException(
                $this->translator->trans('Please make %s and recursively writable.', ['%s' => $this->moduleDir], 'cmfcmfmediamodule'));
        }
    }

    /**
     * Checks whether a given directory is recursively writable.
     *
     * @param string $dir The directory to check.
     *
     * @return bool
     */
    private function is_writable_r($dir)
    {
        if (is_dir($dir)) {
            if (is_writable($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (!$this->is_writable_r($dir."/".$object)) {
                            return false;
                        }
                    }
                }

                return true;
            } else {
                return false;
            }
        } elseif (file_exists($dir)) {
            return is_writable($dir);
        }

        return false;
    }

    /**
     * Downloads the new version from the given URL.
     *
     * @param string $url
     */
    private function downloadNewVersion($url)
    {
        file_put_contents($this->cacheFile, fopen($url, 'r'));
    }

    /**
     * Extracts the new version, removes old files and clears the cache.
     */
    private function extractNewVersion()
    {
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
