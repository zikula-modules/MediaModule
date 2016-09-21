<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

class SelectedTemplateFactory
{
    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    public function __construct(TemplateCollection $templateCollection)
    {
        $this->templateCollection = $templateCollection;
    }

    public function fromDB($jsonOrString)
    {
        if ($this->isJSON($jsonOrString)) {
            $json = json_decode($jsonOrString, true);
            $template = $json['template'];
            $options = $json['options'];
        } else {
            $template = $jsonOrString;
            $options = [];
        }

        return $this->fromTemplateName($template, $options);
    }

    public function fromTemplateName($template, array $options)
    {
        $template = $this->templateCollection->getCollectionTemplate($template);
        if (count($options) == 0) {
            $options = $template->getDefaultOptions();
        }
        return new SelectedTemplate($template, $options);
    }

    private function isJSON($string)
    {
        return strpos($string, '{') !== false;
    }
}