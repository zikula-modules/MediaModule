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

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Form\SettingsType;
use Cmfcmf\Module\MediaModule\Helper\PHPIniHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\FileBinaryMimeTypeGuesser;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("/", options={"expose" = true})
     */
    public function index()
    {
        $this->ensurePermission();

        return $this->redirectToRoute("cmfcmfmediamodule_settings_requirements");
    }

    /**
     * @Route("/requirements")
     * @Template("@CmfcmfMediaModule/Settings/requirements.html.twig")
     * @Theme("admin")
     */
    public function requirements()
    {
        $this->ensurePermission();
        $fileInfoGuesser = new FileinfoMimeTypeGuesser();
        $fileBinaryGuesser = new FileBinaryMimeTypeGuesser();

        if ($fileInfoGuesser->isGuesserSupported()) {
            $fileInfo = [
                'for' => $this->getTranslator()->trans('File Upload Mime Type guessing', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The FileInfo PHP extension is installed.', [], 'cmfcmfmediamodule'),
            ];
        } elseif ($fileBinaryGuesser->isGuesserSupported()) {
            $fileInfo = [
                'for' => $this->getTranslator()->trans('File Upload Mime Type guessing', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('Unix System detected. The file command will be used.', [], 'cmfcmfmediamodule'),
            ];
        } else {
            $fileInfo = [
                'for' => $this->getTranslator()->trans('File Upload Mime Type guessing', [], 'cmfcmfmediamodule'),
                'state' => 'danger',
                'message' => $this->getTranslator()->trans('You must either enable the PHP FileInfo extension or switch to a Unix system.', [], 'cmfcmfmediamodule'),
            ];
        }
        $maxUploadSize = PHPIniHelper::getMaxUploadSize();
        $uploadSize = [
            'for' => $this->getTranslator()->trans('File Uploads', [], 'cmfcmfmediamodule'),
            'state' => $maxUploadSize >= 20 * 1000 * 1000 ? 'success' : 'warning',
            'message' => $this->getTranslator()->trans(
                'You can upload files of at most %size%. Consider raising "upload_max_filesize" and "post_max_size" in your php.ini file to upload larger files.',
                ['%size%' => PHPIniHelper::formatFileSize($maxUploadSize)],
                'cmfcmfmediamodule'
            ),
        ];

        $highMemoryRequired = false;
        try {
            new \Imagine\Imagick\Imagine();

            $imagine = [
                'for' => $this->getTranslator()->trans('Thumbnail generation', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The Imagick PHP extension is installed.', [], 'cmfcmfmediamodule'),
            ];
        } catch (\Imagine\Exception\RuntimeException $e) {
            try {
                new \Imagine\Gmagick\Imagine();

                $imagine = [
                    'for' => $this->getTranslator()->trans('Thumbnail generation', [], 'cmfcmfmediamodule'),
                    'state' => 'success',
                    'message' => $this->getTranslator()->trans('The Gmagick PHP extension is installed.', [], 'cmfcmfmediamodule'),
                ];
            } catch (\Imagine\Exception\RuntimeException $e) {
                try {
                    new \Imagine\Gd\Imagine();

                    $highMemoryRequired = true;
                    $imagine = [
                        'for' => $this->getTranslator()->trans('Thumbnail generation', [], 'cmfcmfmediamodule'),
                        'state' => 'warning',
                        'message' => $this->getTranslator()->trans('You only have the Gd Image processing extension installed. You will need to install the Imagick extension (recommended) or, at last resort, raise your PHP memory limit to _at least_ 256MB !', [], 'cmfcmfmediamodule'),
                    ];
                } catch (\Imagine\Exception\RuntimeException $e) {
                    $imagine = [
                        'for' => $this->getTranslator()->trans('Thumbnail generation', [], 'cmfcmfmediamodule'),
                        'state' => 'danger',
                        'message' => $this->getTranslator()->trans('No image processing extension installed. You must install the Imagick, Gmagick or, at the very least, Gd extension!', [], 'cmfcmfmediamodule'),
                    ];
                }
            }
        }

        if ($highMemoryRequired && PHPIniHelper::getMemoryLimit() < 128 * 1024 * 1024) {
            $memoryLimit = [
                'for' => $this->getTranslator()->trans('Thumbnail generation', [], 'cmfcmfmediamodule'),
                'state' => 'warning',
                'message' => $this->getTranslator()->trans('Your memory limit is below 128MB. Please consider rising it to avoid issues with thumbnail generation.', [], 'cmfcmfmediamodule'),
            ];
        } else {
            $memoryLimit = [
                'for' => $this->getTranslator()->trans('Thumbnail generation', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('Your memory limit is high enough for thumbnail generation.', [], 'cmfcmfmediamodule'),
            ];
        }

        if (class_exists('ZipArchive')) {
            $zip = [
                'for' => $this->getTranslator()->trans('Module upgrade and ZIP content preview', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The ZIP PHP extension is installed.', [], 'cmfcmfmediamodule'),
            ];
        } else {
            $zip = [
                'for' => $this->getTranslator()->trans('Module upgrade and ZIP content preview', [], 'cmfcmfmediamodule'),
                'state' => 'danger',
                'message' => $this->getTranslator()->trans('The ZIP PHP extension is missing.', [], 'cmfcmfmediamodule'),
            ];
        }
        if (extension_loaded('curl')) {
            $curl = [
                'for' => $this->getTranslator()->trans('Module upgrade', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The CURL PHP extension is installed.', [], 'cmfcmfmediamodule'),
            ];
        } else {
            $curl = [
                'for' => $this->getTranslator()->trans('Module upgrade', [], 'cmfcmfmediamodule'),
                'state' => 'danger',
                'message' => $this->getTranslator()->trans('The CURL PHP extension is missing.', [], 'cmfcmfmediamodule'),
            ];
        }
        if (class_exists('PharData')) {
            $phar = [
                'for' => $this->getTranslator()->trans('TAR archive content preview', [], 'cmfcmfmediamodule'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The Phar PHP extension is installed.', [], 'cmfcmfmediamodule'),
            ];
        } else {
            $phar = [
                'for' => $this->getTranslator()->trans('TAR archive content preview', [], 'cmfcmfmediamodule'),
                'state' => 'warning',
                'message' => $this->getTranslator()->trans('The Phar PHP extension is missing.', [], 'cmfcmfmediamodule'),
            ];
        }

        return [
            'memoryLimit' => $memoryLimit,
            'uploadSize' => $uploadSize,
            'fileInfo' => $fileInfo,
            'imagine' => $imagine,
            'curl' => $curl,
            'zip' => $zip,
            'phar' => $phar
        ];
    }

    /**
     * @Route("/general", options={"expose" = true})
     * @Template("@CmfcmfMediaModule/Settings/general.html.twig")
     * @Theme("admin")
     *
     * @return array|RedirectResponse
     */
    public function general(Request $request)
    {
        $this->ensurePermission();

        $form = $this->createForm(SettingsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data as $name => $value) {
                $this->setVar($name, $value);
            }
            $this->addFlash('status', $this->trans('Settings saved!'));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/scribite", options={"expose" = true})
     * @Template("@CmfcmfMediaModule/Settings/scribite.html.twig")
     * @Theme("admin")
     *
     * @return array
     */
    public function scribite(
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->ensurePermission();

        $scribiteInstalled = $kernel->isBundle('ZikulaScribiteModule');
        $descriptionEscapingStrategyForCollectionOk = true;
        $descriptionEscapingStrategyForMediaOk = true;

        if ($scribiteInstalled) {
            $mediaBinding = $this->hookDispatcher->getBindingBetweenAreas(
                'subscriber.cmfcmfmediamodule.ui_hooks.media',
                'provider.zikulascribitemodule.ui_hooks.editor'
            );
            $collectionBinding = $this->hookDispatcher->getBindingBetweenAreas(
                'subscriber.cmfcmfmediamodule.ui_hooks.collections',
                'provider.zikulascribitemodule.ui_hooks.editor'
            );

            $descriptionEscapingStrategyForCollectionOk = !is_object($collectionBinding)
                || 'raw' === $this->getVar('descriptionEscapingStrategyForCollection');
            $descriptionEscapingStrategyForMediaOk = !is_object($mediaBinding)
                || 'raw' === $this->getVar('descriptionEscapingStrategyForMedia');
        }

        return [
            'scribiteInstalled' => $scribiteInstalled,
            'descriptionEscapingStrategyForCollectionOk' => $descriptionEscapingStrategyForCollectionOk,
            'descriptionEscapingStrategyForMediaOk' => $descriptionEscapingStrategyForMediaOk
        ];
    }

    /**
     * Ensures the user has permission to view and update settings.
     */
    private function ensurePermission()
    {
        if (!$this->securityManager->hasPermission(
            'settings',
            'admin'
        )
        ) {
            throw new AccessDeniedException();
        }
    }
}
