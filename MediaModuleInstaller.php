<?php

namespace Cmfcmf\Module\MediaModule;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;

class MediaModuleInstaller extends \Zikula_AbstractInstaller
{
    public function install()
    {
        \DoctrineHelper::createSchema($this->entityManager, static::getEntities());

        $this->createLicenses();

        $temporaryUploadCollection = new CollectionEntity();
        $temporaryUploadCollection
            ->setTitle($this->__('Temporary Upload Collection'))
            ->setDescription($this->__('This collection is needed as temporary storage for uploaded files. Do not edit or delete!'))
        ;
        $this->entityManager->persist($temporaryUploadCollection);

        $exampleCollection = new CollectionEntity();
        $exampleCollection
            ->setTitle($this->__('Example collection'))
            ->setDescription($this->__('Edit or delete this example collection'))
        ;
        $this->entityManager->persist($exampleCollection);

        $this->entityManager->flush();

        if ($temporaryUploadCollection->getId() != CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID) {
            \LogUtil::registerError($this->__('The id of the generated "temporary upload collection" must be 1, but has a different value. This should not have happened. Please report this error.'));
        }

        \HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
        \HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

        $this->setVar('descriptionEscapingStrategyForCollection', 'text');
        $this->setVar('descriptionEscapingStrategyForMedia', 'text');
        $this->setVar('defaultCollectionTemplate', 'cards');
        $this->setVar('slugEditable', true);
        $this->setVar('lastNewVersionCheck', 0);
        $this->setVar('newVersionAvailable', false);

        $this->createUploadDir();

        return true;
    }

    public function upgrade($oldversion)
    {
        if ($oldversion == '1.0.0') {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->update('Cmfcmf\\Module\\MediaModule\\Entity\\Watermark\\TextWatermarkEntity', 'w')
                ->set('w.font', $qb->expr()->literal('cmfcmfmediamodule:Indie_Flower'))
                ->getQuery()
                ->execute()
            ;

            return true;
        }

        return false;
    }

    public function uninstall()
    {
        // @todo Also delete media files?
        \DoctrineHelper::dropSchema($this->entityManager, static::getEntities());

        \HookUtil::unregisterProviderBundles($this->version->getHookProviderBundles());
        \HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());

        $this->delVars();

        return true;
    }

    public static function getEntities()
    {
        $prefix = 'Cmfcmf\\Module\\MediaModule\\Entity\\';
        $mediaPrefix = $prefix . 'Media\\';
        $watermarkPrefix = $prefix . 'Watermark\\';
        $hookObjectPrefix = $prefix . 'HookedObject\\';

        return [
            $hookObjectPrefix . 'HookedObjectEntity',
            $hookObjectPrefix . 'HookedObjectMediaEntity',
            $hookObjectPrefix . 'HookedObjectCollectionEntity',

            $prefix . 'Collection\\CollectionEntity',

            $prefix . 'License\\LicenseEntity',

            $mediaPrefix . 'AbstractMediaEntity',
            $mediaPrefix . 'AbstractFileEntity',
            $mediaPrefix . 'ImageEntity',
            $mediaPrefix . 'VideoEntity',
            $mediaPrefix . 'WebEntity',

            $mediaPrefix . 'DeezerEntity',
            $mediaPrefix . 'SoundCloudEntity',
            $mediaPrefix . 'FlickrEntity',

            $watermarkPrefix . 'AbstractWatermarkEntity',
            $watermarkPrefix . 'ImageWatermarkEntity',
            $watermarkPrefix . 'TextWatermarkEntity',
        ];
    }

    private function createLicenses()
    {
        $license = new LicenseEntity('all-rights-reserved');
        $license
            ->setTitle('All Rights Reserved')
            ->setEnabledForWeb(false)
            ->setEnabledForUpload(true)
        ;
        $this->entityManager->persist($license);

        $license = new LicenseEntity('no-rights-reserved');
        $license
            ->setTitle('No Rights Reserved')
            ->setEnabledForWeb(true)
            ->setEnabledForUpload(true)
        ;
        $this->entityManager->persist($license);

        $ccVersions = ['1.0', '2.0', '2.5', '3.0', '4.0'];
        $ccNames = [
            'CC-BY' => 'Creative Commons Attribution',
            'CC-BY-ND' => 'Creative Commons Attribution No Derivatives',
            'CC-BY-NC' => 'Creative Commons Attribution Non Commercial',
            'CC-BY-NC-ND' => 'Creative Commons Attribution Non Commercial No Derivatives',
            'CC-BY-NC-SA' => 'Creative Commons Attribution Non Commercial Share Alike',
            'CC-BY-SA' => 'Creative Commons Attribution Share Alike'
        ];

        foreach ($ccVersions as $version) {
            foreach ($ccNames as $id => $name) {
                $urlId = strtolower(substr($id, 3));
                $license = new LicenseEntity("$id-$version");
                $license
                    ->setTitle("$name $version")
                    ->setUrl("http://creativecommons.org/licenses/$urlId/$version/")
                    ->setImageUrl("https://i.creativecommons.org/l/$urlId/$version/80x15.png")
                    ->setEnabledForWeb(true)
                    ->setEnabledForUpload(true)
                    ->setOutdated($version != '4.0')
                ;
                $this->entityManager->persist($license);
            }
        }
        $this->entityManager->flush();
    }

    private function createUploadDir()
    {
        $uploadDirectory = \FileUtil::getDataDirectory() . '/cmfcmf-media-module/media';
        mkdir($uploadDirectory, 0777, true);

        $htaccess = <<<TXT
deny from all
<FilesMatch "(?i)\.(css|js|rss|png|gif|jpg|jpeg|psd|svg|txt|rtf|xml|pdf|sdt|odt|doc|docx|pps|ppt|pptx|xls|xlsx|mp3|wav|wma|avi|flv|mov|mp4|rm|vob|wmv|gz|rar|tar.gz|zip|ogg|webm)$">
order allow,deny
allow from all
</FilesMatch>
TXT;
        file_put_contents($uploadDirectory . '/.htaccess', $htaccess);
    }
}
