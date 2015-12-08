<?php

namespace Cmfcmf\Module\MediaModule\Form\Importer;

use Symfony\Component\Form\AbstractType as SymfonyAbstractType;

abstract class AbstractImporterType extends SymfonyAbstractType
{
    public function getName()
    {
        $type = get_class($this);
        $type = substr($type, strrpos($type, '\\') + 1, -strlen('Type'));
        $type = strtolower($type);

        return "cmfcmfmediamodule_importer_$type";
    }
}
