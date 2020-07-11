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

/**
 * Provides a list of all collection templates with some convenience methods.
 */
class TemplateCollection
{
    /**
     * @var TemplateInterface[]
     */
    private $templates;

    public function __construct(iterable $templates = [])
    {
        $this->templates = [];
        foreach ($templates as $template) {
            $this->addCollectionTemplate($template);
        }
    }

    /**
     * Adds a collection template to the template list.
     *
     * @param TemplateInterface $template
     */
    public function addCollectionTemplate(TemplateInterface $template)
    {
        $this->templates[$template->getName()] = $template;
    }

    /**
     * Returns the list of collection templates indexed by template name.
     *
     * @return array|TemplateInterface[]
     */
    public function getCollectionTemplates()
    {
        return $this->templates;
    }

    /**
     * Returns a list of template titles indexed by template name.
     *
     * @return array|string[]
     */
    public function getCollectionTemplateTitles()
    {
        $choices = [];
        foreach ($this->templates as $template) {
            $choices[$template->getTitle()] = $template->getName();
        }

        return $choices;
    }

    /**
     * Returns the specified collection template.
     *
     * @param string $template the template name
     *
     * @return TemplateInterface
     */
    public function getCollectionTemplate($template)
    {
        if (!$this->hasTemplate($template)) {
            throw new \DomainException();
        }

        return $this->templates[$template];
    }

    /**
     * Checks whether or not the specified collection template exists.
     *
     * @param string $template the template name
     *
     * @return bool
     */
    public function hasTemplate($template)
    {
        return isset($this->templates[$template]);
    }
}
