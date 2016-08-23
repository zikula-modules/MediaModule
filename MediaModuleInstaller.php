<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\GroupPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\OwnerPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Zikula\Core\AbstractExtensionInstaller;

class MediaModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->schemaTool->create(static::getEntities());

        $this->createLicenses();

        $temporaryUploadCollection = new CollectionEntity();
        $temporaryUploadCollection
            ->setTitle($this->__('Temporary upload collection'))
            ->setSlug('tmp')
            ->setDescription($this->__('This collection is needed as temporary storage for uploaded files. Do not edit or delete!'))
        ;
        $this->entityManager->persist($temporaryUploadCollection);

        // We need to create and flush the upload collection first, because it has to has the ID 1.
        $this->entityManager->flush();
        if ($temporaryUploadCollection->getId() != CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID) {
            throw new \Exception($this->__f('The id of the generated "temporary upload collection" must be %s, but has a different value. This should not have happened. Please report this error.', ['%s' => CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID]));
        }

        $rootCollection = new CollectionEntity();
        $rootCollection
            ->setTitle($this->__('Root collection'))
            ->setSlug($this->__('root'))
            ->setDescription('The very top of the collection tree.');
        $this->entityManager->persist($rootCollection);

        $temporaryUploadCollection->setParent($rootCollection);
        $this->entityManager->merge($temporaryUploadCollection);

        $exampleCollection = new CollectionEntity();
        $exampleCollection
            ->setTitle($this->__('Example collection'))
            ->setDescription($this->__('Edit or delete this example collection'))
            ->setParent($rootCollection);
        $this->entityManager->persist($exampleCollection);

        $this->createPermissions($temporaryUploadCollection, $rootCollection);

        $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
        $this->hookApi->installProviderHooks($this->bundle->getMetaData());

        $this->setVar('descriptionEscapingStrategyForCollection', 'text');
        $this->setVar('descriptionEscapingStrategyForMedia', 'text');
        $this->setVar('defaultCollectionTemplate', 'cards');
        $this->setVar('slugEditable', true);
        $this->setVar('lastNewVersionCheck', 0);
        $this->setVar('newVersionAvailable', false);

        $this->createCategoryRegistries();

        $this->createUploadDir();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case '1.0.0':
                $qb = $this->entityManager->createQueryBuilder();
                $qb->update('Cmfcmf\\Module\\MediaModule\\Entity\\Watermark\\TextWatermarkEntity', 'w')
                    ->set('w.font', $qb->expr()->literal('cmfcmfmediamodule:Indie_Flower'))
                    ->getQuery()
                    ->execute()
                ;
            case '1.0.1':
            case '1.0.2':
            case '1.0.3':
            case '1.0.4':
            case '1.0.5':
            /** @noinspection PhpMissingBreakStatementInspection */
            case '1.0.6':
                $this->schemaTool->create([
                    'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\GroupPermissionEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\UserPermissionEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\OwnerPermissionEntity',

                    'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction\PasswordPermissionRestrictionEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction\AbstractPermissionRestrictionEntity',
                ]);
                $this->schemaTool->update([
                    'Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity'
                ]);

                // Create root collection.
                $rootCollection = new CollectionEntity();
                $rootCollection
                    ->setTitle($this->__('Root collection'))
                    ->setSlug($this->__('root'))
                    ->setDescription('The very top of the collection tree.')
                ;
                $this->entityManager->persist($rootCollection);

                $allCollections = $this->entityManager
                    ->getRepository('Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity')
                    ->findAll();
                foreach ($allCollections as $collection) {
                    if ($collection->getParent() === null && $collection->getId() != null) {
                        // Collection has no parent and isn't the to-be-created root collection.
                        $collection->setParent($rootCollection);
                        $this->entityManager->merge($collection);
                    }
                }
                $this->entityManager->flush();

                $this->createPermissions(
                    $this->entityManager->find('Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity', 1),
                    $rootCollection
                );
            case '1.1.1':
                $this->schemaTool->update(['Cmfcmf\Module\MediaModule\Entity\Watermark\TextWatermarkEntity']);
                $this->container->get('doctrine.dbal.connection')->executeUpdate("UPDATE `cmfcmfmedia_watermarks` SET `fontColor`='#000000ff',`backgroundColor`='#00000000' WHERE `discr`='text'");

                $this->schemaTool->update([
                    'Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Collection\CollectionCategoryAssignmentEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity',
                    'Cmfcmf\Module\MediaModule\Entity\Media\MediaCategoryAssignmentEntity',
                ]);

                $this->createCategoryRegistries();

                return true;
            default:
                return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        // @todo Also delete media files?
        $this->schemaTool->drop(static::getEntities());

        $this->hookApi->uninstallSubscriberHooks($this->bundle->getMetaData());
        $this->hookApi->uninstallProviderHooks($this->bundle->getMetaData());

        $this->delVars();

        return true;
    }

    /**
     * Returns a list of all entity classes.
     *
     * @return array
     *
     * @internal
     */
    public static function getEntities()
    {
        $prefix = 'Cmfcmf\\Module\\MediaModule\\Entity\\';
        $mediaPrefix = $prefix . 'Media\\';
        $watermarkPrefix = $prefix . 'Watermark\\';
        $hookObjectPrefix = $prefix . 'HookedObject\\';
        $permissionPrefix = $prefix . 'Collection\\Permission\\';

        return [
            $hookObjectPrefix . 'HookedObjectEntity',
            $hookObjectPrefix . 'HookedObjectMediaEntity',
            $hookObjectPrefix . 'HookedObjectCollectionEntity',

            $prefix . 'Collection\CollectionEntity',

            $prefix . 'License\LicenseEntity',

            $permissionPrefix . 'AbstractPermissionEntity',
            $permissionPrefix . 'UserPermissionEntity',
            $permissionPrefix . 'GroupPermissionEntity',
            $permissionPrefix . 'OwnerPermissionEntity',

            $permissionPrefix . 'Restriction\AbstractPermissionRestrictionEntity',
            $permissionPrefix . 'Restriction\PasswordPermissionRestrictionEntity',

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

            $prefix . 'Collection\CollectionCategoryAssignmentEntity',
            $prefix . 'Media\MediaCategoryAssignmentEntity'
        ];
    }

    /**
     * Creates a set of default licenses.
     */
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

    /**
     * Creates the upload directory.
     */
    private function createUploadDir()
    {
        $uploadDirectory = \FileUtil::getDataDirectory() . '/cmfcmf-media-module/media';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $htaccess = <<<TXT
deny from all
<FilesMatch "(?i)\.(css|js|rss|png|gif|jpg|jpeg|psd|svg|txt|rtf|xml|pdf|sdt|odt|doc|docx|pps|ppt|pptx|xls|xlsx|mp3|wav|wma|avi|flv|mov|mp4|rm|vob|wmv|gz|rar|tar.gz|zip|ogg|webm)$">
order allow,deny
allow from all
</FilesMatch>
TXT;
        file_put_contents($uploadDirectory . '/.htaccess', $htaccess);
    }

    /**
     * Creates the basic permission scheme.
     *
     * @param CollectionEntity $temporaryUploadCollection
     * @param CollectionEntity $rootCollection
     */
    private function createPermissions(CollectionEntity $temporaryUploadCollection, CollectionEntity $rootCollection)
    {
        $temporaryUploadCollectionPermission = new GroupPermissionEntity();
        $temporaryUploadCollectionPermission->setCollection($temporaryUploadCollection)
            ->setDescription($this->__('Disallow access to the temporary upload collection.'))
            ->setAppliedToSelf(true)
            ->setAppliedToSubCollections(true)
            ->setGoOn(false)
            ->setPermissionLevels([CollectionPermissionSecurityTree::PERM_LEVEL_NONE])
            ->setPosition(1)
            ->setLocked(true)
            ->setGroupIds([-1]);
        $this->entityManager->persist($temporaryUploadCollectionPermission);

        $adminPermission = new GroupPermissionEntity();
        $adminPermission->setCollection($rootCollection)
            ->setDescription($this->__('Global admin permission'))
            ->setAppliedToSelf(true)
            ->setAppliedToSubCollections(true)
            ->setGoOn(false)
            ->setPermissionLevels([CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS])
            ->setPosition(2)
            ->setLocked(true)
            ->setGroupIds([2]); // Admin group
        $this->entityManager->persist($adminPermission);

        $ownerPermission = new OwnerPermissionEntity();
        $ownerPermission->setCollection($rootCollection)
            ->setDescription($this->__('Allows owners to administrate their own collections.'))
            ->setAppliedToSelf(true)
            ->setAppliedToSubCollections(false)
            ->setGoOn(true)
            ->setPermissionLevels(
                [CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS]
            )
            ->setPosition(3);
        $this->entityManager->persist($ownerPermission);

        $userPermission = new GroupPermissionEntity();
        $userPermission->setCollection($rootCollection)
            ->setDescription($this->__('Allow view and download for everyone.'))
            ->setAppliedToSelf(true)
            ->setAppliedToSubCollections(true)
            ->setGoOn(false)
            ->setPermissionLevels(
                [CollectionPermissionSecurityTree::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM]
            )
            ->setPosition(4)
            ->setGroupIds([-1]); // All groups
        $this->entityManager->persist($userPermission);

        $this->entityManager->flush();
    }

    private function createCategoryRegistries()
    {
        $categoryID = \CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Global')['id'];
        \CategoryRegistryUtil::insertEntry('CmfcmfMediaModule', 'AbstractMediaEntity', 'Main', $categoryID);
        \CategoryRegistryUtil::insertEntry('CmfcmfMediaModule', 'CollectionEntity', 'Main', $categoryID);
    }
}
