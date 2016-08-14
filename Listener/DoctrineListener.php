<?php


namespace Cmfcmf\Module\MediaModule\Listener;

use Cmfcmf\Module\MediaModule\Entity\Media\AudioEntity;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Uploadable\Event\UploadablePostFileProcessEventArgs;
use Gedmo\Uploadable\Events;

class DoctrineListener implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::uploadablePostFileProcess];
    }

    public function uploadablePostFileProcess(UploadablePostFileProcessEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof AudioEntity) {
            /** @var AudioEntity $entity */
            if ($entity->getMimeType() == 'application/octet-stream') {
                if (pathinfo($args->getFileInfo()->getName(), PATHINFO_EXTENSION) == 'mp3') {
                    $om = $args->getEntityManager();
                    $uow = $om->getUnitOfWork();
                    $meta = $om->getClassMetadata(get_class($entity));
                    $config = $args->getListener()->getConfiguration($om, $meta->name);

                    $this->updateField($entity, $uow, $meta, $config['fileMimeTypeField'], 'audio/mp3');
                    $uow->recomputeSingleEntityChangeSet($meta, $entity);
                }
            }
        }
    }

    /**
     * @param object           $object
     * @param object           $uow
     * @param ClassMetadata    $meta
     * @param String           $field
     * @param mixed            $value
     * @param bool             $notifyPropertyChanged
     */
    protected function updateField($object, $uow, ClassMetadata $meta, $field, $value, $notifyPropertyChanged = true)
    {
        $property = $meta->getReflectionProperty($field);
        $oldValue = $property->getValue($object);
        $property->setValue($object, $value);

        if ($notifyPropertyChanged && $object instanceof NotifyPropertyChanged) {
            $uow->propertyChanged($object, $field, $oldValue, $value);
        }
    }
}
