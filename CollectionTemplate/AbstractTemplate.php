<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Translation\TranslationTrait;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Common\I18n\TranslatableInterface;

abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $domain;

    public function __construct(EngineInterface $renderEngine, TranslatorInterface $translator)
    {
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
        $this->renderEngine = $renderEngine;
        $this->translator = $translator;
    }

    public function getName()
    {
        return strtolower($this->getType());
    }

    protected function getType()
    {
        $class = get_class($this);

        return substr($class, strrpos($class, '\\') + 1, -strlen('Template'));
    }

    protected function getTemplate()
    {
        return "CmfcmfMediaModule:CollectionTemplate:{$this->getType()}.html.twig";
    }

    public function render(CollectionEntity $collectionEntity, MediaTypeCollection $mediaTypeCollection, $showChildCollections)
    {
        return $this->renderEngine->render($this->getTemplate(), [
            'collection' => $collectionEntity,
            'mediaTypeCollection' => $mediaTypeCollection,
            'showChildCollections' => $showChildCollections
        ]);
    }
}
