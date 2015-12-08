<?php

namespace Cmfcmf\Module\MediaModule\Importer;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

class ServerDirectoryImporter extends AbstractImporter
{
    /**
     * @var UploadableManager
     */
    private $uploadManager;

    public function getTitle()
    {
        return $this->translator->trans('Server directory', [], $this->domain);
    }

    public function getDescription()
    {
        return $this->translator->trans('Import files from a directory on the server. Use another importer if possible.', [], $this->domain);
    }

    public function checkRequirements()
    {
        return true;
    }

    public function setUploadManager(UploadableManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    public function import($formData)
    {
        $serverDirectory = $formData['importSettings']['serverDirectory'];
        if (!is_readable($serverDirectory)) {
            return $this->translator->trans('Cannot read from the specified directory.', [], $this->domain);
        }
        if (!is_dir($serverDirectory)) {
            return $this->translator->trans('The specified directory doesn\'t appear to be a directory.', [], $this->domain);
        }
        /** @var CollectionEntity $rootCollection */
        $rootCollection = $formData['collection'];


        $finder = Finder::create()
            ->in($serverDirectory)
            ->depth($formData['importSettings']['includeSubDirectories'] ? '>0' : '==1')
            ->files()
        ;
        /** @var SplFileInfo $finderFile */
        foreach ($finder as $finderFile) {
            $file = new File($finderFile->getPath(), false);
            $max = -1;
            $selectedMediaType = null;
            foreach ($this->mediaTypeCollection->getUploadableMediaTypes() as $mediaType) {
                $n = $mediaType->canUpload($file);
                if ($n > $max) {
                    $max = $n;
                    $selectedMediaType = $mediaType;
                }
            }
            if ($selectedMediaType === null) {
                continue;
            }

            $entityClass = $selectedMediaType->getEntityClass();
            /** @var AbstractFileEntity $entity */
            $entity = new $entityClass();
            $entity->setCollection($rootCollection);
            $entity->setTitle('test');

            $em = $this->managerRegistry->getManagerForClass($entityClass);

            $this->uploadManager->markEntityToUpload($entity, $file);
            $em->persist($entity);
            $em->flush();
        }

        return true;
    }
}
