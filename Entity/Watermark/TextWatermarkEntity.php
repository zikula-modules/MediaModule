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
use Imagine\Image\ImagineInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class TextWatermarkEntity extends AbstractWatermarkEntity
{
    /**
     * @ORM\Column(type="text", length=255)
     *
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $text;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * No assertions.
     *
     * @var int
     */
    protected $absoluteSize;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(max="40")
     *
     * @todo Assert valid choice.
     *
     * @var string
     */
    protected $font;

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return TextWatermarkEntity
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getImagineImage(ImagineInterface $imagine, FontCollection $fontCollection, $width, $height)
    {
        $fontPath = $fontCollection->getFontById($this->font)->getPath();
        if ($this->getAbsoluteSize() !== null) {
            $fontSize = $this->getAbsoluteSize();
        } elseif ($this->getRelativeSize() !== null) {
            $fontSize = (int) $this->getRelativeSize() / 100 * $height;
        } else {
            throw new \LogicException('Either relative or absolute watermark size must be set!');
        }
        if (true || !class_exists('ImagickDraw')) {
            // Fall back to ugly image.
            $palette = new \Imagine\Image\Palette\RGB();
            $font = $imagine->font($fontPath, $fontSize, $palette->color('#000'));
            $box = $font->box($this->getText());
            $watermarkImage = $imagine->create($box, $palette->color('#FFF'));
            $watermarkImage->draw()->text($this->text, $font, new \Imagine\Image\Point(0, 0));
        } else {
            // CURRENTLY DISABLED.
            // Use nicer Imagick implementation.
            // Untested!
            // @todo Test and implement it!
            $draw = new \ImagickDraw();
            $draw->setFont($fontPath);
            $draw->setFontSize($fontSize);
            $draw->setStrokeAntialias(true);  //try with and without
            $draw->setTextAntialias(true);  //try with and without

            $draw->setFillColor('#fff');

            $textOnly = new \Imagick();
            $textOnly->newImage(1400, 400, "transparent");  //transparent canvas
            $textOnly->annotateImage($draw, 0, 0, 0, $this->text);

            //Create stroke
            $draw->setFillColor('#000'); //same as stroke color
            $draw->setStrokeColor('#000');
            $draw->setStrokeWidth(8);

            $strokeImage = new \Imagick();
            $strokeImage->newImage(1400, 400, "transparent");
            $strokeImage->annotateImage($draw, 0, 0, 0, $this->text);

            //Composite text over stroke
            $strokeImage->compositeImage($textOnly, \Imagick::COMPOSITE_OVER, 0, 0, \Imagick::CHANNEL_ALPHA);
            $strokeImage->trimImage(0);  //cut transparent border

            $watermarkImage = $imagine->load($strokeImage->getImageBlob());
            //$strokeImage->resizeImage(300,0, \Imagick::FILTER_CATROM, 0.9, false); //resize to final size
        }

        return $watermarkImage;
    }

    /**
     * @Assert\Callback()
     *
     * Make sure that either relativeSize xor absoluteSize is set.
     *
     * @return bool
     */
    public function assertRelativeOrAbsoluteSizeSet()
    {
        $r = $this->relativeSize !== null;
        $a = $this->absoluteSize !== null;

        return $r xor $a;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewTableContent()
    {
        return htmlentities($this->text);
    }

    /**
     * Get the value of Absolute Size.
     *
     * @return int
     */
    public function getAbsoluteSize()
    {
        return $this->absoluteSize;
    }

    /**
     * Set the value of Absolute Size.
     *
     * @param int $absoluteSize
     *
     * @return self
     */
    public function setAbsoluteSize($absoluteSize)
    {
        $this->absoluteSize = $absoluteSize;

        return $this;
    }

    /**
     * Get the value of Font.
     *
     * @return string
     */
    public function getFont()
    {
        return $this->font;
    }

    /**
     * Set the value of Font.
     *
     * @param string $font
     *
     * @return self
     */
    public function setFont($font)
    {
        $this->font = $font;

        return $this;
    }
}
