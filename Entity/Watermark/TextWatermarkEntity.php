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
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
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
     * @ORM\Column(type="string", length=9)
     * @Assert\Regex("/^#[0-9a-fA-F]{8,8}$/")
     *
     * @var string
     */
    protected $fontColor;

    /**
     * @ORM\Column(type="string", length=9)
     * @Assert\Regex("/^#[0-9a-fA-F]{8,8}$/")
     *
     * @var string
     */
    protected $backgroundColor;

    public function __construct()
    {
        parent::__construct();

        $this->fontColor = "#000000FF";
        $this->backgroundColor = "#00000000";
    }

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

        $str2col = function ($str) {
            return (new RGB())->color(substr($str, 0, 7), (int)round(hexdec(substr($str, 7, 2)) / 2.55));
        };

        $font = $imagine->font($fontPath, $fontSize, $str2col($this->fontColor));
        $box = $font->box($this->getText());
        $watermarkImage = $imagine->create($box, $str2col($this->backgroundColor));
        $watermarkImage->draw()->text($this->text, $font, new Point(0, 0));

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

    /**
     * @param string $fontColor
     *
     * @return TextWatermarkEntity
     */
    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    /**
     * @param string $backgroundColor
     *
     * @return TextWatermarkEntity
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }
}
