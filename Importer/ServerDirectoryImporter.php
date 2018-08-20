<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Importer;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ServerDirectoryImporter extends AbstractImporter
{
    /**
     * @var UploadableManager
     */
    private $uploadManager;

    public function getTitle()
    {
        return $this->translator->trans('Server directory', [], 'cmfcmfmediamodule');
    }

    public function getDescription()
    {
        return $this->translator->trans('Import files from a directory on the server. Use another importer if possible.', [], 'cmfcmfmediamodule');
    }

    public function checkRequirements()
    {
        return true;
    }

    public function setUploadManager(UploadableManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    public function import($formData, FlashBagInterface $flashBag)
    {
        $serverDirectory = $formData['importSettings']['serverDirectory'];
        if (!is_readable($serverDirectory)) {
            return $this->translator->trans('Cannot read from the specified directory.', [], 'cmfcmfmediamodule');
        }
        if (!is_dir($serverDirectory)) {
            return $this->translator->trans('The specified directory doesn\'t appear to be a directory.', [], 'cmfcmfmediamodule');
        }
        if (!is_readable($serverDirectory)) {
            return $this->translator->trans('The specified directory isn\'t readable for the webserver.', [], 'cmfcmfmediamodule');
        }
        /** @var CollectionEntity $rootCollection */
        $rootCollection = $formData['collection'];

        $finder = Finder::create()
            ->in($serverDirectory)
            ->depth($formData['importSettings']['includeSubDirectories'] ? '>=0' : '==0')
            ->files()
        ;
        $collectionMapping = ['' => $rootCollection];
        $c = 0;
        /** @var SplFileInfo $finderFile */
        foreach ($finder as $finderFile) {
            $file = new File($finderFile->getPathname(), false);
            $selectedMediaType = $this->mediaTypeCollection->getBestUploadableMediaTypeForFile($file);
            if (null === $selectedMediaType) {
                $flashBag->add('warning', $this->translator->trans('Could not import file %file%, because this kind of media isn\'t yet supported by the MediaModule.', ['%file%' => $file->getPathname()], 'cmfcmfmediamodule'));
                continue;
            }

            $entityClass = $selectedMediaType->getEntityClass();
            /** @var AbstractFileEntity $entity */
            $entity = new $entityClass($this->dataDirectory);
            $relativePath = $finderFile->getRelativePath();
            if ($formData['importSettings']['createSubCollectionsForSubDirectories'] && $relativePath != "") {
                if (!isset($collectionMapping[$relativePath])) {
                    $collection = new CollectionEntity();
                    $lastSeparator = strrpos($relativePath, DIRECTORY_SEPARATOR);
                    $collection->setParent($collectionMapping[substr($relativePath, 0, (int)$lastSeparator)]);
                    $collection->setTitle(substr($relativePath, $lastSeparator === false ? 0 : $lastSeparator + 1));
                    $this->em->persist($collection);
                    $collectionMapping[$relativePath] = $collection;
                }
                $entity->setCollection($collectionMapping[$relativePath]);
            } else {
                $entity->setCollection($rootCollection);
            }
            $entity->setTitle(pathinfo($file->getFilename(), PATHINFO_FILENAME));

            $this->uploadManager->markEntityToUpload($entity, ImportedFile::fromFile($file));
            $this->em->persist($entity);

            $c++;
            if ($c % 50 == 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();

        return true;
    }

    public function getRestrictions()
    {
        return null;
    }
}
