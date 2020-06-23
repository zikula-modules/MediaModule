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
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Provides convenience methods for all collections templates.
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(Environment $twig, TranslatorInterface $translator)
    {
        $this->twig = $twig;
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
        return "@CmfcmfMediaModule/CollectionTemplate/" . lcfirst($this->getType()) . ".html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function render(CollectionEntity $collectionEntity, MediaTypeCollection $mediaTypeCollection, $showChildCollections, array $options)
    {
        return $this->twig->render($this->getTemplate(), [
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
