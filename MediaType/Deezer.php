<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\DeezerEntity;
use Symfony\Component\HttpFoundation\Request;

class Deezer extends AbstractMediaType implements WebMediaTypeInterface, PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Deezer', [], $this->domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-music';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste($pastedText)
    {
        return $this->getParametersFromPastedText($pastedText) !== false ? 10 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste($pastedText)
    {
        $parameters = $this->getParametersFromPastedText($pastedText);
        if ($parameters === false) {
            throw new \RuntimeException();
        }

        $entity = new DeezerEntity();
        if (isset($parameters['title'])) {
            $entity->setTitle($parameters['title']);
        }
        $entity->setUrl('http://www.deezer.com');

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
        if (count($matches) == 3) {
            $parameters['musicId'] = $matches[2];
            $parameters['musicType'] = $matches[1];
            $parameters['showPlaylist'] = false;
        } else {
            $regex = '#src=(?:"|\')((?:.*?)deezer\.com/plugins/player(?:.*?))(?:"|\')#';
            preg_match($regex, $pastedText, $matches);
            if (count($matches) != 2) {
                return false;
            }
            $url = htmlspecialchars_decode($matches[1]);
            parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

            if (!isset($queryParams['type']) || !isset($queryParams['id'])) {
                return false;
            }
            $parameters['musicId'] = $queryParams['id'];
            $parameters['musicType'] = $queryParams['type'];
            if ($parameters['musicType'] == 'tracks') {
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
        if ($type == 'track') {
            $type = 'tracks';
        }
        $url = "http://www.deezer.com/plugins/player?format=classic&autoplay=false&playlist=$playlist&width=700&height=$height&color=$color&layout=dark&size=medium&type=$type&id=$id&title=$title&app_id=1";

        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Deezer:Fullpage.html.twig', [
            'url' => $url,
            'height' => $height
        ]);
    }

    public function getEntityFromWeb(Request $request)
    {
        /** @var DeezerEntity $entity */
        $entity = parent::getEntityFromWeb($request);

        $this->addExtraData($entity);

        return $entity;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        /** @var DeezerEntity $entity */

        $type = $entity->getMusicType();
        $id = $entity->getMusicId();
        if ($type == 'track') {
            $type = 'album';
            $id = $entity->getExtraData()['album']['id'];
        }

        //if ($mode == 'inset') {
        //    $size = max($width, $height);
        //} else if ($mode == 'outbound') {
            $size = min($width, $height);
        //}
        // @todo Ask Deezer whether it is allowed to crop the images.
        $url = "http://api.deezer.com/$type/$id/image?size=" . $size;

        switch ($format) {
            case 'url':
                return $url;
            case 'html':
                return '<img src="' . $url . '" />';
        }
        throw new \LogicException();
    }

    /**
     * @param DeezerEntity $entity
     */
    public function addExtraData(DeezerEntity $entity)
    {
        if ($entity->getMusicType() == 'track' || $entity->getMusicType() == 'album') {
            $track = $this->doJsonGetRequest('http://api.deezer.com/' . $entity->getMusicType() . '/' . $entity->getMusicId());
            if ($entity->getMusicType() == 'track') {
                $entity->addExtraData(['album' => $track['album']]);
            }
            $entity->addExtraData(['artist' => $track['artist']]);
        }
    }

    public function getSearchResults(Request $request, $q, $dropdownValue = null)
    {
        // TODO: Implement getSearchResults() method.
    }
}
