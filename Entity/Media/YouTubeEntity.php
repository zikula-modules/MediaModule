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
class YouTubeEntity extends UrlEntity
{
    public function getYouTubeId(): ?string
    {
        return $this->extraData['youTubeId'] ?? null;
    }

    public function setYouTubeId(string $youTubeId): self
    {
        $this->extraData['youTubeId'] = $youTubeId;

        return $this;
    }

    public function getYouTubeType(): ?string
    {
        return $this->extraData['youTubeType'] ?? null;
    }

    public function setYouTubeType(string $youTubeType): self
    {
        $this->extraData['youTubeType'] = $youTubeType;

        return $this;
    }

    public function getYouTubeThumbnailUrl(): ?string
    {
        return $this->extraData['youTubeThumbnailUrl'] ?? null;
    }

    public function setYouTubeThumbnailUrl(string $youTubeThumbnailUrl): self
    {
        $this->extraData['youTubeThumbnailUrl'] = $youTubeThumbnailUrl;

        return $this;
    }
}
