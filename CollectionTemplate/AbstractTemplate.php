<?php

declare(strict_types=1);

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
        return mb_strtolower($this->getType());
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

        return mb_substr($class, mb_strrpos($class, '\\') + 1, -mb_strlen('Template'));
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
    public function render(CollectionEntity $collectionEntity, MediaTypeCollection $mediaTypeCollection, $showChildCollections, array $options)
    {
        return $this->renderEngine->render($this->getTemplate(), [
            'collection' => $collectionEntity,
            'mediaTypeCollection' => $mediaTypeCollection,
            'showChildCollections' => $showChildCollections,
            'options' => $options
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsForm()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [];
    }
}
