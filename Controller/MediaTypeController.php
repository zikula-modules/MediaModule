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

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Media\MediaCategoryAssignmentEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\VideoEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Google_Client;
use Google_Exception;
use Google_Http_MediaFileUpload;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/media-types")
 */
class MediaTypeController extends AbstractController
{
    /**
     * @Route("/youtube/upload/{id}")
     *
     * @return Response
     */
    public function youtubeUpload(VideoEntity $entity, Request $request)
    {
        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_MEDIA
        )) {
            throw new AccessDeniedException();
        }

        $clientID = $this->getVar('googleApiOAuthClientID');
        $clientSecret = $this->getVar('googleApiOAuthClientSecret');
        if (empty($clientID) || empty($clientSecret)) {
            if (!$this->securityManager->hasPermission('settings', 'admin')) {
                $this->addFlash(
                    'warning',
                    $this->trans('You need to add Google client ID and secret to use this feature!', [], 'cmfcmfmediamodule')
                );
            }

            return $this->redirectToRoute('cmfcmfmediamodule_media_display', [
                'slug' => $entity->getSlug(),
                'collectionSlug' => $entity->getCollection()->getSlug()
            ]);
        }

        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setScopes('https://www.googleapis.com/auth/youtube');
        $client->setRedirectUri(
            filter_var($this->generateUrl('cmfcmfmediamodule_mediatype_youtubeupload', ['id' => $entity->getId()], RouterInterface::ABSOLUTE_URL), FILTER_SANITIZE_URL)
        );

        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);

        if ($request->query->has('code')) {
            if ((string) ($request->getSession()->get('cmfcmfmediamodule_youtube_oauth_state')) !== (string) ($request->query->get('state'))) {
                exit('The session state did not match.');
            }

            $client->authenticate($request->query->get('code'));
            $request->getSession()->set('cmfcmfmediamodule_youtube_oauth_token', $client->getAccessToken());
        }

        if ($request->getSession()->has('cmfcmfmediamodule_youtube_oauth_token')) {
            $client->setAccessToken($request->getSession()->get('cmfcmfmediamodule_youtube_oauth_token'));
        }

        // Check to ensure that the access token was successfully acquired.
        if (!$client->getAccessToken()) {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $client->setState($state);
            $request->getSession()->set('cmfcmfmediamodule_youtube_oauth_state', $state);

            $authUrl = $client->createAuthUrl();
            $htmlBody = <<<END
    <h3>Authorization Required</h3>
    <p>You need to <a href="${authUrl}">authorize access</a> before proceeding.<p>
END;

            return new Response($htmlBody);
        }

        $form = $this->buildYouTubeUploadForm($entity);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@CmfcmfMediaModule/MediaType/Video/youtubeUpload.html.twig', [
                'entity' => $entity,
                'form' => $form->createView()
            ]);
        }

        try {
            $videoPath = $entity->getPath();

            // Create a snippet with title, description, tags and category ID
            // Create an asset resource and set its snippet metadata and type.
            $snippet = $this->createVideoSnippet($entity, $request->getLocale());

            // Set the video's status to "public". Valid statuses are "public",
            // "private" and "unlisted".
            $status = new Google_Service_YouTube_VideoStatus();
            $status->privacyStatus = $form->getData()['privacyStatus'];

            // Associate the snippet and status objects with a new video resource.
            $video = new Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            // Specify the size of each chunk of data, in bytes. Set a higher value for
            // reliable connection as fewer chunks lead to faster uploads. Set a lower
            // value for better recovery on less reliable connections.
            $chunkSizeBytes = 1 * 1024 * 1024;

            // Setting the defer flag to true tells the client to return a request which can be called
            // with ->execute(); instead of making the API call immediately.
            $client->setDefer(true);

            // Create a request for the API's videos.insert method to create and upload the video.
            $insertRequest = $youtube->videos->insert('status,snippet', $video);

            // Create a MediaFileUpload object for resumable uploads.
            $media = new Google_Http_MediaFileUpload(
                $client,
                $insertRequest,
                $entity->getMimeType(),
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize(filesize($videoPath));

            // Read the media file and upload it chunk by chunk.
            $status = false;
            $handle = fopen($videoPath, 'r');
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);

            // If you want to make other calls after the file upload, set setDefer back to false
            $client->setDefer(false);
        } catch (Google_Service_Exception $e) {
            $this->addFlash('error', 'A Google service error occurred: %error%', ['%error%' => $e->getMessage()], 'cmfcmfmediamodule');

            return $this->redirectToRoute('cmfcmfmediamodule_media_display', [
                'slug' => $entity->getSlug(),
                'collectionSlug' => $entity->getCollection()->getSlug()
            ]);
        } catch (Google_Exception $e) {
            $this->addFlash('error', 'A client error occurred: %error%', ['%error%' => $e->getMessage()], 'cmfcmfmediamodule');

            return $this->redirectToRoute('cmfcmfmediamodule_media_display', [
                'slug' => $entity->getSlug(),
                'collectionSlug' => $entity->getCollection()->getSlug()
            ]);
        }

        $request->getSession()->set('cmfcmfmediamodule_youtube_oauth_token', $client->getAccessToken());

        $this->addFlash('status', 'Your video was successfully uploaded.', [], 'cmfcmfmediamodule');

        return $this->redirectToRoute('cmfcmfmediamodule_media_display', [
            'slug' => $entity->getSlug(),
            'collectionSlug' => $entity->getCollection()->getSlug()
        ]);
    }

    /**
     * @return Form|FormInterface
     */
    private function buildYouTubeUploadForm(VideoEntity $entity)
    {
        $builder = $this->createFormBuilder();
        $builder
            ->add('privacyStatus', ChoiceType::class, [
                'choices' => [
                    $this->trans('Public', [], 'cmfcmfmediamodule') => 'public',
                    $this->trans('Unlisted', [], 'cmfcmfmediamodule') => 'unlisted',
                    $this->trans('Private', [], 'cmfcmfmediamodule') => 'private'
                ]
            ])
            ->add('submit', SubmitType::class, [])
            ->setAction($this->generateUrl('cmfcmfmediamodule_mediatype_youtubeupload', ['id' => $entity->getId()]))
        ;

        return $builder->getForm();
    }

    /**
     * @param string      $locale
     *
     * @return Google_Service_YouTube_VideoSnippet
     */
    private function createVideoSnippet(VideoEntity $entity, $locale = 'en')
    {
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($entity->getTitle());
        $snippet->setDescription($entity->getDescription());
        $snippet->setTags($entity->getCategoryAssignments()->map(function (MediaCategoryAssignmentEntity $assignment) use ($locale) {
            return $assignment->getCategory()->getDisplay_name($locale);
        }));
        // Numeric video category. See
        // https://developers.google.com/youtube/v3/docs/videoCategories/list
        // $snippet->setCategoryId("22");

        return $snippet;
    }
}
