<?php

namespace Cmfcmf\Module\MediaModule\Entity\License;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_license")
 * @ORM\HasLifecycleCallbacks()
 */
class LicenseEntity
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     *
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $outdated;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     *
     * @var string
     */
    protected $imageUrl;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $enabledForUpload;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $enabledForWeb;

    /**
     * @ORM\ManyToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity", mappedBy="licenses")
     *
     * @var HookedObjectEntity[]|ArrayCollection
     */
    protected $hookedObjects;

    public function __construct($id)
    {
        $this->id = $id;
        $this->enabledForUpload = true;
        $this->enabledForWeb = true;
        $this->outdated = false;
        $this->hookedObjects = new ArrayCollection();
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'outdated' => $this->outdated,
            'url' => $this->url,
            'imageUrl' => $this->imageUrl,
            'enabledForUpload' => $this->enabledForUpload,
            'enabledForWeb' => $this->enabledForWeb
        ];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
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
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param string $imageUrl
     *
     * @return LicenseEntity
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @return bool
     */
    public function isEnabledForUpload()
    {
        return $this->enabledForUpload;
    }

    /**
     * @param bool $enabledForUpload
     *
     * @return LicenseEntity
     */
    public function setEnabledForUpload($enabledForUpload)
    {
        $this->enabledForUpload = $enabledForUpload;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabledForWeb()
    {
        return $this->enabledForWeb;
    }

    /**
     * @param bool $enabledForWeb
     *
     * @return LicenseEntity
     */
    public function setEnabledForWeb($enabledForWeb)
    {
        $this->enabledForWeb = $enabledForWeb;

        return $this;
    }

    /**
     * @param bool $outdated
     *
     * @return LicenseEntity
     */
    public function setOutdated($outdated)
    {
        $this->outdated = $outdated;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOutdated()
    {
        return $this->outdated;
    }

    /**
     * @param HookedObjectEntity[]|ArrayCollection $hookedObjects
     *
     * @return LicenseEntity
     */
    public function setHookedObjects($hookedObjects)
    {
        $this->hookedObjects = $hookedObjects;

        return $this;
    }

    /**
     * @return HookedObjectEntity[]|ArrayCollection
     */
    public function getHookedObjects()
    {
        return $this->hookedObjects;
    }

    /**
     * @param int $version
     *
     * @return LicenseEntity
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
