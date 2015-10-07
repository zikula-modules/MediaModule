<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\SoundCloudEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\TwitterEntity;
use Symfony\Component\HttpFoundation\Request;

class Twitter extends AbstractMediaType implements WebMediaTypeInterface, PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Twitter', [], $this->domain);
    }

    public function isEnabled()
    {
        return
            \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiKey') != "" &&
            \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiSecret') != "" &&
            \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessToken') != "" &&
            \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessTokenSecret') != ""
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-twitter';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste($pastedText)
    {
        return $this->extractTweetIdFromPaste($pastedText) !== false ? 10 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste($pastedText)
    {
        $entity = new TwitterEntity();

        $tweetId = $this->extractTweetIdFromPaste($pastedText);
        if ($tweetId === false) {
            throw new \RuntimeException();
        }
        $tweetInfo = $this->getTweetInfo($tweetId);

        $entity
            ->setTweetId($tweetId)
            ->setTitle($this->translator->trans('Tweet by: ', [], $this->domain) . $tweetInfo['user']['name'])
            ->setAuthor($tweetInfo['user']['name'])
            ->setAuthorUrl('https://twitter.com/' . $tweetInfo['user']['screen_name'])
            ->setAuthorAvatarUrl($tweetInfo['user']['profile_image_url_https'])
            ->setUrl('https://twitter.com/' . $tweetInfo['user']['screen_name'] . '/status/' . $tweetId)
        ;

        return $entity;
    }

    private function extractTweetIdFromPaste($pastedText)
    {
        preg_match('#twitter\.com/[A-z]+/status/(\d+)#', $pastedText, $results);
        if (count($results) == 2) {
            return $results[1];
        } else {
            return false;
        }
    }

    /**
     * @param AbstractMediaEntity $entity
     * @return string
     */
    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var TwitterEntity $entity */

        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Twitter:Fullpage.html.twig', [
            'entity' => $entity,
            'usePageAddAsset' => true
        ]);
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        return false;
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        /** @var TwitterEntity $entity */

        return '<div>' . $this->renderEngine->render('CmfcmfMediaModule:MediaType/Twitter:Fullpage.html.twig', [
            'entity' => $entity,
            'usePageAddAsset' => false,
            'placeholder' => $this->translator->trans('This is where the Tweet will appear.', [], $this->domain)
        ]) . '</div><p></p>';
    }

    public function getSearchResults(Request $request, $q, $dropdownValue = null)
    {
        $q = str_replace('&', '', $q);
        $q = str_replace('?', '', $q);


        $tweetResults = [
            'results' => [],
            'more' => false
        ];
        //$userResults = [
        //    'results' => [],
        //    'more' => false
        //];
        if ($dropdownValue === null || $dropdownValue == 'tweets') {
            $tweetResults = $this->getTweetSearchResults($q);
        }
        //if ($dropdownValue === null || $dropdownValue == 'users') {
        //    $userResults = $this->getUserSearchResults($q);
        //}
        //$results = [];
        //$results['more'] = max($tweetResults['more'], $userResults['more']);
        //$results['results'] = array_merge($userResults['results'], $tweetResults['results']);

        return $tweetResults;
    }

    private function getTweetSearchResults($q)
    {
        $api = $this->getTwitterApi();

        $response = $api->setGetfield("?count=100&q=$q")
            ->buildOauth('https://api.twitter.com/1.1/search/tweets.json', 'GET')
            ->performRequest()
        ;

        $response = json_decode($response, true, 512,  JSON_BIGINT_AS_STRING);

        $results = [];
        $results['more'] = isset($response['search_metadata']['next_results']) ? true : 0;
        $results['results'] = [];


        foreach ($response['statuses'] as $status) {
            $results['results'][] = [
                [
                    'tweetId' => $status['id'],
                    'title' => $this->translator->trans('Tweet by: ', [], $this->domain) . $status['user']['name'],
                    'author' => $status['user']['name'],
                    'authorUrl' => 'https://twitter.com/' . $status['user']['screen_name'],
                    'authorAvatarUrl' => $status['user']['profile_image_url_https'],
                    'url' => 'https://twitter.com/' . $status['user']['screen_name'] . '/status/' . $status['id']
                ],
                $status['user']['profile_image_url_https'],
                $status['user']['name'] . "\n" . $this->translator->trans('Followers: ', [], $this->domain) . $status['user']['followers_count'],
                $this->translator->trans('Tweet', [], $this->domain),
                $status['text']
            ];
        }

        return $results;
    }

    private function getTweetInfo($tweetId)
    {
        $api = $this->getTwitterApi();

        $response = $api->buildOauth('https://api.twitter.com/1.1/statuses/show/' . $tweetId . '.json', 'GET')
            ->performRequest()
        ;

        return json_decode($response, true, 512,  JSON_BIGINT_AS_STRING);
    }

    /**
     * @return \TwitterAPIExchange
     */
    private function getTwitterApi()
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $api = new \TwitterAPIExchange([
            'oauth_access_token' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessToken'),
            'oauth_access_token_secret' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessTokenSecret'),
            'consumer_key' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiKey'),
            'consumer_secret' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiSecret')
        ]);
        return $api;
    }

//    private function getUserSearchResults($q)
//    {
//        $api = $this->getTwitterApi();
//
//        $response = $api->setGetfield("?count=20&q=$q")
//            ->buildOauth('https://api.twitter.com/1.1/users/search.json', 'GET')
//            ->performRequest()
//        ;
//
//        $response = json_decode($response, true, 512,  JSON_BIGINT_AS_STRING);
//
//        $results = [];
//        $results['more'] = false;
//        $results['results'] = [];
//
//        foreach ($response as $user) {
//            $results['results'][] = [
//                ['special stuff'],
//                $user['profile_image_url_https'],
//                $user['name'] . "\n" . $this->translator->trans('Followers: ', [], $this->domain) . $user['followers_count'],
//                $this->translator->trans('Profile', [], $this->domain),
//                $user['description']
//            ];
//        }
//
//        return $results;
//    }
}
