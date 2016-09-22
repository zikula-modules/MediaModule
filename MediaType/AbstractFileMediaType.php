<?php

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
use Cmfcmf\Module\MediaModule\Font\FontCollection;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFileMediaType extends AbstractMediaType
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var string
     */
    private $zikulaRoot;

    /**
     * @var \SystemPlugin_Imagine_Manager
     */
    protected $imagineManager;

    /**
     * @var FontCollection
     */
    private $fontCollection;

    public function injectThings(ContainerInterface $container, FileLocatorInterface $fileLocator, FontCollection $fontCollection, $kernelRootDir)
    {
        $this->container = $container;

        $this->fileLocator = $fileLocator;
        $this->fontCollection = $fontCollection;
        $this->zikulaRoot = realpath($kernelRootDir . '/..');

        /** @var \SystemPlugin_Imagine_Manager $imagineManager */
        $imagineManager = $this->container->get('systemplugin.imagine.manager');
        $imagineManager->setModule('CmfcmfMediaModule');
        $this->imagineManager = $imagineManager;
    }

    public function getPathToFile($identifier)
    {
        $path = $this->fileLocator->locate($identifier);

        return str_replace('\\', '/', substr($path, strlen($this->zikulaRoot) + 1));
    }

    public function getUrlToFile($identifier)
    {
        return \System::getBaseUri() . '/' . $this->getPathToFile($identifier);
    }

    protected function getPreset(AbstractFileEntity $entity, $file, $width, $height, $mode, $optimize)
    {
        $watermarkId = '';
        $watermark = $entity->getCollection()->getWatermark();
        if ($watermark !== null) {
            $watermarkId = ', w ' . $watermark->getId();
        }

        if ($height == 'original' || $width == 'original') {
            $size = getimagesize($entity->getPath());
            if ($width == 'original') {
                $width = $size[0];
            }
            if ($height == 'original') {
                $height = $size[1];
            }
        }

        $preset = new \SystemPlugin_Imagine_Preset("CmfcmfMediaModule " . (string)$optimize . 'x' . substr($mode, 0, 1) . "x{$width}x{$height}x" . $watermarkId, [
            'mode' => $mode,
            'width' => $width,
            'height' => $height,
            '__module' => 'CmfcmfMediaModule',
            '__transformation' => $this->getTransformation($entity, $file, $width, $height, $mode, $optimize)
        ]);

        return $preset;
    }

    protected function getTransformation(AbstractFileEntity $entity, $file, $width, $height, $mode, $optimize)
    {
        $transformation = new \Imagine\Filter\Transformation();
        if ($optimize) {
            // Optimize for web and rotate the images (after thumbnail creation).
            $transformation
                ->add(new \Imagine\Filter\Basic\Autorotate(), 101)
                ->add(new \Imagine\Filter\Basic\WebOptimization(), 2)
            ;
        }

        $watermark = $entity->getCollection()->getWatermark();
        if ($watermark === null) {
            // The image shall not be watermarked.
            return $transformation;
        }

        $imagine = $this->imagineManager->getImagine();

        // Generate the watermark image. It will already be correctly sized
        // for the thumbnail.
        if ($mode == 'outbound') {
            $wWidth = $width;
            $wHeight = $height;
        } elseif ($mode == 'inset') {
            $imageSize = getimagesize($file);

            $ratios = [
                $width / $imageSize[0],
                $height / $imageSize[1]
            ];
            $wWidth = min($ratios) * $imageSize[0];
            $wHeight = min($ratios) * $imageSize[1];
        } else {
            throw new \LogicException();
        }

        // Check whether the image is big enough to be watermarked.
        if ($watermark->getMinSizeX() !== null && $wWidth < $watermark->getMinSizeX()) {
            return $transformation;
        }
        if ($watermark->getMinSizeY() !== null && $wHeight < $watermark->getMinSizeY()) {
            return $transformation;
        }

        $watermarkImage = $watermark->getImagineImage($imagine, $this->fontCollection, $wWidth, $wHeight);
        $watermarkSize = $watermarkImage->getSize();

        // Calculate watermark position. If the position is negative, handle
        // it as an offset from the bottom / the right side of the image.
        $x = $watermark->getPositionX();
        $y = $watermark->getPositionY();
        if ($x < 0) {
            $x += $wWidth - $watermarkSize->getWidth();
        }
        if ($y < 0) {
            $y += $wHeight - $watermarkSize->getHeight();
        }

        // If the watermark still exceeds the image's width or height, resize the watermark.
        if ($x < 0 || $y < 0 || $x + $watermarkSize->getWidth() > $wWidth || $y + $watermarkSize->getHeight() > $wHeight) {
            $xOffset = 0;
            if ($x < 0) {
                $xOffset = $x * -1;
            }
            $yOffset = 0;
            if ($y < 0) {
                $yOffset = $y * -1;
            }

            $ratios = [
                ($watermarkSize->getWidth() - $xOffset) / $watermarkSize->getWidth(),
                ($watermarkSize->getHeight() - $yOffset) / $watermarkSize->getHeight()
            ];
            $watermarkSize = $watermarkSize->scale(min($ratios));
            $watermarkImage->resize($watermarkSize);

            $x = round($watermark->getPositionX() + $wWidth - $watermarkSize->getWidth());
            $y = round($watermark->getPositionY() + $wHeight - $watermarkSize->getHeight());

            $xOffset = 0;
            if ($x + $watermarkSize->getWidth() > $wWidth) {
                $xOffset = $x + $watermarkSize->getWidth() - $wWidth;
            }
            $yOffset = 0;
            if ($y + $watermarkSize->getHeight() > $wHeight) {
                $yOffset = $y + $watermarkSize->getHeight() - $wHeight;
            }
            $ratios = [
                ($watermarkSize->getWidth() - $xOffset) / $watermarkSize->getWidth(),
                ($watermarkSize->getHeight() - $yOffset) / $watermarkSize->getHeight()
            ];
            $watermarkImage->resize($watermarkSize->scale(min($ratios)));
        }

        $point = new \Imagine\Image\Point($x, $y);
        $transformation
            ->add(new \Imagine\Filter\Basic\Paste($watermarkImage, $point), 100)
        ;

        return $transformation;
    }

    protected function getIconThumbnailByFileExtension(AbstractFileEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true, $forceExtension = false)
    {
        return false;
        // @todo Re-enable?
        if (!in_array($mode, ['inset', 'outbound'])) {
            throw new \InvalidArgumentException('Invalid mode requested.');
        }
        $mode = 'inset';

        $availableSizes = [16, 32, 48, 512];
        foreach ($availableSizes as $size) {
            if ($width <= $size && $height <= $size) {
                break;
            }
        }
        $extension = $forceExtension ? $forceExtension : pathinfo($entity->getFileName(), PATHINFO_EXTENSION);
        $icon = '@CmfcmfMediaModule/Resources/public/images/file-icons/' . $size . 'px/' . $extension . '.png';

        try {
            $path = $this->getPathToFile($icon);
        } catch (\InvalidArgumentException $e) {
            $icon = '@CmfcmfMediaModule/Resources/public/images/file-icons/' . $size . 'px/_blank.png';
            $path = $this->getPathToFile($icon);
        }

        $this->imagineManager->setPreset(
            $this->getPreset($entity, $path, $width, $height, $mode, $optimize)
        );

        $path = $this->imagineManager->getThumb($path, $entity->getImagineId());

        $url = \System::getBaseUri() . '/' . $path;
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
}
