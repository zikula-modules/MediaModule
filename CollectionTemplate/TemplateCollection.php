<?php

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
