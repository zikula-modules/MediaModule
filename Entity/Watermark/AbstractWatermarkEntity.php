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
use Cmfcmf\Module\MediaModule\Traits\StandardFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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

    /**
     * @param RequestStack $requestStack
     * @param string       $dataDirectory
     */
    public function __construct(RequestStack $requestStack, $dataDirectory = '')
    {
        $this->minSizeX = 200;
        $this->minSizeY = 80;

        // TODO refactor these dependencies out of the entities
        $this->requestStack = $requestStack;
        $this->dataDirectory = $dataDirectory;
    }

    /**
     * Returns the HTML content to be displayed inside the watermarks overview table.
     *
     * @return string
     */
    abstract public function getViewTableContent();

    /**
     * @param ImagineInterface $imagine
     * @param FontCollection   $fontCollection
     * @param                  $width
     * @param                  $height
     *
     * @return ImageInterface
     */
    abstract public function getImagineImage(
        ImagineInterface $imagine,
        FontCollection $fontCollection,
        $width,
        $height
    );

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return AbstractWatermarkEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return AbstractWatermarkEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositionX()
    {
        return $this->positionX;
    }

    /**
     * @param int $positionX
     *
     * @return AbstractWatermarkEntity
     */
    public function setPositionX($positionX)
    {
        $this->positionX = $positionX;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositionY()
    {
        return $this->positionY;
    }

    /**
     * @param int $positionY
     *
     * @return AbstractWatermarkEntity
     */
    public function setPositionY($positionY)
    {
        $this->positionY = $positionY;

        return $this;
    }

    /**
     * @return int
     */
    public function getRelativeSize()
    {
        return $this->relativeSize;
    }

    /**
     * @param int $relativeSize
     *
     * @return AbstractWatermarkEntity
     */
    public function setRelativeSize($relativeSize)
    {
        $this->relativeSize = $relativeSize;

        return $this;
    }

    /**
     * Get the value of Min Size.
     *
     * @return int
     */
    public function getMinSizeX()
    {
        return $this->minSizeX;
    }

    /**
     * Set the value of Min Size.
     *
     * @param int $minSizeX
     *
     * @return self
     */
    public function setMinSizeX($minSizeX)
    {
        $this->minSizeX = $minSizeX;

        return $this;
    }

    /**
     * Get the value of Min Size.
     *
     * @return int
     */
    public function getMinSizeY()
    {
        return $this->minSizeY;
    }

    /**
     * Set the value of Min Size.
     *
     * @param int $minSizeY
     *
     * @return self
     */
    public function setMinSizeY($minSizeY)
    {
        $this->minSizeY = $minSizeY;

        return $this;
    }

    /**
     * @param int $version
     *
     * @return AbstractWatermarkEntity
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return AbstractWatermarkEntity
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * @param string $dataDirectory
     *
     * @return AbstractWatermarkEntity
     */
    public function setDataDirectory($dataDirectory)
    {
        $this->dataDirectory = $dataDirectory;

        return $this;
    }
}
