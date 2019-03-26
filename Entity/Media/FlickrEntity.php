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

    /**
     * @return string
     */
    public function getFlickrFarm()
    {
        return isset($this->extraData['flickrFarm']) ? $this->extraData['flickrFarm'] : null;
    }

    /**
     * @param string $flickrFarm
     *
     * @return FlickrEntity
     */
    public function setFlickrFarm($flickrFarm)
    {
        $this->extraData['flickrFarm'] = $flickrFarm;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlickrServer()
    {
        return isset($this->extraData['flickrServer']) ? $this->extraData['flickrServer'] : null;
    }

    /**
     * @param string $flickrServer
     *
     * @return FlickrEntity
     */
    public function setFlickrServer($flickrServer)
    {
        $this->extraData['flickrServer'] = $flickrServer;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlickrSecret()
    {
        return isset($this->extraData['flickrSecret']) ? $this->extraData['flickrSecret'] : null;
    }

    /**
     * @param string $flickrSecret
     *
     * @return FlickrEntity
     */
    public function setFlickrSecret($flickrSecret)
    {
        $this->extraData['flickrSecret'] = $flickrSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlickrId()
    {
        return isset($this->extraData['flickrId']) ? $this->extraData['flickrId'] : null;
    }

    /**
     * @param string $flickrId
     *
     * @return FlickrEntity
     */
    public function setFlickrId($flickrId)
    {
        $this->extraData['flickrId'] = $flickrId;

        return $this;
    }
}
