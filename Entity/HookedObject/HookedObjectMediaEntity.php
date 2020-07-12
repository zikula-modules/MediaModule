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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getHookedObject(): ?HookedObjectEntity
    {
        return $this->hookedObject;
    }

    public function setHookedObject(HookedObjectEntity $hookedObject): self
    {
        $this->hookedObject = $hookedObject;

        return $this;
    }

    public function getMedia(): ?AbstractMediaEntity
    {
        return $this->media;
    }

    public function setMedia(AbstractMediaEntity $media): self
    {
        $this->media = $media;

        return $this;
    }
}
