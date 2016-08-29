<?php

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
    /**
     * @return string
     */
    public function getMusicType()
    {
        return isset($this->extraData['musicType']) ? $this->extraData['musicType'] : null;
    }

    /**
     * @param string $musicType
     *
     * @return DeezerEntity
     */
    public function setMusicType($musicType)
    {
        $this->extraData['musicType'] = $musicType;

        return $this;
    }

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
     * @return bool
     */
    public function isShowPlaylist()
    {
        return isset($this->extraData['showPlaylist']) ? $this->extraData['showPlaylist'] : false;
    }

    /**
     * @param bool $showPlaylist
     *
     * @return DeezerEntity
     */
    public function setShowPlaylist($showPlaylist)
    {
        $this->extraData['showPlaylist'] = $showPlaylist;

        return $this;
    }
}
