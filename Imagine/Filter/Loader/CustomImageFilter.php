<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Imagine\Filter\Loader;

use Cmfcmf\Module\MediaModule\Font\FontCollection;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Filter\Basic\Autorotate;
use Imagine\Filter\Basic\Paste;
use Imagine\Filter\Basic\WebOptimization;
use Imagine\Filter\Transformation;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class CustomImageFilter implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FontCollection
     */
    private $fontCollection;

    /**
     * @param ImagineInterface       $imagine
     * @param EntityManagerInterface $em
     * @param FontCollection         $fontCollection
     */
    public function __construct(
        ImagineInterface $imagine,
        EntityManagerInterface $em,
        FontCollection $fontCollection
    ) {
        $this->imagine = $imagine;
        $this->em = $em;
        $this->fontCollection = $fontCollection;
    }

    /**
     * @param ImageInterface $image
     * @param array          $options
     *
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $transformation = $this->getTransformation();

        return $transformation->apply($image);
    }

    /**
     * @param array $options
     *
     * @return Transformation
     */
    protected function getTransformation(array $options = [])
    {
        $transformation = new Transformation();
        if (isset($options['optimize']) && $options['optimize']) {
            // Optimize for web and rotate the images (after thumbnail creation).
            $transformation
                ->add(new Autorotate(), 101)
                ->add(new WebOptimization(), 2)
            ;
        }

        if (!isset($options['watermark']) || null === $options['watermark'] || !is_array($options['watermark']) || !count($options['watermark'])) {
            // The image shall not be watermarked.
            return $transformation;
        }
        $watermark = $this->em->getRepository('CmfcmfMediaModule:Watermark\\AbstractWatermarkEntity')->find($options['watermark']);
        if (null === $watermark) {
            // watermark not found
            return $transformation;
        }

        // TODO consider replacing custom watermark processing by existing watermark filter
        // check http://symfony.com/doc/current/bundles/LiipImagineBundle/filters/general.html#watermark

        // Generate the watermark image.
        // It will already be correctly sized for the thumbnail.

        $wWidth = $wHeight = 0;
        if (isset($options['mode']) && $options['mode'] == ImageInterface::THUMBNAIL_OUTBOUND) {
            $wWidth = $options['width'];
            $wHeight = $options['height'];
        } elseif (!isset($options['mode']) || $options['mode'] == ImageInterface::THUMBNAIL_INSET) {
            $imageSize = getimagesize($options['file']);

            $ratios = [
                $options['width'] / $imageSize[0],
                $options['height'] / $imageSize[1]
            ];
            $wWidth = min($ratios) * $imageSize[0];
            $wHeight = min($ratios) * $imageSize[1];
        } else {
            throw new \LogicException();
        }

        // Check whether the image is big enough to be watermarked.
        if (null !== $watermark['minSizeX'] && $wWidth < $watermark['minSizeX']) {
            return $transformation;
        }
        if (null !== $watermark['minSizeY'] && $wHeight < $watermark['minSizeY']) {
            return $transformation;
        }

        $watermarkImage = $watermark->getImagineImage($this->imagine, $this->fontCollection, $wWidth, $wHeight);
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

        $point = new Point($x, $y);
        $transformation->add(new Paste($watermarkImage, $point), 100);

        return $transformation;
    }
}
