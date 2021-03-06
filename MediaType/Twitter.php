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
use Cmfcmf\Module\MediaModule\Entity\Media\TwitterEntity;

class Twitter extends AbstractMediaType implements WebMediaTypeInterface, PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Twitter', [], 'cmfcmfmediamodule');
    }

    public function isEnabled()
    {
        return
            '' !== $this->variableApi->get('CmfcmfMediaModule', 'twitterApiKey') &&
            '' !== $this->variableApi->get('CmfcmfMediaModule', 'twitterApiSecret') &&
            '' !== $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessToken') &&
            '' !== $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessTokenSecret')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fab fa-twitter';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste(string $pastedText): int
    {
        return false !== $this->extractTweetIdFromPaste($pastedText) ? 10 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste(string $pastedText): AbstractMediaEntity
    {
        $entity = new TwitterEntity($this->requestStack, $this->dataDirectory);

        $tweetId = $this->extractTweetIdFromPaste($pastedText);
        if (false === $tweetId) {
            throw new \RuntimeException();
        }
        $tweetInfo = $this->getTweetInfo($tweetId);

        $entity
            ->setTweetId($tweetId)
            ->setTitle($this->translator->trans('Tweet by: ', [], 'cmfcmfmediamodule') . $tweetInfo['user']['name'])
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
        if (2 === count($results)) {
            return $results[1];
        }

        return false;
    }

    /**
     * @return string
     */
    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var TwitterEntity $entity */

        return $this->twig->render('@CmfcmfMediaModule/MediaType/Twitter/fullpage.html.twig', [
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

        return '<div>' . $this->twig->render('@CmfcmfMediaModule/MediaType/Twitter/fullpage.html.twig', [
            'entity' => $entity,
            'usePageAddAsset' => false,
            'placeholder' => $this->translator->trans('This is where the Tweet will appear.', [], 'cmfcmfmediamodule')
        ]) . '</div><p></p>';
    }

    public function getSearchResults($q, $dropdownValue = null)
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
        if (null === $dropdownValue || 'tweets' === $dropdownValue) {
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

        $response = $api->setGetfield("?count=100&q=${q}")
            ->buildOauth('https://api.twitter.com/1.1/search/tweets.json', 'GET')
            ->performRequest()
        ;

        $response = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);

        $results = [];
        $results['more'] = isset($response['search_metadata']['next_results']) ? true : 0;
        $results['results'] = [];

        foreach ($response['statuses'] as $status) {
            $results['results'][] = [
                [
                    'tweetId' => $status['id'],
                    'title' => $this->translator->trans('Tweet by: ', [], 'cmfcmfmediamodule') . $status['user']['name'],
                    'author' => $status['user']['name'],
                    'authorUrl' => 'https://twitter.com/' . $status['user']['screen_name'],
                    'authorAvatarUrl' => $status['user']['profile_image_url_https'],
                    'url' => 'https://twitter.com/' . $status['user']['screen_name'] . '/status/' . $status['id']
                ],
                $status['user']['profile_image_url_https'],
                $status['user']['name'] . "\n" . $this->translator->trans('Followers: ', [], 'cmfcmfmediamodule') . $status['user']['followers_count'],
                $this->translator->trans('Tweet', [], 'cmfcmfmediamodule'),
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

        return json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * @return \TwitterAPIExchange
     */
    private function getTwitterApi()
    {
        $api = new \TwitterAPIExchange([
            'oauth_access_token' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessToken'),
            'oauth_access_token_secret' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessTokenSecret'),
            'consumer_key' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiKey'),
            'consumer_secret' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiSecret')
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
//                $user['name'] . "\n" . $this->translator->trans('Followers: ', [], 'cmfcmfmediamodule') . $user['followers_count'],
//                $this->translator->trans('Profile', [], 'cmfcmfmediamodule'),
//                $user['description']
//            ];
//        }
//
//        return $results;
//    }
}
