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
use Cmfcmf\Module\MediaModule\Entity\Media\MediaCategoryAssignmentEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;

class VerySimpleDownloadsModuleImporter extends AbstractImporter
{
    /**
     * @var UploadableManager
     */
    private $uploadManager;

    /**
     * @var CategoryRegistryRepositoryInterface
     */
    private $categoryRegistryRepository;

    /**
     * @var string
     */
    private $fileDirectory = '/VerySimpleDownload/downloads/fileupload';

    public function getTitle()
    {
        return $this->translator->trans('VerySimpleDownloads Module', [], 'cmfcmfmediamodule');
    }

    public function getDescription()
    {
        return $this->translator->trans('Import files from the VerySimpleDownloads Module.', [], 'cmfcmfmediamodule');
    }

    public function getRestrictions()
    {
        return $this->translator->trans('Workflow states are lost and won\'t be imported. All downloads will be visible and approved.', [], 'cmfcmfmediamodule');
    }

    public function checkRequirements()
    {
        $conn = $this->em->getConnection();

        try {
            $conn->executeQuery('SELECT 1 FROM vesido_download LIMIT 1');
            $conn->executeQuery('SELECT 1 FROM vesido_download_category LIMIT 1');
        } catch (TableNotFoundException $e) {
            return $this->translator->trans('Please install the VerySimpleDownloads Module or import it\'s tables into the database.', [], 'cmfcmfmediamodule');
        }

        if (!$this->filesystem->exists($this->dataDirectory . $this->fileDirectory)) {
            return $this->translator->trans('The uploaded files are missing. Make sure %path% contains the uploaded files.', ['%path%' => $this->dataDirectory . $this->fileDirectory], 'cmfcmfmediamodule');
        }

        return true;
    }

    public function setUploadManager(UploadableManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    public function setCategoryRegistryRepository(CategoryRegistryRepositoryInterface $categoryRegistryRepository)
    {
        $this->categoryRegistryRepository = $categoryRegistryRepository;
    }

    public function import($formData, FlashBagInterface $flashBag)
    {
        /** @var CollectionEntity $collection */
        $collection = $formData['collection'];
        $categoryRegistry = $this->categoryRegistryRepository->findOneBy([
            'modname' => 'CmfcmfMediaModule',
            'entityname' => 'AbstractMediaEntity',
            'property' => 'Main'
        ]);

        $conn = $this->em->getConnection();
        $result = $conn->executeQuery(<<<'SQL'
SELECT d.id, d.downloadTitle, d.downloadDescription, d.fileUpload, d.createdBy, d.updatedBy, d.createdDate, d.updatedDate, c.categoryId
FROM vesido_download d
LEFT JOIN vesido_download_category c ON c.entityId = d.id
SQL
        );
        $lastId = -1;
        while ($download = $result->fetch(\PDO::FETCH_ASSOC)) {
            if ($lastId != $download['id']) {
                $lastId = $download['id'];

                $file = new File($this->dataDirectory . $this->fileDirectory . '/' . $download['fileUpload']);
                $mediaType = $this->mediaTypeCollection->getBestUploadableMediaTypeForFile($file);
                $entityClass = $mediaType->getEntityClass();
                /** @var AbstractFileEntity $entity */
                $entity = new $entityClass($this->requestStack, $this->dataDirectory);
                $entity
                    ->setTitle($download['downloadTitle'])
                    ->setDescription($download['downloadDescription'])
                    ->setCollection($collection)
                    ->setCreatedBy($download['createdBy'])
                    ->setUpdatedBy($download['updatedBy'])
                    ->setCreatedDate(new \DateTime($download['createdDate']))
                    ->setUpdatedDate(new \DateTime($download['updatedDate']))
                ;

                $this->uploadManager->markEntityToUpload($entity, ImportedFile::fromFile($file));
            }
            if ($download['categoryId']) {
                $categoryEntity = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', $download['categoryId']);
                $entity->setCategoryAssignments(
                    new ArrayCollection([
                        new MediaCategoryAssignmentEntity($categoryRegistry, $categoryEntity, $entity)
                    ])
                );
            }

            $this->em->persist($entity);
        }

        $this->em->flush();

        return true;
    }
}
