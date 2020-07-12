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
use Cmfcmf\Module\MediaModule\Traits\StandardFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Cmfcmf\Module\MediaModule\Entity\Watermark\Repository\WatermarkRepository")
 * @ORM\Table(name="cmfcmfmedia_watermarks")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr")
 * @ORM\DiscriminatorMap({
 *  "text"  = "TextWatermarkEntity",
 *  "image" = "ImageWatermarkEntity",
 * })
 */
abstract class AbstractWatermarkEntity
{
    use StandardFieldsTrait;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * No assertions.
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     *
     * No assertions.
     *
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank()
     *
     * @var int
     */
    protected $positionX;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank()
     *
     * @var int
     */
    protected $positionY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\GreaterThanOrEqual(value=0)
     *
     * @var int
     */
    protected $minSizeX;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\GreaterThanOrEqual(value=0)
     *
     * @var int
     */
    protected $minSizeY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Range(min=0, max=100)
     *
     * @var int
     */
    protected $relativeSize;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $dataDirectory;

    public function __construct(RequestStack $requestStack, string $dataDirectory = '')
    {
        $this->minSizeX = 200;
        $this->minSizeY = 80;

        // TODO refactor these dependencies out of the entities
        $this->requestStack = $requestStack;
        $this->dataDirectory = $dataDirectory;
    }

    /**
     * Returns the HTML content to be displayed inside the watermarks overview table.
     */
    abstract public function getViewTableContent(): string;

    abstract public function getImagineImage(
        ImagineInterface $imagine,
        FontCollection $fontCollection,
        int $width,
        int $height
    ): ImageInterface;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPositionX(): ?int
    {
        return $this->positionX;
    }

    public function setPositionX(int $positionX): self
    {
        $this->positionX = $positionX;

        return $this;
    }

    public function getPositionY(): ?int
    {
        return $this->positionY;
    }

    public function setPositionY(int $positionY): self
    {
        $this->positionY = $positionY;

        return $this;
    }

    public function getRelativeSize(): ?int
    {
        return $this->relativeSize;
    }

    public function setRelativeSize(int $relativeSize): self
    {
        $this->relativeSize = $relativeSize;

        return $this;
    }

    public function getMinSizeX(): ?int
    {
        return $this->minSizeX;
    }

    public function setMinSizeX(int $minSizeX): self
    {
        $this->minSizeX = $minSizeX;

        return $this;
    }

    public function getMinSizeY(): ?int
    {
        return $this->minSizeY;
    }

    public function setMinSizeY(int $minSizeY): self
    {
        $this->minSizeY = $minSizeY;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function setRequestStack(RequestStack $requestStack): self
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    public function setDataDirectory(string $dataDirectory): self
    {
        $this->dataDirectory = $dataDirectory;

        return $this;
    }
}
