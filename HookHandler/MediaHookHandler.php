<?php

namespace Cmfcmf\Module\MediaModule\HookHandler;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationResponse;

class MediaHookHandler extends AbstractHookHandler
{
    /**
     * @var AbstractMediaEntity[]
     */
    private $entities;

    /**
     * @var MediaTypeCollection
     */
    private $mediaTypeCollection;

    public function setMediaTypeCollection(MediaTypeCollection $mediaTypeCollection)
    {
        $this->mediaTypeCollection = $mediaTypeCollection;
    }

    public function uiView(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $content = $this->renderEngine->render('CmfcmfMediaModule:Media:hookView.html.twig', [
            'hookedObjectMedia' => $hookedObject->getHookedObjectMedia(),
            'mediaTypeCollection' => $this->mediaTypeCollection
        ]);

        $this->uiResponse($hook, $content);
    }

    public function uiEdit(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $mediaTypeCollection = $this->mediaTypeCollection;
        $selectedMedia = array_map(function (HookedObjectMediaEntity $hookedObjectMediaEntity) use ($mediaTypeCollection) {
            return $hookedObjectMediaEntity->getMedia()->toArrayForFinder($mediaTypeCollection);
        }, $hookedObject->getHookedObjectMedia()->getValues());

        $content = $this->renderEngine->render('CmfcmfMediaModule:Media:hookEdit.html.twig', [
            'selectedMedia' => $selectedMedia,
        ]);
        $this->uiResponse($hook, $content);
    }

    public function validateEdit(ValidationHook $hook)
    {
        $mediaIds = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[media]', [], true);

        $this->entities = [];
        $validationResponse = new ValidationResponse('media', $mediaIds);
        foreach ($mediaIds as $mediaId) {
            if (!empty($mediaId)) {
                $mediaEntity = $this->entityManager->find('CmfcmfMediaModule:Media\AbstractMediaEntity', $mediaId);
                if (!is_object($mediaEntity)) {
                    $validationResponse->addError('media', $this->__('Unknown media'));
                } else {
                    $this->entities[] = $mediaEntity;
                }
            }
        }

        $hook->setValidator($this->getProvider(), $validationResponse);
    }

    public function processEdit(ProcessHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearMedia();
        foreach ($this->entities as $mediaEntity) {
            $hookedObject->addMedia($mediaEntity);
        }

        $repository->saveOrDelete($hookedObject);
    }
}
