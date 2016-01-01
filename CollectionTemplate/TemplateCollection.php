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

class TemplateCollection
{
    /**
     * @var TemplateInterface[]
     */
    private $templates;

    public function __construct()
    {
        $this->templates = [];
    }

    public function addCollectionTemplate(TemplateInterface $template)
    {
        $this->templates[$template->getName()] = $template;
    }

    public function getCollectionTemplates()
    {
        return $this->templates;
    }

    public function getCollectionTemplateTitles()
    {
        return array_map(function (TemplateInterface $template) {
            return $template->getTitle();
        }, $this->templates);
    }

    /**
     * @param string $template
     *
     * @return TemplateInterface
     */
    public function getCollectionTemplate($template)
    {
        return $this->templates[$template];
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function hasTemplate($template)
    {
        return isset($this->templates[$template]);
    }
}
