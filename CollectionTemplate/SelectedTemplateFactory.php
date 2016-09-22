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
