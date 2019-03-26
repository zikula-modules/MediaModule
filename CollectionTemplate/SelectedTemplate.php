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
