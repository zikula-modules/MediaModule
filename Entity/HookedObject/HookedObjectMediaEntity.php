<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Entity\HookedObject;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_hookedobject_media")
 */
class HookedObjectMediaEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * No assertions.
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity",
     *     inversedBy="hookedObjectMedia")
     *
     * No assertions.
     *
     * @var HookedObjectEntity
     */
    private $hookedObject;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity",
     *     inversedBy="hookedObjectMedia")
     *
     * No assertions.
     *
     * @var AbstractMediaEntity
     */
    private $media;

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
     * @return HookedObjectMediaEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return HookedObjectEntity
     */
    public function getHookedObject()
    {
        return $this->hookedObject;
    }

    /**
     * @param HookedObjectEntity $hookedObject
     *
     * @return HookedObjectMediaEntity
     */
    public function setHookedObject($hookedObject)
    {
        $this->hookedObject = $hookedObject;

        return $this;
    }

    /**
     * @return AbstractMediaEntity
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param AbstractMediaEntity $media
     *
     * @return HookedObjectMediaEntity
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }
}
