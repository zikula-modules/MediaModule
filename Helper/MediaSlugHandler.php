<?php


namespace Cmfcmf\Module\MediaModule\Helper;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;

class MediaSlugHandler implements SlugHandlerInterface
{
    /**
     * Construct the slug handler
     *
     * @param SluggableListener $sluggable
     */
    public function __construct(SluggableListener $sluggable)
    {
    }

    /**
     * Callback on slug handlers before the decision
     * is made whether or not the slug needs to be
     * recalculated
     *
     * @param SluggableAdapter $ea
     * @param array $config
     * @param object $object
     * @param string $slug
     * @param boolean $needToChangeSlug
     *
     * @return void
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
        $changeSet = $ea->getObjectChangeSet($uow, $object);

        if (isset($changeSet['collection'])) {
            $needToChangeSlug = true;
        }
    }

    /**
     * Callback on slug handlers right after the slug is built
     *
     * @param SluggableAdapter $ea
     * @param array $config
     * @param object $object
     * @param string $slug
     *
     * @return void
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    /**
     * Callback for slug handlers on slug completion
     *
     * @param SluggableAdapter $ea
     * @param array $config
     * @param object $object
     * @param string $slug
     *
     * @return void
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    /**
     * @return boolean whether or not this handler has already urlized the slug
     */
    public function handlesUrlization()
    {
        return false;
    }

    /**
     * Validate handler options
     *
     * @param array $options
     * @param ClassMetadata $meta
     */
    public static function validate(array $options, ClassMetadata $meta)
    {
    }
}
