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

namespace Cmfcmf\Module\MediaModule\Entity\Watermark;

use Cmfcmf\Module\MediaModule\Font\FontCollection;
use Doctrine\ORM\Mapping as ORM;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\RequestStack;
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

    public function __construct(RequestStack $requestStack, string $dataDirectory = '')
    {
        parent::__construct($requestStack, $dataDirectory);

        $this->fontColor = '#000000FF';
        $this->backgroundColor = '#00000000';
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getImagineImage(
        ImagineInterface $imagine,
        FontCollection $fontCollection,
        int $width,
        int $height
    ): ImageInterface {
        $fontPath = $fontCollection->getFontById($this->font)->getPath();
        if (null !== $this->getAbsoluteSize()) {
            $fontSize = $this->getAbsoluteSize();
        } elseif (null !== $this->getRelativeSize()) {
            $fontSize = (int) $this->getRelativeSize() / 100 * $height;
        } else {
            throw new \LogicException('Either relative or absolute watermark size must be set!');
        }

        $str2col = function ($str) {
            return (new RGB())->color(mb_substr($str, 0, 7), (int)round(hexdec(mb_substr($str, 7, 2)) / 2.55));
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
     */
    public function assertRelativeOrAbsoluteSizeSet(): bool
    {
        $r = null !== $this->relativeSize;
        $a = null !== $this->absoluteSize;

        return $r xor $a;
    }

    public function getViewTableContent(): string
    {
        return htmlentities($this->text ?: '');
    }

    public function getAbsoluteSize(): ?int
    {
        return $this->absoluteSize;
    }

    public function setAbsoluteSize(int $absoluteSize): self
    {
        $this->absoluteSize = $absoluteSize;

        return $this;
    }

    public function getFont(): ?string
    {
        return $this->font;
    }

    public function setFont(string $font): self
    {
        $this->font = $font;

        return $this;
    }

    public function getFontColor(): ?string
    {
        return $this->fontColor;
    }

    public function setFontColor(string $fontColor): self
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }
}
