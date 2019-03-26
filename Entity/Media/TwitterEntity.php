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
    public function setTweetId($tweetId)
    {
        $this->extraData['tweetId'] = $tweetId;

        return $this;
    }

    public function getTweetId()
    {
        return isset($this->extraData['tweetId']) ? $this->extraData['tweetId'] : null;
    }
}
