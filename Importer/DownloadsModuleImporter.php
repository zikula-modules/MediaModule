<?php

namespace Cmfcmf\Module\MediaModule\Importer;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class DownloadsModuleImporter extends AbstractImporter
{
    /**
     * @var UploadableManager
     */
    private $uploadManager;

    public function getTitle()
    {
        return $this->translator->trans('Downloads Module', [], 'cmfcmfmediamodule');
    }

    public function getDescription()
    {
        return $this->translator->trans('Import files from the Downloads Module.', [], $this->domain);
    }

    public function checkRequirements()
    {
        $conn = $this->em->getConnection();

        try {
            $conn->executeQuery('SELECT 1 FROM downloads_categories LIMIT 1');
            $conn->executeQuery('SELECT 1 FROM downloads_downloads LIMIT 1');
        } catch (TableNotFoundException $e) {
            return $this->translator->trans('Please install the Donwloads Module or import it\'s tables into the database.');
        }

        // @todo
        return false;

        return true;
    }

    public function setUploadManager(UploadableManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    public function import($formData, FlashBagInterface $flashBag)
    {
        // @todo
    }
}
