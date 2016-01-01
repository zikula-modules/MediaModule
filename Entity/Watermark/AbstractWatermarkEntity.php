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
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Gedmo\Mapping\Annotation as Gedmo;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
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
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     *
     * No assertions.
     *
     * @var int.
     */
    protected $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     *
     * No assertions.
     *
     * @var int.
     */
    protected $updatedUserId;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * No assertions.
     *
     * @var \DateTime.
     */
    protected $createdDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * No assertions.
     *
     * @var \DateTime.
     */
    protected $updatedDate;

    public function __construct()
    {
        $this->minSizeX = 200;
        $this->minSizeY = 80;
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
     * @return int
     */
    public function getCreatedUserId()
    {
        return $this->createdUserId;
    }

    /**
     * @param int $createdUserId
     *
     * @return AbstractWatermarkEntity
     */
    public function setCreatedUserId($createdUserId)
    {
        $this->createdUserId = $createdUserId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedUserId()
    {
        return $this->updatedUserId;
    }

    /**
     * @param int $updatedUserId
     *
     * @return AbstractWatermarkEntity
     */
    public function setUpdatedUserId($updatedUserId)
    {
        $this->updatedUserId = $updatedUserId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     *
     * @return AbstractWatermarkEntity
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param \DateTime $updatedDate
     *
     * @return AbstractWatermarkEntity
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

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
}
