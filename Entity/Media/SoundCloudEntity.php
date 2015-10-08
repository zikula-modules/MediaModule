<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SoundCloudEntity extends WebEntity
{
    /**
     * @return string
     */
    public function getMusicId()
    {
        return isset($this->extraData['musicId']) ? $this->extraData['musicId'] : null;
    }

    /**
     * @param string $musicId
     *
     * @return DeezerEntity
     */
    public function setMusicId($musicId)
    {
        $this->extraData['musicId'] = $musicId;

        return $this;
    }

    /**
     * @return int
     */
    public function getMusicType()
    {
        return isset($this->extraData['musicType']) ? $this->extraData['musicType'] : null;
    }

    /**
     * @param int $musicType
     *
     * @return SoundCloudEntity
     */
    public function setMusicType($musicType)
    {
        $this->extraData['musicType'] = $musicType;

        return $this;
    }
}
