<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

class SelectedTemplate
{
    /**
     * @var TemplateInterface
     */
    private $template;

    /**
     * @var array
     */
    private $options;

    public function __construct(TemplateInterface $template, array $options)
    {
        $this->template = $template;
        $this->options = $options;
    }

    /**
     * @return TemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the database representation of this selected template (JSON format).
     *
     * @return string
     */
    public function toDB()
    {
        return json_encode([
            'template' => $this->template->getName(),
            'options' => $this->options
        ]);
    }
}