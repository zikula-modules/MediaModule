<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Importer;

use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractImporterType extends SymfonyAbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getBlockPrefix()
    {
        $type = get_class($this);
        $type = substr($type, strrpos($type, '\\') + 1, -strlen('Type'));
        $type = strtolower($type);

        return "cmfcmfmediamodule_importer_$type";
    }
}
