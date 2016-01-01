<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides convenience methods for all collections templates.
 */
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
     * @param EngineInterface     $renderEngine
     * @param TranslatorInterface $translator
     */
    public function __construct(EngineInterface $renderEngine, TranslatorInterface $translator)
    {
        $this->renderEngine = $renderEngine;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return strtolower($this->getType());
    }

    /**
     * Calculates the template "type", which is it's unique identifier inside the MediaModule.
     * It's used for the "name" and "template path".
     *
     * @return string
     */
    protected function getType()
    {
        $class = get_class($this);

        return substr($class, strrpos($class, '\\') + 1, -strlen('Template'));
    }

    /**
     * Returns the template path for this collection template.
     *
     * @return string
     */
    protected function getTemplate()
    {
        return "CmfcmfMediaModule:CollectionTemplate:" . lcfirst($this->getType()) . ".html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function render(CollectionEntity $collectionEntity, MediaTypeCollection $mediaTypeCollection, $showChildCollections)
    {
        return $this->renderEngine->render($this->getTemplate(), [
            'collection' => $collectionEntity,
            'mediaTypeCollection' => $mediaTypeCollection,
            'showChildCollections' => $showChildCollections
        ]);
    }
}
