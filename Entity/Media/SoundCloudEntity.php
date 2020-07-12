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
class SoundCloudEntity extends UrlEntity
{
    public function getMusicId(): ?string
    {
        return $this->extraData['musicId'] ?? null;
    }

    public function setMusicId(string $musicId): self
    {
        $this->extraData['musicId'] = $musicId;

        return $this;
    }

    public function getMusicType(): ?int
    {
        return $this->extraData['musicType'] ?? null;
    }

    public function setMusicType(int $musicType): self
    {
        $this->extraData['musicType'] = $musicType;

        return $this;
    }
}
