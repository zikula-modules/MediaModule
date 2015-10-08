<?php

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
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity", inversedBy="hookedObjectMedia")
     *
     * @var HookedObjectEntity
     */
    private $hookedObject;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity", inversedBy="hookedObjectMedia")
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
