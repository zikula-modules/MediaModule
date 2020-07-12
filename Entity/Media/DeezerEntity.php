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
class DeezerEntity extends UrlEntity
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

    public function getMusicType(): ?string
    {
        return $this->extraData['musicType'] ?? null;
    }

    public function setMusicType(string $musicType): self
    {
        $this->extraData['musicType'] = $musicType;

        return $this;
    }

    public function isShowPlaylist(): bool
    {
        return $this->extraData['showPlaylist'] ?? false;
    }

    public function setShowPlaylist(bool $showPlaylist): self
    {
        $this->extraData['showPlaylist'] = $showPlaylist;

        return $this;
    }
}
