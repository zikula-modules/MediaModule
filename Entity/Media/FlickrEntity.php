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

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class FlickrEntity extends UrlEntity
{
    /**
     * @ORM\Column(type="string", length=5)
     *
     * @var string
     */
    protected $flickrFarm;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @var string
     */
    protected $flickrServer;

    /**
     * @ORM\Column(type="string", length=20)
     *
     * @var string
     */
    protected $flickrSecret;

    /**
     * @ORM\Column(type="string", length=20)
     *
     * @var string
     */
    protected $flickrId;

    public function getFlickrFarm(): ?string
    {
        return $this->extraData['flickrFarm'] ?? null;
    }

    public function setFlickrFarm(string $flickrFarm): self
    {
        $this->extraData['flickrFarm'] = $flickrFarm;

        return $this;
    }

    public function getFlickrServer(): ?string
    {
        return $this->extraData['flickrServer'] ?? null;
    }

    public function setFlickrServer(string $flickrServer): self
    {
        $this->extraData['flickrServer'] = $flickrServer;

        return $this;
    }

    public function getFlickrSecret(): ?string
    {
        return $this->extraData['flickrSecret'] ?? null;
    }

    public function setFlickrSecret(string $flickrSecret): self
    {
        $this->extraData['flickrSecret'] = $flickrSecret;

        return $this;
    }

    public function getFlickrId(): ?string
    {
        return $this->extraData['flickrId'] ?? null;
    }

    public function setFlickrId(string $flickrId): self
    {
        $this->extraData['flickrId'] = $flickrId;

        return $this;
    }
}
