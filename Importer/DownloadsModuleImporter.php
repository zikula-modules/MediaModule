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
use Cmfcmf\Module\MediaModule\Entity\Media\UrlEntity;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class DownloadsModuleImporter extends AbstractImporter
{
    const DOWNLOADS_QUERY = <<<'SQL'
SELECT status, uupdate, title, url, filename, description, ddate, hits, submitter, filesize
FROM downloads_downloads
WHERE cid = ?
SQL;
    const DOWNLOAD_CATEGORY_QUERY = <<<'SQL'
SELECT cid, pid, title, description
FROM downloads_categories
ORDER BY pid ASC
SQL;
    /**
     * @var UploadableManager
     */
    private $uploadManager;

    /**
     * @var string
     */
    private $fileDirectory = '/Downloads';

    public function getTitle()
    {
        return $this->translator->trans('Downloads Module', [], 'cmfcmfmediamodule');
    }

    public function getDescription()
    {
        return $this->translator->trans('Import files from the Downloads Module.', [], 'cmfcmfmediamodule');
    }

    public function checkRequirements()
    {
        $conn = $this->em->getConnection();

        try {
            $conn->executeQuery('SELECT 1 FROM downloads_categories LIMIT 1');
            $conn->executeQuery('SELECT 1 FROM downloads_downloads LIMIT 1');
        } catch (TableNotFoundException $e) {
            return $this->translator->trans('Please install the Downloads Module or import it\'s tables into the database.');
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

    public function import($formData, FlashBagInterface $flashBag)
    {
        /** @var CollectionEntity $collection */
        $rootCollection = $formData['collection'];
        $pid2CollectionDict = [0 => $rootCollection];

        $conn = $this->em->getConnection();
        $result = $conn->executeQuery(self::DOWNLOAD_CATEGORY_QUERY);
        while ($downloadCollection = $result->fetch(\PDO::FETCH_ASSOC)) {
            $collection = new CollectionEntity();
            $collection
                ->setTitle($downloadCollection['title'])
                ->setDescription($downloadCollection['description'])
            ;
            $collection->setParent($pid2CollectionDict[$downloadCollection['pid']]);
            $pid2CollectionDict[$downloadCollection['cid']] = $collection;
            $this->em->persist($collection);

            $downloadResult = $conn->executeQuery(self::DOWNLOADS_QUERY, [$downloadCollection['cid']]);
            while ($download = $downloadResult->fetch(\PDO::FETCH_ASSOC)) {
                if (!empty($download['filename'])) {
                    $file = new File($this->dataDirectory . $this->fileDirectory . '/' . $download['filename']);
                    $mediaType = $this->mediaTypeCollection->getBestUploadableMediaTypeForFile($file);
                    $entityClass = $mediaType->getEntityClass();
                    /** @var AbstractFileEntity $entity */
                    $entity = new $entityClass($this->requestStack, $this->dataDirectory);
                    $this->uploadManager->markEntityToUpload($entity, ImportedFile::fromFile($file));
                } else {
                    $entity = new UrlEntity($this->requestStack, $this->dataDirectory);
                    $entity->setUrl($download['url']);
                }
                $entity
                    ->setTitle($download['title'])
                    ->setDescription($download['description'])
                    ->setCollection($collection)
                    //->setCreatedBy()
                    //->setUpdatedBy()
                    ->setCreatedDate(new \DateTime($download['ddate']))
                    ->setUpdatedDate(new \DateTime($download['uupdate']))
                ;
                $this->em->persist($entity);
            }
        }
        $this->em->flush();

        return true;
    }

    /**
     * All restrictions of the importer, i.e. things that can't be imported.
     *
     * @return string|null
     */
    public function getRestrictions()
    {
        return $this->translator->trans('The submitter, email, homepage and version properties of downloads are lost. Workflow states are lost and won\'t be imported. All downloads will be visible and active.', [], 'cmfcmfmediamodule');
    }
}
