<?php

namespace Cmfcmf\Module\MediaModule\Upgrade;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var string
     */
    private $domain;

    public function __construct(TranslatorInterface $translator, $kernelCacheDir, $kernelRootDir)
    {
        $this->fs = new Filesystem();
        $this->cacheFile = $kernelCacheDir . "/CmfcmfMediaModule.zip";

        $zikulaDir = realpath($kernelRootDir . '/..');
        $this->moduleDir = $this->fs->makePathRelative(realpath(__DIR__ . '/..'), $zikulaDir);
        $this->moduleDir = rtrim($this->moduleDir, '/\\');

        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
        $this->translator = $translator;
    }

    public function checkRequirements()
    {
        if (class_exists('ZipArchive') && extension_loaded('curl')) {
            return true;
        }

        return $this->translator->trans('Please enable the ZIP and CURL PHP extensions', [], $this->domain);
    }

    public function checkPermissions()
    {
        if (!is_writable($this->moduleDir)) {
            return $this->translator->trans('Please make %s writable.', ['%s' => $this->moduleDir], $this->domain);
        }
        if (!is_writable($this->moduleDir . '/Controller/MediaController.php')) {
            return $this->translator->trans('%s is writable but it\'s content is not. Please make sure to make it recursively writable.', ['%s' => $this->moduleDir], $this->domain);
        }
        if (!is_writable($this->moduleDir . '/.gitignore')) {
            return $this->translator->trans('%s is writable but some files are not. Please make sure to make it recursively writable. If you already tried, please try "chmod 777 -R media-module" from within the cmfcmf folder.', ['%s' => $this->moduleDir], $this->domain);
        }

        return true;
    }

    public function downloadNewVersion($url)
    {
        file_put_contents($this->cacheFile, fopen($url, 'r'));

        return true;
    }

    public function extractNewVersion()
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

        return true;
    }

    public function upgrade()
    {
        $filemodules = \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getfilemodules');
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', ['filemodules' => $filemodules]);

        $worked = \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgrade', [
            'id' => \ModUtil::getIdFromName('CmfcmfMediaModule')
        ]);

        if ($worked == true) {
            return $worked;
        }

        return $this->translator->trans('Something went wrong with the upgrade code. This should not have happened!', [], $this->domain);
    }
}
