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
     * No assertions.
     *
     * @var string
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
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    protected $outdated;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Url()
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Url()
     *
     * @var string
     */
    protected $imageUrl;

    /**
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    protected $enabledForUpload;

    /**
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    protected $enabledForWeb;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity",
     *     mappedBy="licenses")
     *
     * No assertions.
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

    /**
     * Converts the license entity to an array.
     */
    public function toArray(): array
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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function isEnabledForUpload(): bool
    {
        return $this->enabledForUpload;
    }

    public function setEnabledForUpload(bool $enabledForUpload): self
    {
        $this->enabledForUpload = $enabledForUpload;

        return $this;
    }

    public function isEnabledForWeb(): bool
    {
        return $this->enabledForWeb;
    }

    public function setEnabledForWeb(bool $enabledForWeb): self
    {
        $this->enabledForWeb = $enabledForWeb;

        return $this;
    }

    public function isOutdated(): bool
    {
        return $this->outdated;
    }

    public function setOutdated(bool $outdated): self
    {
        $this->outdated = $outdated;

        return $this;
    }

    /**
     * @param HookedObjectEntity[]|ArrayCollection $hookedObjects
     */
    public function setHookedObjects($hookedObjects): self
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

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }
}
