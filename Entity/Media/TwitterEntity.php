<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TwitterEntity extends WebEntity
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
