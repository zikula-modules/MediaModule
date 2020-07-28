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

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\DeezerEntity;

class Deezer extends AbstractMediaType implements WebMediaTypeInterface, PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Deezer', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fab fa-deezer';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste(string $pastedText): int
    {
        return false !== $this->getParametersFromPastedText($pastedText) ? 10 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste(string $pastedText): AbstractMediaEntity
    {
        $parameters = $this->getParametersFromPastedText($pastedText);
        if (false === $parameters) {
            throw new \RuntimeException();
        }

        $entity = new DeezerEntity($this->requestStack, $this->dataDirectory);
        if (isset($parameters['title'])) {
            $entity->setTitle($parameters['title']);
        }
        $entity->setUrl('https://www.deezer.com');

        $entity->setMusicId($parameters['musicId']);
        $entity->setMusicType($parameters['musicType']);
        $entity->setShowPlaylist($parameters['showPlaylist']);
        $this->addExtraData($entity);

        return $entity;
    }

    private function getParametersFromPastedText($pastedText)
    {
        $parameters = [];
        $regex = '#deezer\.com/(.*?)/(\d+)#';
        preg_match($regex, $pastedText, $matches);
        if (3 === count($matches)) {
            $parameters['musicId'] = $matches[2];
            $parameters['musicType'] = $matches[1];
            $parameters['showPlaylist'] = false;
        } else {
            $regex = '#src=(?:"|\')((?:.*?)deezer\.com/plugins/player(?:.*?))(?:"|\')#';
            preg_match($regex, $pastedText, $matches);
            if (2 !== count($matches)) {
                return false;
            }
            $url = htmlspecialchars_decode($matches[1]);
            parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

            if (!isset($queryParams['type']) || !isset($queryParams['id'])) {
                return false;
            }
            $parameters['musicId'] = $queryParams['id'];
            $parameters['musicType'] = $queryParams['type'];
            if ('tracks' === $parameters['musicType']) {
                $parameters['musicType'] = 'track';
            }
            if (isset($queryParams['title'])) {
                $parameters['title'] = $queryParams['title'];
            }
            if (isset($queryParams['playlist'])) {
                $parameters['showPlaylist'] = $queryParams['playlist'];
            } else {
                $parameters['showPlaylist'] = false;
            }
        }
        if (!isset($parameters['title'])) {
            $result = $this->doJsonGetRequest('http://api.deezer.com/' . $parameters['musicType'] . '/' . $parameters['musicId']);
            $parameters['title'] = $result['title'];
        }

        return $parameters;
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var DeezerEntity $entity */
        $playlist = $entity->isShowPlaylist() ? 'true' : 'false';
        $height = $entity->isShowPlaylist() ? 290 : 92;
        $color = '1990DB';
        $title = urldecode($entity->getTitle());
        $id = $entity->getMusicId();
        $type = $entity->getMusicType();
        if ('track' === $type) {
            $type = 'tracks';
        }
        $url = "http://www.deezer.com/plugins/player?format=classic&autoplay=false&playlist=${playlist}&width=700&height=${height}&color=${color}&layout=dark&size=medium&type=${type}&id=${id}&title=${title}&app_id=1";

        return $this->twig->render('@CmfcmfMediaModule/MediaType/Deezer/fullpage.html.twig', [
            'url' => $url,
            'height' => $height
        ]);
    }

    public function getEntityFromWeb()
    {
        /** @var DeezerEntity $entity */
        $entity = parent::getEntityFromWeb();

        $this->addExtraData($entity);

        return $entity;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        /** @var DeezerEntity $entity */
        $type = $entity->getMusicType();
        $id = $entity->getMusicId();
        if ('track' === $type) {
            $type = 'album';
            $id = $entity->getExtraData()['album']['id'];
        }

        //if ($mode == 'inset') {
        //    $size = max($width, $height);
        //} else if ($mode == 'outbound') {
        $size = min($width, $height);
        //}
        // @todo Ask Deezer whether it is allowed to crop the images.
        $url = "http://api.deezer.com/${type}/${id}/image?size=" . $size;

        switch ($format) {
            case 'url':
                return $url;
            case 'html':
                return '<img src="' . $url . '" />';
        }
        throw new \LogicException();
    }

    public function addExtraData(DeezerEntity $entity)
    {
        if ('track' === $entity->getMusicType() || 'album' === $entity->getMusicType()) {
            $track = $this->doJsonGetRequest('http://api.deezer.com/' . $entity->getMusicType() . '/' . $entity->getMusicId());
            if ('track' === $entity->getMusicType()) {
                $entity->addExtraData(['album' => $track['album']]);
            }
            $entity->addExtraData(['artist' => $track['artist']]);
        }
    }

    public function getFormOptions(AbstractMediaEntity $entity)
    {
        /** @var DeezerEntity $entity */
        return [
            'showPlaylistCheckbox' => 'playlist' === $entity->getMusicType()
        ];
    }

    public function getSearchResults($q, $dropdownValue = null)
    {
        // TODO: Implement getSearchResults() method.
    }
}
