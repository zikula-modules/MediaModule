<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\SoundCloudEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\YouTubeEntity;
use Symfony\Component\HttpFoundation\Request;

class YouTube extends AbstractMediaType implements WebMediaTypeInterface, PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->__('YouTube');
    }

    public function isEnabled()
    {
        return \ModUtil::getVar('CmfcmfMediaModule', 'googleApiKey') != "";
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-youtube';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste($pastedText)
    {
        return $this->extractYouTubeIdAndTypeFromPaste($pastedText) !== false ? 10 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste($pastedText)
    {
        $entity = new YouTubeEntity();

        list($type, $id) = $this->extractYouTubeIdAndTypeFromPaste($pastedText);
        if ($id === false || !in_array($type, ['playlist', 'video', 'channel', true])) {
            throw new \RuntimeException();
        }

        $this->setYouTubeDataByIdAndType($entity, $type, $id);

        return $entity;
    }

    private function extractYouTubeIdAndTypeFromPaste($pastedText)
    {
        preg_match('#youtube\.com\/channel\/([a-zA-Z0-9_-]+)#', $pastedText, $results);
        if (count($results) == 2) {
            return ['channel', $results[1]];
        }

        preg_match('#youtube(?:-nocookie)?\.com\/[a-zA-Z0-9_\-\/\=\?\&]+list=([a-zA-Z0-9_-]+)#', $pastedText, $results);
        if (count($results) == 2) {
            return ['playlist', $results[1]];
        }

        preg_match('#(?:youtube(?:-nocookie)?\.com\/\S*(?:(?:\/e(?:mbed))?\/|watch\?(?:\S*?&?v\=))|youtu\.be\/)([a-zA-Z0-9_-]{6,11})#', $pastedText, $results);
        if (count($results) == 2) {
            return ['video', $results[1]];
        }

        return false;
    }

    /**
     * @param AbstractMediaEntity $entity
     * @return string
     */
    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var YouTubeEntity $entity */
        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/YouTube:Fullpage.html.twig', [
            'entity' => $entity
        ]);
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        /** @var YouTubeEntity $entity */

        $url = $entity->getYouTubeThumbnailUrl();
        switch ($format) {
            case 'url':
                return $url;
            case 'html':
                return '<img src="' . $url . '" />';
        }
        throw new \LogicException();
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        /** @var YouTubeEntity $entity */

        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/YouTube:Fullpage.html.twig', [
            'entity' => $entity
        ]);
    }

    public function getSearchResults(Request $request, $q, $dropdownValue = null)
    {
        $youtube = $this->getYouTubeApi();

        $response = $youtube->search->listSearch('id,snippet', [
            'q' => $q,
            'maxResults' => 50
        ]);

        $result = [
            'more' => $response['pageInfo']['totalResults'],
            'results' => []
        ];

        foreach ($response['items'] as $searchResult) {
            $type = $id = $url = $typeName = $authorAvatarUrl = null;
            switch ($searchResult['id']['kind']) {
                case 'youtube#video':
                    $type = 'video';
                    $typeName = $this->__('Video');
                    $id = $searchResult['id']['videoId'];
                    $url = 'https://youtube.com/watch?v=' . urlencode($id);
                    break;
                case 'youtube#channel':
                    $type = 'channel';
                    $typeName = $this->__('Channel');
                    $id = $searchResult['id']['channelId'];
                    $url = 'https://youtube.com/channel/' . urlencode($id);
                    $authorAvatarUrl = $searchResult['snippet']['thumbnails']['high']['url'];
                    break;
                case 'youtube#playlist':
                    $type = 'playlist';
                    $typeName = $this->__('Playlist');
                    $id = $searchResult['id']['playlistId'];
                    $url = 'https://youtube.com/playlist/?list=' . urlencode($id);
                    break;
                default:
                    continue;
            }
            $author = $searchResult['snippet']['channelTitle'];
            $authorUrl = 'https://youtube.com/channel/' . urlencode($searchResult['snippet']['channelId']);

            $result['results'][] = [
                [
                    'title' => $searchResult['snippet']['title'],
                    'url' => $url,
                    'author' => $author,
                    'authorUrl' => $authorUrl,
                    'authorAvatarUrl' => $authorAvatarUrl,
                    'youTubeId' => $id,
                    'youTubeType' => $type,
                    'youTubeThumbnailUrl' => $searchResult['snippet']['thumbnails']['high']['url']
                ],
                $searchResult['snippet']['thumbnails']['medium']['url'],
                $typeName,
                $searchResult['snippet']['channelTitle'],
                $searchResult['snippet']['title']
            ];
        }

        return $result;
    }

    private function setYouTubeDataByIdAndType(YouTubeEntity &$entity, $type, $id)
    {
        $api = $this->getYouTubeApi();

        $entity
            ->setYouTubeId($id)
            ->setYouTubeType($type)
        ;

        switch ($type) {
            case 'video':
                $response = $api->videos->listVideos('id,snippet', [
                    'id' => $id
                ]);
                $entity
                    ->setUrl('https://youtube.com/watch?v=' . urlencode($id))
                    ->setAuthorUrl('https://youtube.com/channel/' . urlencode($id))
                ;
                break;
            case 'playlist':
                $response = $api->playlists->listPlaylists('id,snippet', [
                    'id' => $id
                ]);
                $entity
                    ->setUrl('https://youtube.com/channel/' . urlencode($id))
                    ->setAuthorUrl('https://youtube.com/playlist/?list=' . urlencode($response['items'][0]['snippet']['channelId']))
                ;
                break;
            case 'channel':
                $response = $api->channels->listChannels('id,snippet', [
                    'id' => $id
                ]);
                $entity
                    ->setUrl('https://youtube.com/channel/' . urlencode($id))
                    ->setAuthorUrl('https://youtube.com/channel/' . urlencode($id))
                    ->setAuthorAvatarUrl($response['items'][0]['snippet']['thumbnails']['high']['url'])
                ;
                break;
            default:
                throw new \LogicException();
        }
        $entity->setTitle($response['items'][0]['snippet']['title'])
            ->setAuthor($response['items'][0]['snippet']['channelTitle'])
            ->setYouTubeThumbnailUrl($response['items'][0]['snippet']['thumbnails']['high']['url'])
        ;
    }

    /**
     * @return \Google_Service_YouTube
     */
    private function getYouTubeApi()
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $client = new \Google_Client();
        $client->setApplicationName('Zikula Media Module by @cmfcmf');
        $client->setDeveloperKey(\ModUtil::getVar('CmfcmfMediaModule', 'googleApiKey'));

        return new \Google_Service_YouTube($client);
    }
}
