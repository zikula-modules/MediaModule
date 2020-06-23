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

use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class SelectedTemplateFactory
{
    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    /**
     * @var string
     */
    private $defaultTemplate;

    public function __construct(
        TemplateCollection $templateCollection,
        VariableApiInterface $variableApi
    ) {
        $this->templateCollection = $templateCollection;
        $this->defaultTemplate = $variableApi->get('CmfcmfMediaModule', 'defaultCollectionTemplate');
    }

    public function fromDB($jsonOrString)
    {
        if (!$jsonOrString) {
            $jsonOrString = $this->defaultTemplate;
        }

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
        if (0 === count($options)) {
            $options = $template->getDefaultOptions();
        }

        return new SelectedTemplate($template, $options);
    }

    private function isJSON($string)
    {
        return false !== mb_strpos($string, '{');
    }
}
