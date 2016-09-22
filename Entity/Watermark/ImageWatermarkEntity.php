<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Entity\Watermark;

use Cmfcmf\Module\MediaModule\Font\FontCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Uploadable\Uploadable;
use Imagine\Image\ImagineInterface;

/**
 * @ORM\Entity
 * @Gedmo\Uploadable(pathMethod="getPathToUploadTo", filenameGenerator="SHA1", appendNumber=true,
 *     allowedTypes="image/png,image/jpeg,image/gif")
 *
 * NOTE: If you change the allowed mime types here, make sure to also change them in
 * {@link ImageWatermarkType}.
 */
class ImageWatermarkEntity extends AbstractWatermarkEntity implements Uploadable
{
    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileName
     */
    protected $fileName;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileMimeType
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize
     */
    protected $fileSize;

    /**
     * {@inheritdoc}
     */
    public function getImagineImage(
        ImagineInterface $imagine,
        FontCollection $fontCollection,
        $width,
        $height
    ) {
        $watermarkImage = $imagine->open($this->getPath());
        if ($this->getRelativeSize() !== null) {
            $y = (int) $height * $this->getRelativeSize() / 100;
            $factor = $y / $watermarkImage->getSize()->getHeight();
            $x = $watermarkImage->getSize()->getWidth() * $factor;
            $actualWidth = $width - abs($this->positionX);
            if ($x > $actualWidth) {
                $factor = $actualWidth / $x;
                $x = $actualWidth;
                $y *= $factor;
            }
            $watermarkImage->resize(new \Imagine\Image\Box($x, $y));
        }

        return $watermarkImage;
    }

    public function getPathToUploadTo($defaultPath)
    {
        unset($defaultPath);

        return \FileUtil::getDataDirectory() . '/cmfcmf-media-module/watermarks';
    }

    public function getPath()
    {
        return $this->getPathToUploadTo(null) . '/' . $this->fileName;
    }

    public function getUrl()
    {
        return "/" . ltrim(\System::getBaseUri(), '/') . $this->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getViewTableContent()
    {
        $src = htmlentities($this->getUrl());
        $title = htmlentities($this->title);

        return <<<EOD
<img class="img-responsive" style="max-width:150px;max-height:100px" src="$src" alt="$title" />
EOD;
    }
}
