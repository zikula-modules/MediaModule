<?php

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("/", options={"expose" = true})
     */
    public function indexAction()
    {
        $this->ensurePermission();

        return $this->redirectToRoute("cmfcmfmediamodule_settings_requirements");
    }

    /**
     * @Route("/requirements")
     * @Template()
     * @Theme("admin")
     */
    public function requirementsAction()
    {
        $this->ensurePermission();

        if (FileinfoMimeTypeGuesser::isSupported()) {
            $fileInfo = [
                'for' => $this->getTranslator()->trans('File Upload Mime Type guessing'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The FileInfo PHP extension is installed.'),
            ];
        } elseif (FileBinaryMimeTypeGuesser::isSupported()) {
            $fileInfo = [
                'for' => $this->getTranslator()->trans('File Upload Mime Type guessing'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('Unix System detected. The file command will be used.'),
            ];
        } else {
            $fileInfo = [
                'for' => $this->getTranslator()->trans('File Upload Mime Type guessing'),
                'state' => 'danger',
                'message' => $this->getTranslator()->trans('You must either enable the PHP FileInfo extension or switch to a Unix system.'),
            ];
        }
        $maxUploadSize = PHPIniHelper::getMaxUploadSize();
        $uploadSize = [
            'for' => $this->getTranslator()->trans('File Uploads'),
            'state' => $maxUploadSize >= 20 * 1000 * 1000 ? 'success' : 'warning',
            'message' => $this->getTranslator()->trans(
                'You can upload files of at most %size%. Consider raising "upload_max_filesize" and "post_max_size" in your php.ini file to upload larger files.',
                ['%size%' => PHPIniHelper::formatFileSize($maxUploadSize)]),
        ];


        $highMemoryRequired = false;
        try {
            new \Imagine\Imagick\Imagine();

            $imagine = [
                'for' => $this->getTranslator()->trans('Thumbnail generation'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The Imagick PHP extension is installed.'),
            ];
        } catch (\Imagine\Exception\RuntimeException $e) {
            try {
                new \Imagine\Gmagick\Imagine();

                $imagine = [
                    'for' => $this->getTranslator()->trans('Thumbnail generation'),
                    'state' => 'success',
                    'message' => $this->getTranslator()->trans('The Gmagick PHP extension is installed.'),
                ];
            } catch (\Imagine\Exception\RuntimeException $e) {
                try {
                    new \Imagine\Gd\Imagine();

                    $highMemoryRequired = true;
                    $imagine = [
                        'for' => $this->getTranslator()->trans('Thumbnail generation'),
                        'state' => 'warning',
                        'message' => $this->getTranslator()->trans('You only have the Gd Image processing extension installed. You will need to install the Imagick extension (recommended) or, at last resort, raise your PHP memory limit to _at least_ 256MB !'),
                    ];
                } catch (\Imagine\Exception\RuntimeException $e) {
                    $imagine = [
                        'for' => $this->getTranslator()->trans('Thumbnail generation'),
                        'state' => 'danger',
                        'message' => $this->getTranslator()->trans('No image processing extension installed. You must install the Imagick, Gmagick or, at the very least, Gd extension!'),
                    ];
                }
            }
        }

        if ($highMemoryRequired && PHPIniHelper::getMemoryLimit() < 128 * 1024 * 1024) {
            $memoryLimit = [
                'for' => $this->getTranslator()->trans('Thumbnail generation'),
                'state' => 'warning',
                'message' => $this->getTranslator()->trans('Your memory limit is below 128MB. Please consider rising it to avoid issues with thumbnail generation.'),
            ];
        } else {
            $memoryLimit = [
                'for' => $this->getTranslator()->trans('Thumbnail generation'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('Your memory limit is high enough for thumbnail generation.'),
            ];
        }

        if (class_exists('ZipArchive')) {
            $zip = [
                'for' => $this->getTranslator()->trans('Module upgrade and ZIP content preview'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The ZIP PHP extension is installed.'),
            ];
        } else {
            $zip = [
                'for' => $this->getTranslator()->trans('Module upgrade and ZIP content preview'),
                'state' => 'danger',
                'message' => $this->getTranslator()->trans('The ZIP PHP extension is missing.'),
            ];
        }
        if (extension_loaded('curl')) {
            $curl = [
                'for' => $this->getTranslator()->trans('Module upgrade'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The CURL PHP extension is installed.'),
            ];
        } else {
            $curl = [
                'for' => $this->getTranslator()->trans('Module upgrade'),
                'state' => 'danger',
                'message' => $this->getTranslator()->trans('The CURL PHP extension is missing.'),
            ];
        }
        if (class_exists('PharData')) {
            $phar = [
                'for' => $this->getTranslator()->trans('TAR archive content preview'),
                'state' => 'success',
                'message' => $this->getTranslator()->trans('The Phar PHP extension is installed.'),
            ];
        } else {
            $phar = [
                'for' => $this->getTranslator()->trans('TAR archive content preview'),
                'state' => 'warning',
                'message' => $this->getTranslator()->trans('The Phar PHP extension is missing.'),
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
     * @Template()
     * @Theme("admin")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function generalAction(Request $request)
    {
        $this->ensurePermission();

        $collectionTemplateCollection = $this->get(
            'cmfcmf_media_module.collection_template_collection');
        $translator = $this->get('translator');

        $form = $this->createForm(
            new SettingsType(
                $translator,
                $collectionTemplateCollection->getCollectionTemplateTitles()));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            foreach ($data as $name => $value) {
                \ModUtil::setVar('CmfcmfMediaModule', $name, $value);
            }
            $this->addFlash('status', $this->__('Settings saved!'));
        }

        $scribiteInstalled = \ModUtil::available('Scribite');
        $descriptionEscapingStrategyForCollectionOk = true;
        $descriptionEscapingStrategyForMediaOk = true;

        if ($scribiteInstalled) {
            $mediaBinding = $this->get('hook_dispatcher')->getBindingBetweenAreas(
                "subscriber.cmfcmfmediamodule.ui_hooks.media",
                "provider.scribite.ui_hooks.editor");
            $collectionBinding = $this->get('hook_dispatcher')->getBindingBetweenAreas(
                "subscriber.cmfcmfmediamodule.ui_hooks.collection",
                "provider.scribite.ui_hooks.editor");

            $descriptionEscapingStrategyForCollectionOk = !is_object($collectionBinding)
                || \ModUtil::getVar(
                    'CmfcmfMediaModule',
                    'descriptionEscapingStrategyForCollection') == 'raw';
            $descriptionEscapingStrategyForMediaOk = !is_object($mediaBinding)
                || \ModUtil::getVar(
                    'CmfcmfMediaModule',
                    'descriptionEscapingStrategyForMedia') == 'raw';
        }

        return [
            'form' => $form->createView(),
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
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            'settings',
            'admin')
        ) {
            throw new AccessDeniedException();
        }
    }
}
