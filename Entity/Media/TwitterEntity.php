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
class TwitterEntity extends UrlEntity
{
    public function getTweetId(): ?string
    {
        return $this->extraData['tweetId'] ?? null;
    }

    public function setTweetId(string $tweetId): self
    {
        $this->extraData['tweetId'] = $tweetId;

        return $this;
    }
}
