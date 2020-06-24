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

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Config\FileLocatorInterface;

abstract class AbstractFileMediaType extends AbstractMediaType
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var string
     */
    private $zikulaRoot;

    /**
     * @var CacheManager
     */
    protected $imagineCacheManager;

    public function injectThings(
        FileLocatorInterface $fileLocator,
        CacheManager $cacheManager,
        string $projectDir
    ) {
        $this->fileLocator = $fileLocator;
        $this->imagineCacheManager = $cacheManager;
        $this->zikulaRoot = realpath($projectDir . '/..');
    }

    public function getPathToFile($identifier)
    {
        $path = $this->fileLocator->locate($identifier);

        return str_replace('\\', '/', mb_substr($path, mb_strlen($this->zikulaRoot) + 1));
    }

    public function getUrlToFile($identifier)
    {
        return $this->getBaseUri() . '/' . $this->getPathToFile($identifier);
    }

    protected function getImagineRuntimeOptions(AbstractFileEntity $entity, $file, $width, $height, $mode, $optimize)
    {
        if ('original' === $height || 'original' === $width) {
            $size = getimagesize($entity->getPath());
            if ('original' === $width) {
                $width = $size[0];
            }
            if ('original' === $height) {
                $height = $size[1];
            }
        }

        $watermark = $entity->getCollection()->getWatermark();
        $watermarkId = null !== $watermark ? $watermark->getId() : null;

        $options = [
            'thumbnail' => [
                'size'      => [$width, $height],
                'mode'      => ($mode ? $mode : ImageInterface::THUMBNAIL_OUTBOUND),
                'extension' => null // file extension for thumbnails (jpg, png, gif; null for original file type)
            ],
            'cmfcmfmediamodule.custom_image_filter' => [
                'watermark' => $watermarkId,
                'file' => $file,
                'width' => $width,
                'height' => $height,
                'mode' => $mode,
                'optimize' => $optimize
            ]
        ];

        return $options;
    }

    protected function getIconThumbnailByFileExtension(AbstractFileEntity $entity, $width, $height, $format = 'html', $mode = ImageInterface::THUMBNAIL_OUTBOUND, $optimize = true, $forceExtension = false)
    {
        return false;
        // @todo Re-enable?
        if (!in_array($mode, [ImageInterface::THUMBNAIL_INSET, ImageInterface::THUMBNAIL_OUTBOUND])) {
            $mode = ImageInterface::THUMBNAIL_INSET;
        }

        $availableSizes = [16, 32, 48, 512];
        $chosenSize = 0;
        foreach ($availableSizes as $size) {
            if ($width <= $size && $height <= $size) {
                break;
            }
            $chosenSize = $size;
        }
        $extension = $forceExtension ? $forceExtension : pathinfo($entity->getFileName(), PATHINFO_EXTENSION);
        $icon = '@CmfcmfMediaModule/Resources/public/images/file-icons/' . $chosenSize . 'px/' . $extension . '.png';

        try {
            $path = $this->getPathToFile($icon);
        } catch (\InvalidArgumentException $e) {
            $icon = '@CmfcmfMediaModule/Resources/public/images/file-icons/' . $chosenSize . 'px/_blank.png';
            $path = $this->getPathToFile($icon);
        }

        $imagineOptions = $this->getImagineRuntimeOptions($entity, $path, $width, $height, $mode, $optimize);
        $path = $this->imagineCacheManager->getBrowserPath($path, 'zkroot', $imagineOptions);

        $url = $this->getBaseUri() . '/' . $path;
        switch ($format) {
            case 'url':
                return $url;
            case 'html':
                return '<img src="' . $url . '" />';
            case 'path':
                return $path;
        }
        throw new \LogicException();
    }

    public function getOriginalWithWatermark(AbstractFileEntity $entity, $mode, $optimize = true)
    {
        switch ($mode) {
            case 'path':
                return $entity->getPath();
            case 'url':
                return $entity->getUrl();
            default:
                throw new \LogicException();
        }
    }

    protected function getBaseUri()
    {
        return $this->requestStack->getCurrentRequest()->getBasePath();
    }
}
